<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * 実データは schema-gen/load_data.php（旧AccessのCSVから投入）で入れる。
     * 標準の users テーブル / User モデルはこのプロジェクトでは削除済みのため、
     * Breeze 既定の User シーダーは撤去してある。
     */
    public function run(): void
    {
        //
    }
}
