<?php

namespace Tests\Unit;

use App\Support\FileStorage;
use PHPUnit\Framework\TestCase;

class FileStorageTest extends TestCase
{
    public function test_human_size(): void
    {
        $this->assertSame('—', FileStorage::humanSize(0));
        $this->assertSame('512 B', FileStorage::humanSize(512));
        $this->assertSame('1 KB', FileStorage::humanSize(1024));
        $this->assertSame('1.5 KB', FileStorage::humanSize(1536));
        $this->assertSame('25 MB', FileStorage::humanSize(25 * 1024 * 1024));
    }

    public function test_key_for_is_scoped_and_unique(): void
    {
        $a = FileStorage::keyFor('www', 'PNG');
        $b = FileStorage::keyFor('www', 'png');

        $this->assertStringStartsWith('sites/www/files/', $a);
        $this->assertStringEndsWith('.png', $a);
        $this->assertNotSame($a, $b);
    }

    public function test_is_image(): void
    {
        $this->assertTrue(FileStorage::isImage('JPG'));
        $this->assertFalse(FileStorage::isImage('pdf'));
        $this->assertFalse(FileStorage::isImage(null));
    }
}
