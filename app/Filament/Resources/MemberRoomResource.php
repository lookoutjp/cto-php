<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberRoomResource\Pages;
use App\Models\MemberRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MemberRoomResource extends Resource
{
    protected static ?string $model = MemberRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('legacy_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('ninshou')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('site_id')
                    ->required()
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('legacy_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site_id')
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
            'index' => Pages\ListMemberRooms::route('/'),
            'create' => Pages\CreateMemberRoom::route('/create'),
            'edit' => Pages\EditMemberRoom::route('/{record}/edit'),
        ];
    }
}
