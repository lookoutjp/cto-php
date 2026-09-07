<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentSort;
use App\Models\Member;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentController extends Controller
{
    /**
     * ログイン会員向けに、カテゴリ見出しの「投稿する」「カテゴリ管理員になる」ボタンや
     * 承認待ちの表示を出し分けるためのフラグ群。
     *
     * @return array{isSiteMember: bool, manageableCatIds: int[], pendingCatIds: int[]}
     */
    private function memberContext(Request $request): array
    {
        $user = $request->user();
        if (! $user instanceof Member || ! $user->belongsToSite()) {
            return ['isSiteMember' => false, 'manageableCatIds' => [], 'pendingCatIds' => [], 'pendingCountByCat' => []];
        }

        $manageable = ContentSort::manageableIdsFor($user);

        $pendingCountByCat = $manageable === []
            ? []
            : Content::query()->pendingReview()
                ->whereIn('content_sort', $manageable)
                ->selectRaw('content_sort, count(*) as c')
                ->groupBy('content_sort')
                ->pluck('c', 'content_sort')
                ->map(fn ($v) => (int) $v)
                ->all();

        return [
            'isSiteMember' => true,
            'manageableCatIds' => $manageable,
            'pendingCatIds' => $user->pendingCategoryApplicationIds()->all(),
            'pendingCountByCat' => $pendingCountByCat,
        ];
    }

    /**
     * 旧 contents.asp 相当。
     *   ?q=      キーワード検索（サイト内の公開コンテンツを name/keyword で検索）
     *   ?category=  特定カテゴリの詳細（現在地の直下カテゴリ毎にグループ表示、旧 contents.asp?Contentsort=N）
     *   指定なし  カテゴリ階層をまるごと表示（従来どおり）
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->get('q'));
        $memberCtx = $this->memberContext($request);

        if ($keyword !== '') {
            $results = Content::query()->publiclyVisible()
                ->where(fn ($q) => $q->where('name', 'ilike', "%{$keyword}%")->orWhere('keyword', 'ilike', "%{$keyword}%"))
                ->listingOrder()
                ->get();

            return view('public.contents-index', [
                'mode' => 'search', 'keyword' => $keyword, 'results' => $results,
            ] + $memberCtx);
        }

        $categoryId = $request->integer('category');

        if ($categoryId) {
            $all = ContentSort::query()->publicVisible()->listingOrder()->get();
            $category = $all->firstWhere('id', $categoryId);

            if (! $category) {
                throw new NotFoundHttpException;
            }

            $byFather = $all->groupBy(fn (ContentSort $c) => (int) $c->father_id);

            // 子孫カテゴリ全体（孫以降も含む）の id を集める。旧 contents.asp は
            // カテゴリ単位でなく配下ツリー丸ごとのコンテンツを一覧するため。
            $collectIds = function (int $id) use (&$collectIds, $byFather): array {
                $ids = [$id];
                foreach ($byFather[$id] ?? [] as $child) {
                    $ids = array_merge($ids, $collectIds((int) $child->id));
                }

                return $ids;
            };

            $children = ($byFather[$category->id] ?? collect())
                ->map(function (ContentSort $child) use ($collectIds) {
                    $child->setRelation('contents', Content::query()
                        ->published()
                        ->whereIn('content_sort', $collectIds((int) $child->id))
                        ->listingOrder()
                        ->get());

                    return $child;
                });

            $ownContents = $category->contents()->published()->listingOrder()->get();

            // 管理員が見るときは、このカテゴリ配下の承認待ち投稿もカテゴリページに出す。
            $pendingItems = collect();
            if (in_array((int) $category->id, $memberCtx['manageableCatIds'], true)) {
                $subtreeIds = collect($collectIds((int) $category->id));
                $pendingItems = Content::query()->pendingReview()
                    ->whereIn('content_sort', $subtreeIds->all())
                    ->with(['sort:id,name', 'submitter:member_id,name'])
                    ->orderBy('adddatetime')->orderBy('id')
                    ->get();
            }

            // 「現在位置」をトップ→親→…→本カテゴリの階層で明示するための祖先リスト。
            $byId = $all->keyBy('id');
            $ancestors = [];
            $cursor = $category;
            while ($cursor->father_id) {
                $parent = $byId->get((int) $cursor->father_id) ?? ContentSort::find($cursor->father_id);
                if (! $parent) {
                    break;
                }
                array_unshift($ancestors, $parent);
                $cursor = $parent;
            }

            return view('public.contents-index', [
                'mode' => 'category', 'category' => $category, 'children' => $children,
                'ownContents' => $ownContents, 'ancestors' => $ancestors,
                'pendingItems' => $pendingItems,
            ] + $memberCtx);
        }

        $categories = ContentSort::publicTree();

        return view('public.contents-index', ['mode' => 'tree', 'categories' => $categories] + $memberCtx);
    }

    public function show(Content $content): View
    {
        $visibleSortIds = ContentSort::query()->publicVisible()->pluck('id');

        if ((int) $content->ok !== 1 || ! $visibleSortIds->contains($content->content_sort)) {
            throw new NotFoundHttpException;
        }

        $content->increment('clicks');
        $content->loadMissing('sort');

        return view('public.contents-show', compact('content'));
    }
}
