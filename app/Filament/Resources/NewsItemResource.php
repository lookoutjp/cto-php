<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsItemResource\Pages;
use App\Models\NewsItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NewsItemResource extends Resource
{
    protected static ?string $model = NewsItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('adddatetime'),
                Forms\Components\TextInput::make('clicks')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('editdatetime'),
                Forms\Components\TextInput::make('istop')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('newsdate'),
                Forms\Components\TextInput::make('news_img')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('title')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adddatetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('editdatetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('istop')
                    ->searchable(),
                Tables\Columns\TextColumn::make('newsdate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('news_img')
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
