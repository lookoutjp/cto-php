<?php
/**
 * 旧ASP(Access)スキーマCSVから Laravel migration ファイルを自動生成するスクリプト。
 * 一度きりの移行支援ツール。生成後は database/migrations/ の中身を目視確認すること。
 */

require __DIR__ . '/../vendor/autoload.php';

$scratch = 'C:/Users/user/AppData/Local/Temp/claude/C--inetpub-wwwroot-cto-asp/c6f402bd-63b4-442f-b9be-2231585bfa1d/scratchpad';

$sources = [
    // [csvファイル, グループ名(central|tenant), 元テーブル名 => 出力テーブル名の上書き]
    ['file' => "$scratch/userdb_schema.csv", 'group' => 'central'],
    ['file' => "$scratch/security_schema.csv", 'group' => 'central'],
    ['file' => "$scratch/templatedb_schema.csv", 'group' => 'tenant'],
];

// 列名の上書き（自動スネークケース変換だと不自然になるもの）
$columnOverrides = [
    'ID' => 'id',
    'SiteID' => 'site_id',
    'siteid' => 'site_id',
    'memberID' => 'member_id',
    'memberid' => 'member_id',
    'ContentID' => 'content_id',
    'SurveyID' => 'survey_id',
    'surveyid' => 'survey_id',
    'RoutineWorkId' => 'routine_work_id',
    'tagid' => 'tag_id',
    'tagidf' => 'tag_id_father',
    'fatherID' => 'father_id',
    'fatherid' => 'father_id',
    'theid' => 'the_id',
    'idfrom' => 'id_from',
    'idto' => 'id_to',
    'idfromkind' => 'id_from_kind',
    'idtokind' => 'id_to_kind',
    'hoursET' => 'hours_et',
    'hoursET_actual' => 'hours_et_actual',
    'hoursEMonth' => 'hours_e_month',
    'SqlIn_CS' => 'sql_in_cs',
    'SqlIn_FS' => 'sql_in_fs',
    'SqlIn_ID' => 'sql_in_id',
    'SqlIn_IP' => 'sql_in_ip',
    'SqlIn_lang' => 'sql_in_lang',
    'SqlIn_site' => 'sql_in_site',
    'SqlIn_SJ' => 'sql_in_sj',
    'SqlIn_TIME' => 'sql_in_time',
    'SqlIn_usrname' => 'sql_in_username',
    'SqlIn_WEB' => 'sql_in_web',
];

// テーブル名の上書き（自動変換だと不自然/紛らわしいもの）
$tableOverrides = [
    'Change' => 'change_requests',
    'Content' => 'contents',
    'ContentComment' => 'content_comments',
    'ContentSort' => 'content_sorts',
    'control' => 'controls',
    'custom' => 'site_customs',
    'faq' => 'faqs',
    'files' => 'files',
    'filetag' => 'file_tags',
    'guestbook' => 'guestbooks',
    'guestbookc' => 'guestbook_categories',
    'homeworksort' => 'homework_sorts',
    'link' => 'links',
    'log' => 'logs',
    'log_OKNG' => 'log_okngs',
    'maillist' => 'mail_lists',
    'message' => 'messages',
    'monku' => 'complaints',
    'news' => 'news',
    'otoi' => 'inquiries',
    'problem' => 'problems',
    'product' => 'products',
    'relation' => 'relations',
    'risk' => 'risks',
    'RoutineWork' => 'routine_works',
    'RoutineWorkList' => 'routine_work_lists',
    'status' => 'statuses',
    'Survey' => 'surveys',
    'SurveyChoice' => 'survey_choices',
    'SurveyChoiceResult' => 'survey_choice_results',
    'surveyReplyList' => 'survey_reply_lists',
    'todo' => 'todos',
    'topmenu' => 'top_menus',
    'wbs' => 'wbs',
    'category' => 'categories',
    // central
    'member' => 'members',
    'room' => 'rooms',
    'memberroom' => 'member_room',
    'lebel' => 'levels',
    'passwordresettoken' => 'password_reset_tokens',
    'websession' => 'web_sessions',
    'sysversion' => 'sysversions',
    'sqlInLog' => 'sql_in_logs',
];

// ADOX上ではPKと認識されなかったが、実質的な主キーとみなす列（旧Accessの設計漏れの補正）
$assumedPkIfIdColumn = true;

