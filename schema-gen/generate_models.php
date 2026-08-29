<?php
/**
 * migrationと同じスキーマ情報からEloquentモデルの雛形を自動生成する。
 */

$scratch = 'C:/Users/user/AppData/Local/Temp/claude/C--inetpub-wwwroot-cto-asp/c6f402bd-63b4-442f-b9be-2231585bfa1d/scratchpad';

$sources = [
    ['file' => "$scratch/userdb_schema.csv", 'group' => 'central'],
    ['file' => "$scratch/security_schema.csv", 'group' => 'central'],
    ['file' => "$scratch/templatedb_schema.csv", 'group' => 'tenant'],
];

$columnOverrides = [
    'ID' => 'id', 'SiteID' => 'site_id', 'siteid' => 'site_id',
    'memberID' => 'member_id', 'memberid' => 'member_id',
    'ContentID' => 'content_id', 'SurveyID' => 'survey_id', 'surveyid' => 'survey_id',
    'RoutineWorkId' => 'routine_work_id', 'tagid' => 'tag_id', 'tagidf' => 'tag_id_father',
    'fatherID' => 'father_id', 'fatherid' => 'father_id', 'theid' => 'the_id',
    'idfrom' => 'id_from', 'idto' => 'id_to', 'idfromkind' => 'id_from_kind', 'idtokind' => 'id_to_kind',
    'hoursET' => 'hours_et', 'hoursET_actual' => 'hours_et_actual', 'hoursEMonth' => 'hours_e_month',
    'SqlIn_CS' => 'sql_in_cs', 'SqlIn_FS' => 'sql_in_fs', 'SqlIn_ID' => 'sql_in_id', 'SqlIn_IP' => 'sql_in_ip',
    'SqlIn_lang' => 'sql_in_lang', 'SqlIn_site' => 'sql_in_site', 'SqlIn_SJ' => 'sql_in_sj',
    'SqlIn_TIME' => 'sql_in_time', 'SqlIn_usrname' => 'sql_in_username', 'SqlIn_WEB' => 'sql_in_web',
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
    'passwordresettoken' => 'password_reset_tokens', 'websession' => 'web_sessions',
    'sysversion' => 'sysversions', 'sqlInLog' => 'sql_in_logs',
];

// モデルクラス名の明示指定（複数形テーブル名からの単純な単数化だと不自然なもの）
$modelNameOverrides = [
    'member' => 'Member', 'room' => 'Room', 'memberroom' => 'MemberRoom', 'lebel' => 'Level',
    'passwordresettoken' => 'PasswordResetToken', 'websession' => 'WebSession', 'sysversion' => 'SysVersion',
    'sqlInLog' => 'SqlInLog', 'Change' => 'ChangeRequest', 'Content' => 'Content',
    'ContentComment' => 'ContentComment', 'ContentSort' => 'ContentSort', 'control' => 'Control',
    'custom' => 'SiteCustom', 'faq' => 'Faq', 'files' => 'FileItem', 'filetag' => 'FileTag',
    'guestbook' => 'Guestbook', 'guestbookc' => 'GuestbookCategory', 'homeworksort' => 'HomeworkSort',
    'link' => 'LinkItem', 'log' => 'LogEntry', 'log_OKNG' => 'LogOkng', 'maillist' => 'MailList',
    'message' => 'MessageItem', 'monku' => 'Complaint', 'news' => 'NewsItem', 'otoi' => 'Inquiry',
    'problem' => 'Problem', 'product' => 'Product', 'relation' => 'Relation', 'risk' => 'Risk',
    'RoutineWork' => 'RoutineWork', 'RoutineWorkList' => 'RoutineWorkList', 'status' => 'StatusMaster',
    'Survey' => 'Survey', 'SurveyChoice' => 'SurveyChoice', 'SurveyChoiceResult' => 'SurveyChoiceResult',
    'surveyReplyList' => 'SurveyReplyList', 'todo' => 'Todo', 'topmenu' => 'TopMenu', 'wbs' => 'Wbs',
    'category' => 'Category',
];

