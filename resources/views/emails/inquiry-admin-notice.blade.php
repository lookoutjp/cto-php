{{ $siteName }} に新しいお問い合わせが届きました。
管理画面にアクセスして対応してください。

お問い合わせ番号: {{ $inquiry->ticket_number }}
受付日時: {{ optional($inquiry->create_date)->format('Y-m-d H:i') }}

お名前: {{ $inquiry->customer_name }}（{{ $inquiry->customer_nameread }}）
メール: {{ $inquiry->email }}
電話: {{ $inquiry->phone }} / 昼間: {{ $inquiry->dayphone }}
住所: 〒{{ $inquiry->code }} {{ $inquiry->address }}
会員ID: {{ $inquiry->member_id ?: 'ゲスト' }}

内容:
-------------------------------------------
{{ $inquiry->remark }}
-------------------------------------------
