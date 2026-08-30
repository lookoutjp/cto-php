<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(Member::class)->ignore($this->user()->member_id, 'member_id'),
            ],
            'nameread' => ['nullable', 'string', 'max:255'],
            'appeal' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'hp' => ['nullable', 'string', 'max:255'],
            'sex' => ['nullable', 'in:0,1'],
            'introduce' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'お名前',
            'email' => 'メールアドレス',
            'nameread' => 'ふりがな',
            'appeal' => 'ニックネーム',
            'phone' => '電話番号',
            'hp' => 'ホームページ',
            'sex' => '性別',
            'introduce' => '自己紹介',
        ];
    }
}
