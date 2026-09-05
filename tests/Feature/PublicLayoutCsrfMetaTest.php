<?php

namespace Tests\Feature;

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * components.layouts.public（公開フロント共通レイアウト）には
 * <meta name="csrf-token"> が無かった。layouts.app / layouts.guest には
 * あるのに、この1枚だけ抜けており、公開ページ側の fetch() ベースのJS
 * （例: 管理者モードのカテゴリ並び替え category-sortable.js）が
 * CSRFトークンを取得できず 419 で保存に失敗していた（実際にこれで壊れていた）。
 * 退行防止のため、公開ページのHTMLにこのmetaタグが含まれることを確認する。
 */
class PublicLayoutCsrfMetaTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_layout_includes_csrf_token_meta_tag(): void
    {
        Room::create(['site_id' => 'www', 'sitename' => 'テスト', 'site_joutai' => 1, 'function_list' => '']);

        $this->get('/')->assertOk()->assertSee('name="csrf-token"', false);
    }
}
