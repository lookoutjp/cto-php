<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'ステージ／カテゴリ';

    protected static ?int $navigationSort = 170;

    protected static ?string $modelLabel = 'ステージ／カテゴリ';

    protected static ?string $pluralModelLabel = 'ステージ／カテゴリ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('categoryname')->label(FieldLabels::ja('categoryname'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('kind')->label(FieldLabels::ja('kind'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoryname')->label(FieldLabels::ja('categoryname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kind')->label(FieldLabels::ja('kind'))
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
