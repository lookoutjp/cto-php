<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Level;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '組織階層';

    protected static ?int $navigationSort = 340;

    protected static ?string $modelLabel = '組織階層';

    protected static ?string $pluralModelLabel = '組織階層';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('fatherlevel')->label(FieldLabels::ja('fatherlevel'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('level')->label(FieldLabels::ja('level'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('levelname')->label(FieldLabels::ja('levelname'))
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fatherlevel')->label(FieldLabels::ja('fatherlevel'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')->label(FieldLabels::ja('level'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('levelname')->label(FieldLabels::ja('levelname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('site_id')->label(FieldLabels::ja('site_id'))
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
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
