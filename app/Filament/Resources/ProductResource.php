<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '成果物';

    protected static ?int $navigationSort = 200;

    protected static ?string $modelLabel = '成果物';

    protected static ?string $pluralModelLabel = '成果物';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('maker')->label(FieldLabels::ja('maker'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate')->label(FieldLabels::ja('renewdate')),
                Forms\Components\TextInput::make('responsible_party')->label(FieldLabels::ja('responsible_party'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('stage')->label(FieldLabels::ja('stage'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('status')->label(FieldLabels::ja('status'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maker')->label(FieldLabels::ja('maker'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')->label(FieldLabels::ja('renewdate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('responsible_party')->label(FieldLabels::ja('responsible_party'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')->label(FieldLabels::ja('stage'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->label(FieldLabels::ja('status'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')->label(FieldLabels::ja('title'))
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
