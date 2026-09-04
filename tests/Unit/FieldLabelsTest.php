<?php

namespace Tests\Unit;

use App\Support\FieldLabels;
use Tests\TestCase;

class FieldLabelsTest extends TestCase
{
    public function test_known_columns_translate_to_japanese(): void
    {
        $this->assertSame('表示順', FieldLabels::ja('junban'));
        $this->assertSame('メニュー名', FieldLabels::ja('menuname'));
        $this->assertSame('サイト名', FieldLabels::ja('sitename'));
    }

    public function test_unknown_column_falls_back_to_the_raw_name(): void
    {
        $this->assertSame('some_unmapped_column', FieldLabels::ja('some_unmapped_column'));
    }
}
