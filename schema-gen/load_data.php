<?php
/**
 * export_data.ps1 で出力したCSV群を、migrationと同じ列名マッピングでMariaDBへ投入する。
 * 実行: php schema-gen/load_data.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$scratch = 'C:/Users/user/AppData/Local/Temp/claude/C--inetpub-wwwroot-cto-asp/c6f402bd-63b4-442f-b9be-2231585bfa1d/scratchpad';

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

$jobs = [
    // [csvディレクトリ, [元テーブル名, ...], truncateするか, upsertキー(nullならinsert)]
    ['dir' => "$scratch/export_www", 'tables' => [
        'category','Change','Content','ContentComment','ContentSort','custom','faq','files','filetag',
        'guestbook','guestbookc','link','log','log_OKNG','message','news','otoi','problem','product',
        'relation','risk','RoutineWork','RoutineWorkList','status','Survey','SurveyChoice',
        'SurveyChoiceResult','surveyReplyList','todo','topmenu','wbs',
    ], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['room'], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['lebel'], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['memberroom'], 'truncate' => true],
    ['dir' => "$scratch/export_userdb", 'tables' => ['member'], 'truncate' => false, 'upsertKey' => 'member_id'],
];

DB::statement('SET FOREIGN_KEY_CHECKS=0');

foreach ($jobs as $job) {
    foreach ($job['tables'] as $srcTable) {
        $csvPath = "{$job['dir']}/{$srcTable}.csv";
        if (!file_exists($csvPath)) {
            echo "SKIP (no csv): {$srcTable}\n";
            continue;
        }
        $rows = loadCsv($csvPath);
        $targetTable = $tableOverrides[$srcTable] ?? (toSnake($srcTable) . 's');

        if ($job['truncate']) {
            DB::table($targetTable)->truncate();
        }

        $inserted = 0;
        foreach ($rows as $row) {
            $mapped = [];
            foreach ($row as $col => $val) {
                // memberroomはmigration側でid列をサロゲートキーと衝突回避のためlegacy_idに退避しているため合わせる
                if ($srcTable === 'memberroom' && $col === 'id') {
                    $mapped['legacy_id'] = ($val === '') ? null : $val;
                    continue;
                }
                $newCol = mapColumn($col, $columnOverrides);
                $mapped[$newCol] = ($val === '') ? null : $val;
            }

            if (!empty($job['upsertKey'])) {
                $key = $job['upsertKey'];
                DB::table($targetTable)->updateOrInsert([$key => $mapped[$key]], $mapped);
            } else {
                DB::table($targetTable)->insert($mapped);
            }
            $inserted++;
        }
        echo "{$srcTable} -> {$targetTable} : {$inserted} rows\n";
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "完了\n";
