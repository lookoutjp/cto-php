<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageItemResource\Pages;
use App\Models\MessageItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MessageItemResource extends Resource
{
    protected static ?string $model = MessageItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_from')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('from')
                    ->maxLength(225)
                    ->default(null),
                Forms\Components\TextInput::make('readed')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('time'),
                Forms\Components\TextInput::make('to')
                    ->maxLength(225)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delete_from')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from')
                    ->searchable(),
                Tables\Columns\TextColumn::make('readed')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')
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
            'index' => Pages\ListMessageItems::route('/'),
            'create' => Pages\CreateMessageItem::route('/create'),
            'edit' => Pages\EditMessageItem::route('/{record}/edit'),
        ];
    }
}
