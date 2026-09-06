<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsItemResource\Pages;
use App\Models\NewsItem;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class NewsItemResource extends Resource
{
    protected static ?string $model = NewsItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'ニュース';

    protected static ?int $navigationSort = 160;

    protected static ?string $modelLabel = 'ニュース';

    protected static ?string $pluralModelLabel = 'ニュース';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpanFull(),
                TiptapEditor::make('content')->label(FieldLabels::ja('content'))
                    ->profile('default')
                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                    ->columnSpanFull(),
                Forms\Components\Checkbox::make('istop')->label(FieldLabels::ja('istop'))
                    ->helperText('チェックするとトップページ・一覧の先頭に固定表示されます。')
                    ->formatStateUsing(fn ($state) => (string) $state === '1')
                    ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),
                Forms\Components\DateTimePicker::make('newsdate')->label(FieldLabels::ja('newsdate'))
                    ->seconds(false)
                    ->default(now()),
                Forms\Components\DateTimePicker::make('adddatetime')->label(FieldLabels::ja('adddatetime'))
                    ->default(now()),
                Forms\Components\DateTimePicker::make('editdatetime')->label(FieldLabels::ja('editdatetime')),
                Forms\Components\TextInput::make('clicks')->label(FieldLabels::ja('clicks'))
                    ->numeric()
                    ->default(null),
                // 画像(news_img) は編集画面では非表示。
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label(FieldLabels::ja('title'))
                    ->searchable()
                    ->limit(60),
                Tables\Columns\IconColumn::make('istop')->label(FieldLabels::ja('istop'))
                    ->boolean()
                    ->state(fn ($record) => (string) $record->istop === '1'),
                Tables\Columns\TextColumn::make('newsdate')->label(FieldLabels::ja('newsdate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('adddatetime')->label(FieldLabels::ja('adddatetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('editdatetime')->label(FieldLabels::ja('editdatetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')->label(FieldLabels::ja('clicks'))
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListNewsItems::route('/'),
            'create' => Pages\CreateNewsItem::route('/create'),
            'edit' => Pages\EditNewsItem::route('/{record}/edit'),
        ];
    }
}
