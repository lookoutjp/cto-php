<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileItemResource\Pages;
use App\Models\FileItem;
use App\Models\FileTag;
use App\Support\CurrentSite;
use App\Support\FileStorage;
use App\Support\Plans;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileItemResource extends Resource
{
    protected static ?string $model = FileItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationLabel = 'ファイル';

    protected static ?string $modelLabel = 'ファイル';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('storage_key')
                    ->label('ファイル')
                    ->disk(FileStorage::DISK)
                    ->visibility('private')
                    ->directory(fn () => 'sites/'.app(CurrentSite::class)->id().'/files')
                    ->getUploadedFileNameForStorageUsing(
                        fn ($file) => Str::uuid()->toString().'.'.strtolower($file->getClientOriginalExtension())
                    )
                    ->storeFileNamesIn('filename')
                    ->maxSize(FileStorage::MAX_BYTES / 1024)
                    ->required(fn (string $operation) => $operation === 'create')
                    ->helperText('最大 '.FileStorage::humanSize(FileStorage::MAX_BYTES))
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('intro')
                    ->label('説明')
                    ->maxLength(2000)
                    ->columnSpanFull(),

                Forms\Components\Select::make('tags')
                    ->label('タグ')
                    ->multiple()
                    ->options(fn () => FileTag::query()->orderBy('tagname')->pluck('tagname', 'tag_id'))
                    ->dehydrated(false)
                    ->afterStateHydrated(fn (Forms\Components\Select $c, ?FileItem $record) => $c->state($record?->tagIds() ?? []))
                    ->helperText('タグの追加・編集は「ファイルタグ」から'),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('filename')
                    ->label('名前')
                    ->formatStateUsing(fn (FileItem $r) => $r->downloadName())
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('fileext')
                    ->label('種類')
                    ->badge(),
                Tables\Columns\TextColumn::make('intro')
                    ->label('説明')
                    ->limit(40)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('追加者')
                    ->default(fn (FileItem $r) => $r->member_id)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('size_bytes')
                    ->label('サイズ')
                    ->formatStateUsing(fn ($state) => FileStorage::humanSize($state))
                    ->sortable(),
                Tables\Columns\IconColumn::make('storage_key')
                    ->label('実体')
                    ->boolean()
                    ->state(fn (FileItem $r) => $r->hasBytes()),
                Tables\Columns\TextColumn::make('adddt')
                    ->label('追加日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('DL')
                    ->icon('heroicon-m-arrow-down-tray')
                    ->visible(fn (FileItem $r) => $r->hasBytes())
                    ->url(fn (FileItem $r) => route('files.download', $r->id), shouldOpenInNewTab: true),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (FileItem $r) => $r->hasBytes() && Storage::disk(FileStorage::DISK)->delete($r->storage_key)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(fn ($records) => $records->each(
                            fn (FileItem $r) => $r->hasBytes() && Storage::disk(FileStorage::DISK)->delete($r->storage_key)
                        )),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFileItems::route('/'),
            'create' => Pages\CreateFileItem::route('/create'),
            'edit' => Pages\EditFileItem::route('/{record}/edit'),
        ];
    }

    /** アップロード後、DBの付随カラム（fileext / filename / size / mime / tag_id / member_id）を整える。 */
    public static function fillFromUpload(array $data, ?FileItem $record = null): array
    {
        $tagIds = collect($data['tags'] ?? [])->map(fn ($v) => (int) $v)->filter()->unique()->values();
        $data['tag_id'] = $tagIds->isEmpty() ? null : ','.$tagIds->implode(',').',';
        unset($data['tags']);

        $key = $data['storage_key'] ?? $record?->storage_key;

        if ($key && Storage::disk(FileStorage::DISK)->exists($key)) {
            $data['fileext'] = strtolower(pathinfo($key, PATHINFO_EXTENSION));
            $data['size_bytes'] = Storage::disk(FileStorage::DISK)->size($key);
            $data['mime'] = Storage::disk(FileStorage::DISK)->mimeType($key) ?: null;

            // storeFileNamesIn('filename') は「元のファイル名.拡張子」を入れるので拡張子を落とす
            if (! empty($data['filename'])) {
                $data['filename'] = pathinfo($data['filename'], PATHINFO_FILENAME);
            }
        }

        if ($record === null) {
            $data['member_id'] = auth()->id();
            $data['adddt'] = now();
            $data['renban'] = 0;
        }

        return $data;
    }
}
