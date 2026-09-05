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
                Forms\Components\DateTimePicker::make('adddatetime')->label(FieldLabels::ja('adddatetime')),
                Forms\Components\TextInput::make('clicks')->label(FieldLabels::ja('clicks'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('editdatetime')->label(FieldLabels::ja('editdatetime')),
                Forms\Components\TextInput::make('istop')->label(FieldLabels::ja('istop'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('newsdate')->label(FieldLabels::ja('newsdate')),
                Forms\Components\TextInput::make('news_img')->label(FieldLabels::ja('news_img'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('title')->label(FieldLabels::ja('title'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adddatetime')->label(FieldLabels::ja('adddatetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')->label(FieldLabels::ja('clicks'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('editdatetime')->label(FieldLabels::ja('editdatetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('istop')->label(FieldLabels::ja('istop'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('newsdate')->label(FieldLabels::ja('newsdate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('news_img')->label(FieldLabels::ja('news_img'))
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
            'index' => Pages\ListNewsItems::route('/'),
            'create' => Pages\CreateNewsItem::route('/create'),
            'edit' => Pages\EditNewsItem::route('/{record}/edit'),
        ];
    }
}
