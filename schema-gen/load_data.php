<?php
/**
 * export_access.ps1 で出力したCSV群を、migrationと同じ列名マッピングで
 * PostgreSQL へ投入する。
 *
 * 実行:
 *   php schema-gen/load_data.php [CSVルート] [接続名]
 *   例) php schema-gen/load_data.php C:/path/to/scratchpad          … 既定接続(pgsql)
 *       php schema-gen/load_data.php C:/path/to/scratchpad neon     … Neon へ投入
 *
 * 備考:
 *  - マイグレーションに外部キー制約は無いため、FK抑止は best-effort。
 *    session_replication_role は Neon の非スーパーユーザーでは失敗するが無視して続行する。
 *  - 投入後に bigserial 列のシーケンスを setval で復旧する
 *  - Access Yes/No 由来の boolean 列（surveys）を明示的に正規化する
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$scratch = $argv[1] ?? 'C:/Users/user/AppData/Local/Temp/claude/C--inetpub-wwwroot-cto-asp/afb0ac0d-48b5-45c1-8098-a0c7626ea39c/scratchpad';
$connName = $argv[2] ?? config('database.default');
$db = DB::connection($connName);
echo "接続: {$connName}\n";

$columnOverrides = [
    'ID' => 'id', 'SiteID' => 'site_id', 'siteid' => 'site_id',
    'memberID' => 'member_id', 'memberid' => 'member_id',
    'ContentID' => 'content_id', 'SurveyID' => 'survey_id', 'surveyid' => 'survey_id',
    'RoutineWorkId' => 'routine_work_id', 'tagid' => 'tag_id', 'tagidf' => 'tag_id_father',
    'fatherID' => 'father_id', 'fatherid' => 'father_id', 'theid' => 'the_id',
    'idfrom' => 'id_from', 'idto' => 'id_to', 'idfromkind' => 'id_from_kind', 'idtokind' => 'id_to_kind',
    'hoursET' => 'hours_et', 'hoursET_actual' => 'hours_et_actual', 'hoursEMonth' => 'hours_e_month',
];

$tableOverrides = [
    'Change' => 'change_requests', 'Content' => 'contents', 'ContentComment' => 'content_comments',
    'ContentSort' => 'content_sorts', 'control' => 'controls', 'custom' => 'site_customs', 'faq' => 'faqs',
    'files' => 'files', 'filetag' => 'file_tags', 'guestbook' => 'guestbooks', 'guestbookc' => 'guestbook_categories',
    'homeworksort' => 'homework_sorts', 'link' => 'links', 'log' => 'logs', 'log_OKNG' => 'log_okngs',
    'maillist' => 'mail_lists', 'message' => 'messages', 'monku' => 'complaints', 'news' => 'news',
    'otoi' => 'inquiries', 'problem' => 'problems', 'product' => 'products', 'relation' => 'relations',
    'risk' => 'risks', 'RoutineWork' => 'routine_works', 'RoutineWorkList' => 'routine_work_lists',
    'status' => 'statuses', 'Survey' => 'surveys', 'SurveyChoice' => 'survey_choices',
    'SurveyChoiceResult' => 'survey_choice_results', 'surveyReplyList' => 'survey_reply_lists',
    'todo' => 'todos', 'topmenu' => 'top_menus', 'wbs' => 'wbs', 'category' => 'categories',
    'member' => 'members', 'room' => 'rooms', 'memberroom' => 'member_room', 'lebel' => 'levels',
];

// Access Yes/No 由来で、migration が boolean NOT NULL にした列
$booleanColumns = [
    'surveys' => ['open_yn', 'specify_yn'],
];

function toSnake($name) {
    $name = preg_replace('/([a-zA-Z])ID$/', '$1_id', $name);
    $name = preg_replace('/^ID$/', 'id', $name);
    $name = preg_replace('/(?<!^)(?<![A-Z_])([A-Z])/', '_$1', $name);
    $name = strtolower($name);
    $name = preg_replace('/_+/', '_', $name);
    return trim($name, '_');
}

function mapColumn($orig, $overrides) {
    return $overrides[$orig] ?? toSnake($orig);
}

function loadCsv($path) {
    $fh = fopen($path, 'r');
    $header = fgetcsv($fh);
    $rows = [];
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) !== count($header)) continue;
        $rows[] = array_combine($header, $row);
    }
    fclose($fh);
    return $rows;
}

function normalizeBool($v) {
    if ($v === null || $v === '') return false;
    $v = strtolower(trim((string) $v));
    return in_array($v, ['1', '-1', 't', 'true', 'yes', 'on'], true);
}

$jobs = [
    ['dir' => "$scratch/export_www", 'tables' => [
        'category','Change','Content','ContentComment','ContentSort','custom','faq','files','filetag',
        'guestbook','guestbookc','link','log','log_OKNG','message','news','otoi','problem','product',
        'relation','risk','RoutineWork','RoutineWorkList','status','Survey','SurveyChoice',
        'SurveyChoiceResult','surveyReplyList','todo','topmenu','wbs',
    ], 'truncate' => true, 'siteId' => 'www'],
    ['dir' => "$scratch/export_userdb", 'tables' => ['room'], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['lebel'], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['memberroom'], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['member'], 'truncate' => false, 'upsertKey' => 'member_id'],
];

try {
    $db->statement("SET session_replication_role = 'replica'");
} catch (\Throwable $e) {
    echo "note: session_replication_role をセットできませんでした（FK制約は無いので影響なし）\n";
}

$touchedTables = [];
$errors = [];

foreach ($jobs as $job) {
    foreach ($job['tables'] as $srcTable) {
        $csvPath = "{$job['dir']}/{$srcTable}.csv";
        if (!file_exists($csvPath)) {
            echo "SKIP (no csv): {$srcTable}\n";
            continue;
        }
        $rows = loadCsv($csvPath);
        $targetTable = $tableOverrides[$srcTable] ?? (toSnake($srcTable) . 's');
        $touchedTables[$targetTable] = true;

        if (!empty($job['truncate'])) {
            $db->table($targetTable)->truncate();
        }

        $inserted = 0;
        foreach ($rows as $row) {
            $mapped = [];
            foreach ($row as $col => $val) {
                if ($srcTable === 'memberroom' && $col === 'id') {
                    $mapped['legacy_id'] = ($val === '') ? null : $val;
                    continue;
                }
                $newCol = mapColumn($col, $columnOverrides);
                $mapped[$newCol] = ($val === '') ? null : $val;
            }

            foreach ($booleanColumns[$targetTable] ?? [] as $bcol) {
                if (array_key_exists($bcol, $mapped)) {
                    $mapped[$bcol] = normalizeBool($mapped[$bcol]);
                }
            }

            // 業務テーブルはこのジョブのサイトに属する（旧templatedbコピー方式の置き換え）
            if (! empty($job['siteId'])) {
                $mapped['site_id'] = $job['siteId'];
            }

            try {
                if (!empty($job['upsertKey'])) {
                    $key = $job['upsertKey'];
                    $db->table($targetTable)->updateOrInsert([$key => $mapped[$key]], $mapped);
                } else {
                    $db->table($targetTable)->insert($mapped);
                }
                $inserted++;
            } catch (\Throwable $e) {
                $errors[] = "{$targetTable}: " . $e->getMessage();
            }
        }
        echo "{$srcTable} -> {$targetTable} : {$inserted}/" . count($rows) . " rows\n";
    }
}

// bigserial 列のシーケンスを、投入済みの最大IDに合わせる
foreach (array_keys($touchedTables) as $t) {
    try {
        $hasId = $db->selectOne(
            "SELECT 1 AS ok FROM information_schema.columns WHERE table_name = ? AND column_name = 'id'",
            [$t]
        );
        if (! $hasId) {
            continue;
        }
        $seq = $db->selectOne("SELECT pg_get_serial_sequence(?, 'id') AS seq", [$t])->seq ?? null;
        if ($seq) {
            $db->statement("SELECT setval(?, COALESCE((SELECT MAX(id) FROM \"{$t}\"), 1))", [$seq]);
        }
    } catch (\Throwable $e) {
        $errors[] = "seq {$t}: " . $e->getMessage();
    }
}

try {
    $db->statement("SET session_replication_role = 'origin'");
} catch (\Throwable $e) {
    // 上でセットできていなければ戻す必要も無い
}

if ($errors) {
    echo "\n--- エラー " . count($errors) . " 件 ---\n";
    foreach (array_slice($errors, 0, 40) as $e) echo "  $e\n";
}

echo "\n完了\n";