function toSnake($name) {
    $name = preg_replace('/([a-zA-Z])ID$/', '$1_id', $name);
    $name = preg_replace('/^ID$/', 'id', $name);
    $name = preg_replace('/(?<!^)(?<![A-Z_])([A-Z])/', '_$1', $name);
    $name = strtolower($name);
    $name = preg_replace('/_+/', '_', $name);
    return trim($name, '_');
}

$tables = [];
foreach ($sources as $src) {
    $fh = fopen($src['file'], 'r');
    fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 6) continue;
        [$table, $column, $type, $size, $nullable, $pk] = $row;
        $tables[$table]['group'] = $src['group'];
        $tables[$table]['columns'][] = ['name' => $column, 'type' => $type, 'pk' => $pk === 'YES'];
    }
    fclose($fh);
}

$surrogateIdTables = ['memberroom', 'lebel', 'passwordresettoken'];

$outDir = __DIR__ . '/../app/Models';
if (!is_dir($outDir)) mkdir($outDir, 0777, true);

$results = [];
foreach ($tables as $tname => $tdef) {
    $hasPk = false;
    foreach ($tdef['columns'] as $c) { if ($c['pk']) { $hasPk = true; break; } }
    if (!$hasPk) {
        foreach ($tdef['columns'] as $c) {
            if (strtolower($c['name']) === 'id') { $hasPk = true; break; }
        }
    }

    $pkCols = array_values(array_filter($tdef['columns'], function($c) use ($tname) {
        return $c['pk'] || strtolower($c['name']) === 'id';
    }));
    // 「id」補正は最初の一致のみ有効（PK未検出時）
    $realPk = array_values(array_filter($tdef['columns'], fn($c) => $c['pk']));
    if (empty($realPk)) {
        foreach ($tdef['columns'] as $c) {
            if (strtolower($c['name']) === 'id') { $realPk = [$c]; break; }
        }
    }

    $outTable = $tableOverrides[$tname] ?? (toSnake($tname) . 's');
    $modelName = $modelNameOverrides[$tname] ?? ucfirst(toSnake($tname));
    $addSurrogateId = in_array($tname, $surrogateIdTables);

    $primaryKeyLine = '';
    $incrementingLine = '';
    $keyTypeLine = '';

    if ($addSurrogateId) {
        // id() サロゲートを使っているのでEloquent標準のまま
    } elseif (count($realPk) === 1) {
        $pkName = $columnOverrides[$realPk[0]['name']] ?? toSnake($realPk[0]['name']);
        if ($pkName !== 'id') {
            $primaryKeyLine = "    protected \$primaryKey = '{$pkName}';\n";
        }
        if ($realPk[0]['type'] !== 'Integer') {
            $incrementingLine = "    public \$incrementing = false;\n";
            $keyTypeLine = "    protected \$keyType = 'string';\n";
        }
    } else {
        // 複合キー。Eloquentは複合PKを直接サポートしないため主キー指定はせず、コメントで注記。
        $primaryKeyLine = "    // 注意: 元スキーマは複合キーのため、Eloquentの主キー機能は使えません。\n" .
                          "    // save()/find()は使わず、クエリビルダで明示的に条件指定してください。\n";
    }

    $body = "<?php\n\nnamespace App\\Models;\n\nuse Illuminate\\Database\\Eloquent\\Model;\n\nclass {$modelName} extends Model\n{\n";
    $body .= "    protected \$table = '{$outTable}';\n";
    $body .= $primaryKeyLine;
    $body .= $incrementingLine;
    $body .= $keyTypeLine;
    $body .= "    public \$timestamps = false;\n";
    $body .= "    protected \$guarded = [];\n";
    $body .= "}\n";

    file_put_contents("$outDir/{$modelName}.php", $body);
    $results[] = "{$modelName}.php  <=  {$tname}";
}

echo "生成完了: " . count($results) . " 件\n" . implode("\n", $results) . "\n";
