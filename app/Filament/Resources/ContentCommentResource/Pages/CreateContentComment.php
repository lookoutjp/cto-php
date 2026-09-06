<?php

namespace App\Filament\Resources\ContentCommentResource\Pages;

use App\Filament\Concerns\RedirectsCreateBackToOrigin;
use App\Filament\Resources\ContentCommentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateContentComment extends CreateRecord
{
    use RedirectsCreateBackToOrigin;

    protected static string $resource = ContentCommentResource::class;
}
