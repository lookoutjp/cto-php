<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * miraipm テナントのロゴを新しい miraiPM ロゴ（public/files/miraipm/miraipm-logo.svg）に差し替える。
 * 旧ロゴ（Access 由来の PNG、実体は移行されておらず 404）を置き換える。
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('rooms')->where('site_id', 'miraipm')->update([
            'logo' => 'files/miraipm/miraipm-logo.svg',
            'logoheight' => 44,
            'logowidth' => null,
        ]);
    }

    public function down(): void
    {
        DB::table('rooms')->where('site_id', 'miraipm')->update([
            'logo' => 'files/miraipm/WebUp/20250721/26_43691_0.png',
            'logoheight' => 57,
            'logowidth' => 125,
        ]);
    }
};
