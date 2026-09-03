<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestbookResource\Pages;
use App\Models\Guestbook;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuestbookResource extends Resource
{
    protected static ?string $model = Guestbook::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('answer_date'),
                Forms\Components\TextInput::make('category')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('create_date'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('homepage')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('orders')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('parent')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('revert')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('revert_date'),
                Forms\Components\TextInput::make('space_num')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('top')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('user_name')
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('answer_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('create_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('homepage')
                    ->searchable(),
                Tables\Columns\TextColumn::make('orders')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent')
                    ->searchable(),
                Tables\Columns\TextColumn::make('revert_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('space_num')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('top')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_name')
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
            'index' => Pages\ListGuestbooks::route('/'),
            'create' => Pages\CreateGuestbook::route('/create'),
            'edit' => Pages\EditGuestbook::route('/{record}/edit'),
        ];
    }
}
