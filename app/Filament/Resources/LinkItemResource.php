<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkItemResource\Pages;
use App\Filament\Resources\LinkItemResource\RelationManagers;
use App\Models\LinkItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LinkItemResource extends Resource
{
    protected static ?string $model = LinkItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('allow')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('com')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('hits')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('homepage')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('jj')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('linktime'),
                Forms\Components\TextInput::make('logo')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('site')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('allow')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hits')
                    ->searchable(),
                Tables\Columns\TextColumn::make('homepage')
                    ->searchable(),
                Tables\Columns\TextColumn::make('linktime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('logo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('site')
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
            'index' => Pages\ListLinkItems::route('/'),
            'create' => Pages\CreateLinkItem::route('/create'),
            'edit' => Pages\EditLinkItem::route('/{record}/edit'),
        ];
    }
}
