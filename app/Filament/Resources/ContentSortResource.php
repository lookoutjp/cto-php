<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentSortResource\Pages;
use App\Models\ContentSort;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentSortResource extends Resource
{
    protected static ?string $model = ContentSort::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'コンテンツカテゴリ';

    protected static ?string $modelLabel = 'コンテンツカテゴリ';

    protected static ?string $pluralModelLabel = 'コンテンツカテゴリ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('categoryimage')->label(FieldLabels::ja('categoryimage'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('father_id')->label(FieldLabels::ja('father_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('introduce')->label(FieldLabels::ja('introduce'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('koukaiflag')->label(FieldLabels::ja('koukaiflag'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('link')->label(FieldLabels::ja('link'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('manager')->label(FieldLabels::ja('manager'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('name')->label(FieldLabels::ja('name'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('ninshouspecial')->label(FieldLabels::ja('ninshouspecial'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('tobbs')->label(FieldLabels::ja('tobbs'))
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoryimage')->label(FieldLabels::ja('categoryimage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('father_id')->label(FieldLabels::ja('father_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('koukaiflag')->label(FieldLabels::ja('koukaiflag'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager')->label(FieldLabels::ja('manager'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->label(FieldLabels::ja('name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tobbs')->label(FieldLabels::ja('tobbs'))
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
            'index' => Pages\ListContentSorts::route('/'),
            'create' => Pages\CreateContentSort::route('/create'),
            'edit' => Pages\EditContentSort::route('/{record}/edit'),
        ];
    }
}
