<?php

namespace App\Support;

use App\Models\Member;
use App\Models\MemberRoom;

/**
 * Filament の会員選択（投稿者・許可メンバー等）で使う [member_id => ラベル]。
 */
class MemberOptions
{
    /**
     * 現在サイトに所属している会員。
     *
     * @return array<string, string>
     */
    public static function forCurrentSite(): array
    {
        $siteId = app(CurrentSite::class)->id();

        $memberIds = MemberRoom::query()->where('site_id', $siteId)->pluck('member_id');

        return Member::query()
            ->whereIn('member_id', $memberIds)
            ->orderBy('name')
            ->get(['member_id', 'name'])
            ->mapWithKeys(fn (Member $m) => [
                $m->member_id => trim((string) $m->name) !== ''
                    ? trim($m->name).'（'.$m->member_id.'）'
                    : $m->member_id,
            ])
            ->all();
    }

    /**
     * メールアドレスの部分一致でライブ検索する（Filament Select の
     * getSearchResultsUsing 用）。全サイト横断で検索する。
     *
     * @return array<string, string>
     */
    public static function searchByEmail(string $search): array
    {
        if (trim($search) === '') {
            return [];
        }

        return Member::query()
            ->where('email', 'like', '%'.$search.'%')
            ->orderBy('email')
            ->limit(50)
            ->get(['member_id', 'email', 'name'])
            ->mapWithKeys(fn (Member $m) => [$m->member_id => self::emailOptionLabel($m)])
            ->all();
    }

    /** 選択肢の表示形式「メールアドレス（名前）」。 */
    public static function emailOptionLabel(?Member $member): ?string
    {
        if (! $member) {
            return null;
        }

        $email = (string) $member->email;

        return trim((string) $member->name) !== ''
            ? $email.'（'.trim($member->name).'）'
            : $email;
    }
}
