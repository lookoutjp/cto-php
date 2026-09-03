<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('category')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('maker')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('person_do')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate'),
                Forms\Components\TextInput::make('responsible_party')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('stage')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('status')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_do')
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsible_party')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
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
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
