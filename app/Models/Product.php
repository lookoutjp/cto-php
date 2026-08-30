<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSite;
use App\Models\Concerns\HasAttachments;
use App\Models\Concerns\TaskModel;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToSite;
    use HasAttachments;
    use TaskModel;

    public static string $taskKind = 'product';
    public static ?string $taskDateColumn = null; // products に期限列は無い

    protected $table = 'products';
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'renewdate' => 'datetime',
    ];
}
