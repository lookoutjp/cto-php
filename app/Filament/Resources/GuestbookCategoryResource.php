<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestbookCategoryResource\Pages;
use App\Models\GuestbookCategory;
use App\Support\FieldLabels;
use App\Support\MemberOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class GuestbookCategoryResource extends Resource
{
    protected static ?string $model = GuestbookCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '掲示板カテゴリ';

    protected static ?int $navigationSort = 80;

    protected static ?string $modelLabel = '掲示板カテゴリ';

    protected static ?string $pluralModelLabel = '掲示板カテゴリ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('名前')
                    ->required()
                    ->maxLength(225)
                    ->columnSpanFull(),

                TiptapEditor::make('intro')
                    ->label('説明')
                    ->profile('default')
                    ->columnSpanFull(),

                // メンバー（旧 guestbookc.member の "||id||id||" 制限リスト）。
                // 空 = 全参加者に公開。指定するとそのメンバーだけに限定。
                Forms\Components\Select::make('member')
                    ->label('メンバー')
                    ->helperText('指定するとそのメンバーだけがこのコミュニティを閲覧・投稿できます。空なら全員。')
                    ->multiple()
                    ->searchable()
                    ->options(fn () => MemberOptions::forCurrentSite())
                    ->formatStateUsing(fn ($state) => collect(preg_split('/\|\||,/', (string) $state))
                        ->map(fn ($v) => trim($v))
                        ->filter()
                        ->values()
                        ->all())
                    ->dehydrateStateUsing(fn ($state) => empty($state)
                        ? null
                        : '||'.implode('||', $state).'||')
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('madetime')
                    ->label('作成日時')
                    ->seconds(true)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('madetime')->label(FieldLabels::ja('madetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label(FieldLabels::ja('name'))
                    ->searchable(),
            ])
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
            'index' => Pages\ListGuestbookCategories::route('/'),
            'create' => Pages\CreateGuestbookCategory::route('/create'),
            'edit' => Pages\EditGuestbookCategory::route('/{record}/edit'),
        ];
    }
}