// 自然主キーを持つが、Eloquentでの扱いを楽にするため surrogate id を追加するテーブル
$surrogateIdTables = ['memberroom', 'lebel', 'passwordresettoken'];

function toSnake($name) {
    $name = preg_replace('/([a-zA-Z])ID$/', '$1_id', $name);
    $name = preg_replace('/^ID$/', 'id', $name);
    $name = preg_replace('/(?<!^)(?<![A-Z_])([A-Z])/', '_$1', $name);
    $name = strtolower($name);
    $name = preg_replace('/_+/', '_', $name);
    $name = trim($name, '_');
    return $name;
}

function mapType($accessType) {
    switch ($accessType) {
        case 'Integer': return ['integer', []];
        case 'SmallInt': return ['smallInteger', []];
        case 'TinyInt': return ['tinyInteger', []];
        case 'BigInt': return ['bigInteger', []];
        case 'Boolean': return ['boolean', []];
        case 'Single': return ['float', []];
        case 'Double': return ['double', []];
        case 'Currency': return ['decimal', [19, 4]];
        case 'Date': return ['dateTime', []];
        case 'DBDate': return ['date', []];
        case 'DBTimeStamp': return ['dateTime', []];
        case 'GUID': return ['uuid', []];
        case 'LongVarWChar(Memo)': return ['text', []];
        case 'VarWChar':
        case 'WChar':
        case 'VarChar':
        case 'Char':
            return ['string', []]; // サイズは呼び出し側で付与
        case 'Binary':
        case 'VarBinary':
        case 'LongVarBinary(OLE)':
            return ['binary', []];
        case 'Numeric':
            return ['decimal', [18, 2]];
        default:
            return ['string', []];
    }
}

// --- CSV読み込み ---
$tables = []; // tableName => ['group'=>..., 'columns'=>[...]]

foreach ($sources as $src) {
    $fh = fopen($src['file'], 'r');
    $header = fgetcsv($fh);
    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) < 6) continue;
        [$table, $column, $type, $size, $nullable, $pk] = $row;
        if (!isset($tables[$table])) {
            $tables[$table] = ['group' => $src['group'], 'columns' => []];
        }
        $tables[$table]['columns'][] = [
            'name' => $column,
            'type' => $type,
            'size' => (int)$size,
            'nullable' => $nullable === 'YES',
            'pk' => $pk === 'YES',
        ];
    }
    fclose($fh);
}

// --- PK補正（idカラムがあり、かつADOXがPKを検出できなかった場合） ---
foreach ($tables as $tname => &$tdef) {
    $hasPk = false;
    foreach ($tdef['columns'] as $c) { if ($c['pk']) { $hasPk = true; break; } }
    if (!$hasPk && $assumedPkIfIdColumn) {
        foreach ($tdef['columns'] as &$c) {
            if (strtolower($c['name']) === 'id') {
                $c['pk'] = true;
            }
        }
        unset($c);
    }
}
unset($tdef);

// --- 「id」という名前だが主キーに選ばれなかった列は、実質使われていない残骸の可能性が高いため nullable にする ---
foreach ($tables as $tname => &$tdef) {
    foreach ($tdef['columns'] as &$c) {
        if (strtolower($c['name']) === 'id' && !$c['pk']) {
            $c['nullable'] = true;
        }
    }
    unset($c);
}
unset($tdef);

// --- migrationファイル生成 ---
$outDir = __DIR__ . '/../database/migrations';
if (!is_dir($outDir)) mkdir($outDir, 0777, true);

$order = [];
foreach ($tables as $tname => $tdef) {
    $order[] = [$tdef['group'] === 'central' ? 0 : 1, $tname];
}
usort($order, function($a, $b) {
    if ($a[0] !== $b[0]) return $a[0] <=> $b[0];
    return strcmp($a[1], $b[1]);
});

$baseTime = strtotime('2026-08-15 00:00:00');
$i = 0;
$log = [];

