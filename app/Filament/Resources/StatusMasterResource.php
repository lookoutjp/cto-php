<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusMasterResource\Pages;
use App\Models\StatusMaster;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatusMasterResource extends Resource
{
    protected static ?string $model = StatusMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'ステータスマスター';

    protected static ?string $modelLabel = 'ステータスマスター';

    protected static ?string $pluralModelLabel = 'ステータスマスター';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('kind')->label(FieldLabels::ja('kind'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('percent')->label(FieldLabels::ja('percent'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('statuscomment')->label(FieldLabels::ja('statuscomment'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('statusname')->label(FieldLabels::ja('statusname'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kind')->label(FieldLabels::ja('kind'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('percent')->label(FieldLabels::ja('percent'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statusname')->label(FieldLabels::ja('statusname'))
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
            'index' => Pages\ListStatusMasters::route('/'),
            'create' => Pages\CreateStatusMaster::route('/create'),
            'edit' => Pages\EditStatusMaster::route('/{record}/edit'),
        ];
    }
}
