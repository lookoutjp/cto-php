<?php

namespace App\Http\Controllers;

use App\Models\ContentSort;
use App\Models\Member;
use App\Support\CurrentSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 公開ページ左サイドバー「カテゴリ」（トップレベルの content_sorts）の
 * 管理者モードでのドラッグ&ドロップ並び替え。子カテゴリ（father_id）には触れない。
 */
class CategoryReorderController extends Controller
{
    public function reorder(Request $request, CurrentSite $currentSite): JsonResponse
    {
        $siteId = $currentSite->idOrNull();
        $user = $request->user();

        abort_unless(
            $siteId !== null && $user instanceof Member && $user->managesSite($siteId),
            403
        );

        $data = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        // トップレベルカテゴリのIDだけを許可する（他サイト・子カテゴリを紛れ込ませられない）。
        $topLevelIds = ContentSort::query()->topLevel()->pluck('id')->all();
        $orderedIds = array_values(array_intersect($data['ids'], $topLevelIds));

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                ContentSort::query()->whereKey($id)->update(['junban' => $index + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }
}
