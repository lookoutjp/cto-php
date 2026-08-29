<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusMasterResource\Pages;
use App\Filament\Resources\StatusMasterResource\RelationManagers;
use App\Models\StatusMaster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusMasterResource extends Resource
{
    protected static ?string $model = StatusMaster::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('junban')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('kind')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('percent')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('statuscomment')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('statusname')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('junban')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kind')
                    ->searchable(),
                Tables\Columns\TextColumn::make('percent')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('statusname')
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
