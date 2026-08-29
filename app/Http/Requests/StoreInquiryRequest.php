<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100'],
            'customer_nameread' => ['nullable', 'string', 'max:300'],
            'address' => ['nullable', 'string', 'max:200'],
            'code' => ['nullable', 'string', 'max:16'],
            'phone' => ['nullable', 'string', 'max:100'],
            'dayphone' => ['nullable', 'string', 'max:100'],
            'remark' => ['required', 'string', 'max:10000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'お名前',
            'email' => 'メールアドレス',
            'customer_nameread' => 'お名前ふりがな',
            'address' => 'ご住所',
            'code' => '郵便番号',
            'phone' => 'お電話番号',
            'dayphone' => '昼間お電話番号',
            'remark' => 'お問い合わせ内容',
        ];
    }
}
