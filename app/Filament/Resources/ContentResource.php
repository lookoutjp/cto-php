<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use App\Models\ContentSort;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Support\CategoryOptions;
use App\Support\CurrentSite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'コンテンツ';

    protected static ?int $navigationSort = 30;

    protected static ?string $modelLabel = 'コンテンツ';

    protected static ?string $pluralModelLabel = 'コンテンツ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('content_sort')
                    ->label('カテゴリ')
                    ->options(fn () => CategoryOptions::indented())
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->label('タイトル')
                    ->required()
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('title2')
                    ->label('副タイトル')
                    ->maxLength(200)
                    ->columnSpanFull(),

                Forms\Components\Select::make('owner')
                    ->label('投稿者')
                    ->options(fn () => self::memberOptions())
                    ->default(fn () => auth()->user()?->getAuthIdentifier())
                    ->native(false)
                    ->searchable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('keyword')
                    ->label('キーワード')
                    ->helperText('キーワードを「,」で区切ってください。')
                    ->maxLength(255)
                    ->columnSpanFull(),

                // 本文（旧 explain）。旧CKEditor相当のリッチテキスト（無料のTipTap）。
                TiptapEditor::make('explain')
                    ->label('本文')
                    ->profile('default')
                    ->disk('s3')
                    ->directory(fn () => 'sites/'.app(CurrentSite::class)->id().'/content')
                    ->visibility('public')
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('adddatetime')
                    ->label('追加日時')
                    ->seconds(true)
                    ->default(now())
                    ->columnSpanFull(),

                Forms\Components\Radio::make('commentok')
                    ->label('コメント設定')
                    ->options([
                        1 => 'コメントを受ける',
                        0 => 'コメントを受けない',
                    ])
                    ->default(0)
                    ->formatStateUsing(fn ($state) => (int) $state === 1 ? 1 : 0)
                    ->dehydrateStateUsing(fn ($state) => (int) $state === 1 ? 1 : 0)
                    ->columnSpanFull(),

                // 公開設定（旧 ok）。下書き=0 / 審査待ち=2 / 公開済み=1。
                // published() スコープ（ok=1）はそのまま。既存データ(0/1)は
                // 下書き / 公開済み に自然対応する。
                Forms\Components\Radio::make('ok')
                    ->label('公開設定')
                    ->options([
                        '0' => '下書き',
                        '2' => '審査待ち',
                        '1' => '公開済み',
                    ])
                    ->default('0')
                    ->formatStateUsing(fn ($state) => (string) ((int) $state))
                    ->dehydrateStateUsing(fn ($state) => (int) $state)
                    ->columnSpanFull(),

                Forms\Components\Fieldset::make('その他')
                    ->schema([
                        Forms\Components\Toggle::make('recommend')
                            ->label('おすすめに表示')
                            ->formatStateUsing(fn ($state) => (int) $state === 1)
                            ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0),
                        Forms\Components\TextInput::make('junban')
                            ->label('表示順')
                            ->numeric()
                            ->helperText('小さいほど上に表示されます。'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * 投稿者(owner)候補: 現在サイトの会員。
     *
     * @return array<string, string>
     */
    private static function memberOptions(): array
    {
        $siteId = app(CurrentSite::class)->id();

        $memberIds = MemberRoom::query()->where('site_id', $siteId)->pluck('member_id');

        return Member::query()
            ->whereIn('member_id', $memberIds)
            ->orderBy('name')
            ->get(['member_id', 'name'])
            ->mapWithKeys(fn (Member $m) => [
                $m->member_id => trim((string) $m->name) !== ''
                    ? trim($m->name).'（'.$m->member_id.'）'
                    : $m->member_id,
            ])
            ->all();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('タイトル')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('content_sort')->label('カテゴリ')
                    ->formatStateUsing(fn ($state) => ContentSort::find($state)?->name ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('owner')->label('投稿者')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ok')->label('公開設定')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ((int) $state) {
                        1 => '公開済み',
                        2 => '審査待ち',
                        default => '下書き',
                    })
                    ->color(fn ($state) => match ((int) $state) {
                        1 => 'success',
                        2 => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('recommend')->label('おすすめ')
                    ->boolean(),
                Tables\Columns\TextColumn::make('adddatetime')->label('追加日時')
                    ->dateTime('Y/m/d H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')->label('閲覧数')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('adddatetime', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
