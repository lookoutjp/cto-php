<?php

namespace App\Support;

/**
 * 旧Access由来のテーブル・カラム名（英字）を管理画面（Filament）で読みやすい
 * 日本語ラベルに変換する。Resource の各フィールドで
 *   ->label(FieldLabels::ja('列名'))
 * のように使う。辞書に無い列名は元の列名をそのまま返す（欠落があっても壊れない）。
 */
class FieldLabels
{
    private const MAP = [
        // 共通
        'id' => 'ID',
        'legacy_id' => '旧ID',
        'site_id' => 'サイトID',
        'member_id' => '会員ID',
        'name' => '名前',
        'junban' => '表示順',
        'father_id' => '親ID',
        'kind' => '種別',
        'memo' => 'メモ',
        'code' => 'コード',
        'time' => '日時',
        'dt' => '日時',
        'from' => '差出人',
        'to' => '宛先',
        'link' => 'リンク',
        'category' => 'カテゴリ',
        'area' => '分野',
        'unit' => '単位',
        'indicator' => '指標',
        'strategy' => '方針',

        // 会員（members）
        'nameread' => 'ふりがな',
        'appeal' => 'ニックネーム',
        'introduce' => '紹介文',
        'email' => 'メールアドレス',
        'phone' => '電話番号',
        'dayphone' => '昼間の電話番号',
        'address' => '住所',
        'addressread' => '住所（ふりがな）',
        'password' => 'パスワード',
        'sex' => '性別',
        'hp' => 'ホームページ',
        'pointm' => 'ポイント',
        'pointmtime' => 'ポイント更新日時',
        'regtime' => '登録日時',
        'loginedtime' => '最終ログイン日時',
        'login_error_times' => 'ログイン失敗回数',
        'timerenew' => '最終アクセス日時',
        'online' => 'オンライン状態',
        'question' => '秘密の質問',
        'answer' => '回答',
        'magazine' => 'メルマガ購読',
        'ninshou' => '権限（-1:管理員／1:参加者／0:閲覧のみ）',
        'ninshouspecial' => '個別公開設定',

        // サイト（rooms）
        'sitename' => 'サイト名',
        'sitename_color' => 'サイト名の色',
        'sitecolor' => 'サイトカラー',
        'sitebgcolor' => 'サイト背景色',
        'sitedomain' => 'サイトドメイン',
        'siteintro' => 'サイト紹介文',
        'site_mail' => 'サイトのお問い合わせ先メール',
        'site_joutai' => 'サイト状態',
        'function_list' => '有効機能一覧',
        'comname' => '会社名',
        'comaddress' => '会社住所',
        'comphone' => '会社電話番号',
        'comfax' => '会社FAX番号',
        'comemail' => '会社メールアドレス',
        'compostcode' => '郵便番号',
        'comomanager' => '会社担当者',
        'webmanager' => 'Webサイト管理者',
        'komon' => '顧問',
        'manager' => '担当者',
        'manager_shouko' => '管理者の呼称',
        'managerwords' => '管理者からの言葉',
        'favicon' => 'ファビコン画像',
        'logo' => 'ロゴ画像',
        'logoheight' => 'ロゴの高さ(px)',
        'logowidth' => 'ロゴの幅(px)',
        'homepagemainimage' => 'トップページ画像',
        'pagetopimage' => 'ページ上部画像',
        'pagebackimage' => 'ページ背景画像',
        'pagebackimagerepeat' => '背景画像の繰り返し',
        'pagewidth' => 'ページ幅',
        'smtpid' => 'SMTP ID',
        'smtppass' => 'SMTPパスワード',
        'smtpserver' => 'SMTPサーバー',
        'sw_koukoku' => '広告表示',
        'copyright' => '著作権表記',

        // トップメニュー
        'menuname' => 'メニュー名',
        'linkaddress' => 'リンク先',

        // カテゴリ（content_sorts / categories）
        'categoryimage' => 'カテゴリ画像',
        'categoryname' => 'カテゴリ名',
        'koukaiflag' => '公開フラグ',
        'tobbs' => '掲示板リンク',

        // コンテンツ（contents）
        'adddatetime' => '登録日時',
        'adddt' => '登録日時',
        'add_date_time' => '登録日時',
        'addtime' => '登録日時',
        'createdt' => '作成日時',
        'create_date' => '作成日',
        'edittime' => '更新日時',
        'editdatetime' => '更新日時',
        'clicks' => '閲覧数',
        'commentok' => 'コメント許可',
        'content_sort' => 'カテゴリ',
        'content_id' => 'コンテンツID',
        'explain' => '説明',
        'hlsyosailink' => '詳細リンク',
        'keyword' => 'キーワード',
        'nameintro' => 'タイトル紹介',
        'ok' => '承認状態',
        'okngflag' => '承認/却下フラグ',
        'oktime' => '承認日時',
        'owner' => '作成者',
        'recommend' => 'おすすめ',
        'recommend_date' => 'おすすめ登録日',
        'survey_id' => '関連サーベイID',
        'syokai' => '概要',
        'syosai' => '詳細本文',
        'title' => 'タイトル',
        'title2' => 'サブタイトル',
        'content' => '内容',

        // ニュース
        'newsdate' => '掲載日',
        'istop' => 'トップ固定表示',
        'news_img' => '画像',

        // FAQ
        'orders' => '表示順',

        // ファイル
        'filename' => 'ファイル名',
        'fileext' => '拡張子',
        'size_bytes' => 'サイズ(バイト)',
        'storage_key' => '保存先キー',
        'intro' => '説明',
        'tags' => 'タグ',
        'tag_id' => 'タグID',
        'tag_id_father' => '親タグID',
        'tagname' => 'タグ名',
        'download' => 'ダウンロード数',

        // 問い合わせ
        'customer_name' => 'お名前',
        'customer_nameread' => 'お名前（ふりがな）',
        'remark' => '内容',
        'state' => '対応状況',
        'treated_date' => '対応日',
        'treated_remark' => '対応内容',

        // タスク共通（TODO/課題/リスク/成果物/定例作業/変更管理）
        'status' => 'ステータス',
        'person_do' => '担当者',
        'team_id' => 'チーム',
        'duedate' => '期限',
        'dotoday' => '本日のタスク',
        'delete_to' => '削除フラグ',
        'delete_from' => '削除フラグ（送信者側）',
        'renewdate' => '更新日時',
        'maker' => '起票者',
        'approver' => '承認者',
        'situation' => '状況',
        'completioncriteria' => '完了基準',
        'completion_date' => '完了日',
        'complete_date' => '完了日',
        'stage' => 'ステージ',
        'responsible_party' => '責任者',
        'function_name' => '機能名',
        'hours_e_month' => '月間見積工数',
        'hours_et' => '見積工数',
        'hours_et_actual' => '実績工数',

        // 変更管理（change_requests）
        'occurrence_day' => '発生日',
        'hour_estimation' => '工数見積',
        'judge_result' => '判定結果',
        'judge_person_custmer' => '判定者（顧客側）',
        'judge_person_system' => '判定者（システム側）',
        'judge_day' => '判定日',
        'research_reply_day' => '調査回答日',
        'researcher' => '調査担当者',
        'research_result' => '調査結果',
        'scope_of_impact' => '影響範囲',
        'ng_reason' => '却下理由',
        'approve_day' => '承認日',
        'do_content' => '対応内容',
        'do_hours' => '対応工数',
        'done_day' => '完了日',
        'changemaker' => '起票者',

        // WBS
        'godate' => '開始予定日',
        'tododays' => '計画工数（日）',
        'tododays_ed' => '実績工数（日）',
        'actualdays' => '実績日数',
        'iscategory' => 'サマリ項目',
        'jun' => '表示順',
        'space_num' => 'インデント数',
        'parent' => '親',
        'deep' => '階層の深さ',

        // 定例作業（routine_works マスター）
        'circle' => '繰り返し単位（day/week/month/year）',
        'circle_number' => '繰り返し数',
        'actiondate' => '実施日',
        'monitorfrequency' => '確認頻度',
        'monitoreddate' => '前回確認日',
        'routine_work_id' => '定例作業マスターID',

        // 関連（relations）
        'id_from' => '起点ID',
        'id_from_kind' => '起点の種別',
        'id_to' => '終点ID',
        'id_to_kind' => '終点の種別',
        'rtype' => '関連種別',
        'trigger' => '発生条件',
        'revert' => '管理員からの返信',
        'revert_date' => '返信日',

        // サーベイ
        'choice_explain' => '選択肢の説明',
        'choice_number' => '選択肢番号',
        'choice_title' => '選択肢タイトル',
        'selectable_numbers' => '選択可能数',
        'specify_yn' => '記名式',
        'open_yn' => '受付中',
        'answer_date' => '回答日',
        'answer_due_date' => '回答期限',

        // ステータスマスター
        'statusname' => 'ステータス名',
        'statuscomment' => 'ステータスの説明',
        'percent' => '進捗率(%)',

        // メッセージ
        'readed' => '既読',

        // リスク（impact系）
        'probability' => '発生確率',
        'impact2cost' => 'コストへの影響',
        'impact2quality' => '品質への影響',
        'impact2schedule' => 'スケジュールへの影響',
        'impact2scope' => 'スコープへの影響',

        // その他汎用
        'user_name' => 'ユーザー名',
        'com' => '会社',
        'custcont' => '顧客担当者',
        'custname' => '顧客名',
        'homepage' => 'ホームページ',
        'mail_list_sort' => 'メールリスト区分',
        'madetime' => '作成日時',
        'delitiji' => '削除日時',
        'f1' => '項目1',
        'jj' => '状態',
        'hits' => 'アクセス数',
        'linktime' => 'リンク登録日時',
        'level' => 'レベル',
        'levelname' => 'レベル名',
        'fatherlevel' => '親レベル',
        'start_date' => '開始日',
        'top' => 'スレッド先頭ID',
        'allow' => '許可',
        'site' => 'サイト',
    ];

    public static function ja(string $field): string
    {
        return self::MAP[$field] ?? $field;
    }
}
