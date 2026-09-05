<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RelationResource\Pages;
use App\Models\Relation;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RelationResource extends Resource
{
    protected static ?string $model = Relation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'タスク関連';

    protected static ?int $navigationSort = 220;

    protected static ?string $modelLabel = 'タスク関連';

    protected static ?string $pluralModelLabel = 'タスク関連';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('id_from')->label(FieldLabels::ja('id_from'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('id_from_kind')->label(FieldLabels::ja('id_from_kind'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('id_to')->label(FieldLabels::ja('id_to'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('id_to_kind')->label(FieldLabels::ja('id_to_kind'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('rtype')->label(FieldLabels::ja('rtype'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_from')->label(FieldLabels::ja('id_from'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_from_kind')->label(FieldLabels::ja('id_from_kind'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_to')->label(FieldLabels::ja('id_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_to_kind')->label(FieldLabels::ja('id_to_kind'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('rtype')->label(FieldLabels::ja('rtype'))
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
            'index' => Pages\ListRelations::route('/'),
            'create' => Pages\CreateRelation::route('/create'),
            'edit' => Pages\EditRelation::route('/{record}/edit'),
        ];
    }
}
