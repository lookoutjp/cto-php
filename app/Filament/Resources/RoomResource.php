<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('comaddress')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comemail')
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comfax')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comname')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comomanager')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comphone')
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('compostcode')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('copyright')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('favicon')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('function_list')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('homepagemainimage')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('komon')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('logo')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('logoheight')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('logowidth')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('manager_shouko')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('managerwords')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('online')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('pagebackimage')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('pagebackimagerepeat')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('pagetopimage')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('pagewidth')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sitebgcolor')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('sitecolor')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sitedomain')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('siteintro')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('site_joutai')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('site_mail')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sitename')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('sitename_color')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('smtpid')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('smtppass')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('smtpserver')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sw_koukoku')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('webmanager')
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comaddress')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comemail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comfax')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comname')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comomanager')
                    ->searchable(),
                Tables\Columns\TextColumn::make('comphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('compostcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('favicon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('homepagemainimage')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('komon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('logoheight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('logowidth')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager_shouko')
                    ->searchable(),
                Tables\Columns\TextColumn::make('online')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagebackimage')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagebackimagerepeat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagetopimage')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagewidth')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitebgcolor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitecolor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitedomain')
                    ->searchable(),
                Tables\Columns\TextColumn::make('site_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('site_joutai')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site_mail')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitename')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitename_color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('smtpid')
                    ->searchable(),
                Tables\Columns\TextColumn::make('smtppass')
                    ->searchable(),
                Tables\Columns\TextColumn::make('smtpserver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sw_koukoku')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('webmanager')
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
