<?php
/**
 * demo / miraipm など「www 以外」のテナントの業務データを、
 * ID をオフセットして単一DBへ投入する。
 *
 *   php schema-gen/load_tenant.php demo    C:/path/to/scratchpad/export_demo    1000000
 *   php schema-gen/load_tenant.php miraipm C:/path/to/scratchpad/export_miraipm 2000000
 *
 * - 旧テナントはそれぞれ独立DBだったため id 空間が www と重複する。
 *   id と「同一テナント内の別業務テーブルを指す参照列」を一律 offset する。
 * - member_id / person_do / team_id 等（会員・レベルは全サイト共有）は offset しない。
 * - 対象テーブルは site_id = <tenant> の行を削除してから入れ直す（冪等）。
 * - top_menus / site_customs / logs / files 系は対象外（旧ASPナビ・S3前提のため）。
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

[$script, $tenant, $dir, $offsetArg] = $argv + [null, null, null, null];
if (! $tenant || ! $dir) {
    fwrite(STDERR, "usage: php load_tenant.php <tenant> <csvDir> [offset]\n");
    exit(1);
}
$offset = (int) ($offsetArg ?: 1_000_000);
if ($tenant === 'www') {
    fwrite(STDERR, "www は load_data.php で投入してください。\n");
    exit(1);
}

/** src(Access名) => [target, refCols(offset対象、mapped名)] */
$tables = [
    'category' => ['categories', []],
    'status' => ['statuses', []],
    'ContentSort' => ['content_sorts', ['father_id']],
    'Content' => ['contents', ['content_sort', 'survey_id']],
    'ContentComment' => ['content_comments', ['content_id']],
    'news' => ['news', []],
    'faq' => ['faqs', []],
    'link' => ['links', []],
    'message' => ['messages', []],
    'otoi' => ['inquiries', []],
    'guestbookc' => ['guestbook_categories', []],
    'guestbook' => ['guestbooks', ['category', 'parent', 'top']],
    'todo' => ['todos', ['status', 'category']],
    'problem' => ['problems', ['status', 'category']],
    'risk' => ['risks', ['status', 'category']],
    'product' => ['products', ['status', 'category', 'stage']],
    'Change' => ['change_requests', ['status', 'category', 'stage']],
    'wbs' => ['wbs', ['father_id', 'status', 'category']],
    'RoutineWork' => ['routine_works', ['status', 'category']],
    'RoutineWorkList' => ['routine_work_lists', ['status', 'category', 'routine_work_id']],
    'Survey' => ['surveys', []],
    'SurveyChoice' => ['survey_choices', ['survey_id']],
    'SurveyChoiceResult' => ['survey_choice_results', ['survey_id']],
    'surveyReplyList' => ['survey_reply_lists', ['survey_id']],
    'relation' => ['relations', ['id_from', 'id_to']],
];

$columnOverrides = [
    'ID' => 'id', 'SiteID' => 'site_id', 'siteid' => 'site_id',
    'memberID' => 'member_id', 'memberid' => 'member_id',
    'ContentID' => 'content_id', 'SurveyID' => 'survey_id', 'surveyid' => 'survey_id',
    'RoutineWorkId' => 'routine_work_id', 'tagid' => 'tag_id', 'tagidf' => 'tag_id_father',
    'fatherID' => 'father_id', 'fatherid' => 'father_id', 'theid' => 'the_id',
    'idfrom' => 'id_from', 'idto' => 'id_to', 'idfromkind' => 'id_from_kind', 'idtokind' => 'id_to_kind',
    'hoursET' => 'hours_et', 'hoursET_actual' => 'hours_et_actual', 'hoursEMonth' => 'hours_e_month',
];
$booleanColumns = ['surveys' => ['open_yn', 'specify_yn']];

function toSnake(string $name): string
{
    $name = preg_replace('/([a-zA-Z])ID$/', '$1_id', $name);
    $name = preg_replace('/^ID$/', 'id', $name);
    $name = preg_replace('/(?<!^)(?<![A-Z_])([A-Z])/', '_$1', $name);
    $name = strtolower(preg_replace('/_+/', '_', $name));

    return trim($name, '_');
}
function mapColumn(string $orig, array $ov): string
{
    return $ov[$orig] ?? toSnake($orig);
}
function loadCsv(string $path): array
{
    $fh = fopen($path, 'r');
    $head = fgetcsv($fh);
    $rows = [];
    while (($r = fgetcsv($fh)) !== false) {
        if (count($r) === 1 && $r[0] === null) {
            continue;
        }
        $rows[] = array_combine($head, array_pad(array_slice($r, 0, count($head)), count($head), null));
    }
    fclose($fh);

    return $rows;
}
function offsetVal($v, int $offset)
{
    if ($v === null || $v === '') {
        return $v;
    }
    if (is_numeric($v) && (int) $v > 0) {
        return (string) ((int) $v + $offset);
    }

    return $v;
}

$db = DB::connection();
$totals = [];

foreach ($tables as $src => [$target, $refCols]) {
    $csv = "$dir/$src.csv";
    if (! file_exists($csv)) {
        echo "SKIP (no csv): $src\n";
        continue;
    }
    $cols = \Illuminate\Support\Facades\Schema::getColumnListing($target);
    $rows = loadCsv($csv);

    $db->table($target)->where('site_id', $tenant)->delete();

    $mapped = [];
    foreach ($rows as $row) {
        $out = [];
        foreach ($row as $c => $val) {
            $mc = mapColumn($c, $columnOverrides);
            if (! in_array($mc, $cols, true)) {
                continue;
            }
            if ($mc === 'id' || in_array($mc, $refCols, true)) {
                $val = offsetVal($val, $offset);
            }
            $out[$mc] = $val === '' ? null : $val;
        }
        foreach ($booleanColumns[$target] ?? [] as $bc) {
            $out[$bc] = in_array(strtolower(trim((string) ($out[$bc] ?? ''))), ['1', '-1', 't', 'true', 'yes', 'on'], true);
        }
        $out['site_id'] = $tenant;
        $mapped[] = $out;
    }

    foreach (array_chunk($mapped, 200) as $chunk) {
        try {
            $db->table($target)->insert($chunk);
        } catch (\Throwable $e) {
            foreach ($chunk as $one) {
                try {
                    $db->table($target)->insert($one);
                } catch (\Throwable $e2) {
                    echo "  ERR $target id={$one['id']}: ".substr($e2->getMessage(), 0, 120)."\n";
                }
            }
        }
    }
    $totals[$target] = count($mapped);
    echo str_pad($target, 24)." <- ".count($mapped)." rows (offset $offset)\n";
}

// bigserial シーケンスを最大IDに合わせる
foreach (array_keys($totals) as $t) {
    try {
        $db->statement("SELECT setval(pg_get_serial_sequence('$t','id'), GREATEST((SELECT MAX(id) FROM $t), 1))");
    } catch (\Throwable $e) {
    }
}

echo "\n{$tenant}: ".array_sum($totals)." 行投入。\n";