foreach ($order as [$grp, $tname]) {
    $tdef = $tables[$tname];
    $outTable = $tableOverrides[$tname] ?? toSnake($tname) . 's';

    $pkCols = array_values(array_filter($tdef['columns'], fn($c) => $c['pk']));
    $pkColNamesOriginal = array_map(fn($c) => $c['name'], $pkCols);
    $addSurrogateId = in_array($tname, $surrogateIdTables);

    $lines = [];
    $lines[] = "<?php";
    $lines[] = "";
    $lines[] = "use Illuminate\\Database\\Migrations\\Migration;";
    $lines[] = "use Illuminate\\Database\\Schema\\Blueprint;";
    $lines[] = "use Illuminate\\Support\\Facades\\Schema;";
    $lines[] = "";
    $lines[] = "return new class extends Migration";
    $lines[] = "{";
    $lines[] = "    /**";
    $lines[] = "     * 旧Access: {$tname} テーブルから自動生成";
    $lines[] = "     * 論理グループ: " . ($tdef['group'] === 'central' ? 'central（全サイト共有DB）' : 'tenant（テナントごとの業務DB）');
    $lines[] = "     */";
    $lines[] = "    public function up(): void";
    $lines[] = "    {";
    $lines[] = "        Schema::create('{$outTable}', function (Blueprint \$table) {";

    $usedNames = [];
    if ($addSurrogateId) {
        $lines[] = "            \$table->id();";
        $usedNames['id'] = true;
    }

    foreach ($tdef['columns'] as $c) {
        $newName = $columnOverrides[$c['name']] ?? toSnake($c['name']);
        if (isset($usedNames[$newName])) {
            // サロゲートidとの衝突、またはその他の重複は元カラムをlegacy_接頭辞で退避
            $newName = $addSurrogateId && $newName === 'id' ? 'legacy_id' : $newName . '_2';
        }
        $usedNames[$newName] = true;

        $isSinglePk = $c['pk'] && count($pkColNamesOriginal) === 1 && !$addSurrogateId;

        if ($isSinglePk && $c['type'] === 'Integer') {
            $lines[] = "            \$table->id('{$newName}');";
            continue;
        }

        [$method, $args] = mapType($c['type']);

        if ($method === 'string') {
            $size = $c['size'] > 0 && $c['size'] <= 255 ? $c['size'] : 255;
            $argStr = "'{$newName}', {$size}";
        } elseif (!empty($args)) {
            $argStr = "'{$newName}', " . implode(', ', $args);
        } else {
            $argStr = "'{$newName}'";
        }

        $line = "            \$table->{$method}({$argStr})";

        if ($isSinglePk && $c['type'] !== 'Integer') {
            $line .= "->primary()";
        }
        if ($c['nullable'] && !$c['pk']) {
            $line .= "->nullable()";
        }
        $line .= ";";
        $lines[] = $line;
    }

    if (count($pkColNamesOriginal) > 1 && !$addSurrogateId) {
        $mapped = array_map(fn($n) => "'" . ($columnOverrides[$n] ?? toSnake($n)) . "'", $pkColNamesOriginal);
        $lines[] = "            \$table->primary([" . implode(', ', $mapped) . "]);";
    } elseif (count($pkColNamesOriginal) > 1 && $addSurrogateId) {
        $mapped = array_map(fn($n) => "'" . ($columnOverrides[$n] ?? toSnake($n)) . "'", $pkColNamesOriginal);
        $lines[] = "            \$table->unique([" . implode(', ', $mapped) . "]);";
    } elseif (count($pkColNamesOriginal) === 1 && $addSurrogateId) {
        $mapped = "'" . ($columnOverrides[$pkColNamesOriginal[0]] ?? toSnake($pkColNamesOriginal[0])) . "'";
        $lines[] = "            \$table->unique([{$mapped}]);";
    }

    $lines[] = "        });";
    $lines[] = "    }";
    $lines[] = "";
    $lines[] = "    public function down(): void";
    $lines[] = "    {";
    $lines[] = "        Schema::dropIfExists('{$outTable}');";
    $lines[] = "    }";
    $lines[] = "};";
    $lines[] = "";

    $ts = date('Y_m_d_His', $baseTime + $i);
    $i++;
    $fname = "{$ts}_create_{$outTable}_table.php";
    file_put_contents("$outDir/$fname", implode("\n", $lines));
    $log[] = "$fname  <=  $tname ($grp)";
}

echo "生成完了: " . count($order) . " 件\n";
echo implode("\n", $log) . "\n";
