<?php

namespace App\Filament\RelationManagers;

use App\Support\CurrentSite;
use App\Support\FileStorage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * どの Filament リソースにも付けられる「添付ファイル」タブ。
 * 対象モデルが App\Models\Concerns\HasAttachments を use していること。
 *
 *   public static function getRelations(): array { return [AttachmentsRelationManager::class]; }
 */
class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = '添付ファイル';

    protected static bool $isLazy = false;

    public static function canViewForRecord(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('storage_key')
                ->label('ファイル')
                ->disk(FileStorage::DISK)
                ->visibility('private')
                ->directory(fn () => 'sites/'.app(CurrentSite::class)->id().'/attachments')
                ->getUploadedFileNameForStorageUsing(
                    fn ($file) => Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension())
                )
                ->storeFileNamesIn('original_name')
                ->maxSize(FileStorage::MAX_BYTES / 1024)
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('original_name')
            ->modelLabel('添付')
            ->pluralModelLabel('添付')
            ->emptyStateHeading('添付はありません')
            ->emptyStateIcon('heroicon-o-paper-clip')
            ->columns([
                Tables\Columns\TextColumn::make('ext')
                    ->label('')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->isImage() ? '🖼' : strtoupper((string) $state)),
                Tables\Columns\TextColumn::make('original_name')->label('名前')->wrap(),
                Tables\Columns\TextColumn::make('size_bytes')
                    ->label('サイズ')
                    ->formatStateUsing(fn ($state) => FileStorage::humanSize($state)),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('追加者')
                    ->default(fn ($record) => $record->member_id),
                Tables\Columns\TextColumn::make('created_at')->label('追加日時')->dateTime('Y/m/d H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('添付を追加')
                    ->mutateFormDataUsing(fn (array $data) => $this->fillAttachment($data)),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('DL')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->url(fn ($record) => route('attachments.download', $record->id), shouldOpenInNewTab: true),
                Tables\Actions\DeleteAction::make()
                    ->before(fn ($record) => Storage::disk(FileStorage::DISK)->delete($record->storage_key)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(fn ($records) => $records->each(
                            fn ($r) => Storage::disk(FileStorage::DISK)->delete($r->storage_key)
                        )),
                ]),
            ]);
    }

    protected function fillAttachment(array $data): array
    {
        $key = $data['storage_key'];

        if (Storage::disk(FileStorage::DISK)->exists($key)) {
            $data['ext'] = strtolower(pathinfo($key, PATHINFO_EXTENSION)) ?: null;
            $data['size_bytes'] = Storage::disk(FileStorage::DISK)->size($key);
            $data['mime'] = Storage::disk(FileStorage::DISK)->mimeType($key) ?: null;
        }

        if (! empty($data['original_name'])) {
            $data['original_name'] = basename($data['original_name']);
        }

        $data['member_id'] = auth()->id();
        $data['created_at'] = now();

        return $data;
    }
}
