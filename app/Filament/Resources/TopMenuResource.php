<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TopMenuResource\Pages;
use App\Models\TopMenu;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TopMenuResource extends Resource
{
    protected static ?string $model = TopMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'トップメニュー';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'トップメニュー';

    protected static ?string $pluralModelLabel = 'トップメニュー';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('linkaddress')->label(FieldLabels::ja('linkaddress'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('menuname')->label(FieldLabels::ja('menuname'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('linkaddress')->label(FieldLabels::ja('linkaddress'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('menuname')->label(FieldLabels::ja('menuname'))
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
            'index' => Pages\ListTopMenus::route('/'),
            'create' => Pages\CreateTopMenu::route('/create'),
            'edit' => Pages\EditTopMenu::route('/{record}/edit'),
        ];
    }
}
