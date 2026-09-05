<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentSortResource\Pages;
use App\Models\ContentSort;
use App\Support\CurrentSite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ContentSortResource extends Resource
{
    protected static ?string $model = ContentSort::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'コンテンツカテゴリ';

    protected static ?int $navigationSort = 20;

    protected static ?string $modelLabel = 'コンテンツカテゴリ';

    protected static ?string $pluralModelLabel = 'コンテンツカテゴリ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 親カテゴリ（旧 father_id）。トップ(ルート)からの階層をインデントで表示。
                // 自分自身と自分の子孫は循環になるため候補から除外する。
                Forms\Components\Select::make('father_id')
                    ->label('親カテゴリ')
                    ->options(fn (?ContentSort $record) => self::parentOptions($record))
                    ->default(0)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->searchable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(50)
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('link')
                    ->label('リンク')
                    ->rows(2)
                    ->helperText('外部リンクの場合は http:// または https:// から入力してください。'
                        .'入力するとそのリンク先へ直接遷移します。空にすれば通常のカテゴリに戻ります。')
                    ->columnSpanFull(),

                // カテゴリ説明文（旧 introduce）。旧CKEditor相当のリッチテキスト（無料のTipTap）。
                // 表・画像・分割線・文字色・見出し・整列などに対応。
                TiptapEditor::make('introduce')
                    ->label('カテゴリ説明文')
                    ->profile('default')
                    ->disk('s3')
                    ->directory(fn () => 'sites/'.app(CurrentSite::class)->id().'/category-content')
                    ->visibility('public')
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('categoryimage')
                    ->label('カテゴリ画像')
                    ->maxLength(250)
                    ->helperText('画像のURLを直接入力するか、下からアップロードしてください。')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('categoryimage_upload')
                    ->label('画像をアップロード')
                    ->dehydrated(false)
                    ->image()
                    ->disk('s3')
                    ->directory(fn () => 'sites/'.app(CurrentSite::class)->id().'/category-images')
                    ->visibility('public')
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $file = is_array($state) ? Arr::first($state) : $state;

                        if ($file instanceof TemporaryUploadedFile) {
                            $path = $file->store(
                                'sites/'.app(CurrentSite::class)->id().'/category-images',
                                's3'
                            );
                            $set('categoryimage', Storage::disk('s3')->url($path));
                        }
                    })
                    ->columnSpanFull(),

                // 権限（旧 ninshou）。旧ASP: ゲスト / 承認待ち / ユーザ / 管理員。
                // 保存値: ゲスト=NULL, 承認待ち=0, ユーザ=1, 管理員=-1（既存の
                // scopePublicVisible: ninshou が NULL または 0 なら公開、と整合）。
                Forms\Components\Radio::make('ninshou')
                    ->label('権限')
                    ->options([
                        '' => 'ゲスト',
                        '0' => '承認待ち',
                        '1' => 'ユーザ',
                        '-1' => '管理員',
                    ])
                    ->default('')
                    ->helperText('権限順位：管理員 ＞ ユーザ ＞ 承認待 ＞ ゲスト。設定した権限以上の訪問者だけがアクセスできます。')
                    ->formatStateUsing(fn ($state) => $state === null ? '' : (string) $state)
                    ->dehydrateStateUsing(fn ($state) => $state === '' || $state === null ? null : (int) $state)
                    ->columnSpanFull(),

                Forms\Components\Checkbox::make('koukaiflag')
                    ->label('公開フラグ')
                    ->helperText('チェックした場合、メニューに表示され、該当権限を持つ訪問者がアクセスできます。')
                    ->default(true)
                    ->formatStateUsing(fn ($state) => (int) $state === 1)
                    ->dehydrateStateUsing(fn ($state) => $state ? 1 : 0)
                    ->columnSpanFull(),

                // 表示順(junban)は公開ページ左サイドバーのドラッグ&ドロップで
                // 並び替えるため、この画面では編集しない（フォームに出さない）。

                Forms\Components\TextInput::make('manager')
                    ->label('担当者')
                    ->maxLength(50)
                    ->columnSpanFull(),
            ]);
    }

    /**
     * 親カテゴリ選択肢。ルート + 全カテゴリ（自分と子孫を除く）を階層インデントで返す。
     *
     * @return array<int, string>
     */
    private static function parentOptions(?ContentSort $record): array
    {
        $all = ContentSort::query()->orderBy('junban')->orderBy('id')->get();

        // 除外対象: 自分自身と、その子孫すべて（循環防止）
        $excluded = [];
        if ($record) {
            $excluded[$record->id] = true;
            $stack = [$record->id];
            while ($stack) {
                $parentId = array_pop($stack);
                foreach ($all->where('father_id', $parentId) as $child) {
                    if (! isset($excluded[$child->id])) {
                        $excluded[$child->id] = true;
                        $stack[] = $child->id;
                    }
                }
            }
        }

        $byFather = $all->groupBy(fn (ContentSort $c) => (int) $c->father_id);

        $options = [0 => 'ルート'];

        $walk = function (int $fatherId, int $depth) use (&$walk, $byFather, $excluded, &$options) {
            foreach ($byFather->get($fatherId, collect()) as $node) {
                if (isset($excluded[$node->id])) {
                    continue;
                }
                $options[$node->id] = str_repeat('　', $depth).($depth > 0 ? '└ ' : '').$node->name;
                $walk((int) $node->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return $options;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('名前')
                    ->searchable(),
                Tables\Columns\TextColumn::make('father_id')->label('親カテゴリ')
                    ->formatStateUsing(fn ($state) => (int) $state === 0
                        ? 'ルート'
                        : (ContentSort::find($state)?->name ?? $state))
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')->label('表示順')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ninshou')->label('権限')
                    ->formatStateUsing(fn ($state) => match ((string) $state) {
                        '', 'null' => 'ゲスト',
                        '0' => '承認待ち',
                        '1' => 'ユーザ',
                        '-1' => '管理員',
                        default => $state,
                    }),
                Tables\Columns\IconColumn::make('koukaiflag')->label('公開')
                    ->boolean(),
            ])
            ->defaultSort('junban')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentSorts::route('/'),
            'create' => Pages\CreateContentSort::route('/create'),
            'edit' => Pages\EditContentSort::route('/{record}/edit'),
        ];
    }
}
