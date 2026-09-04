<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkItemResource\Pages;
use App\Models\LinkItem;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LinkItemResource extends Resource
{
    protected static ?string $model = LinkItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'リンク集';

    protected static ?string $modelLabel = 'リンク集';

    protected static ?string $pluralModelLabel = 'リンク集';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('allow')->label(FieldLabels::ja('allow'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('com')->label(FieldLabels::ja('com'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('hits')->label(FieldLabels::ja('hits'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('homepage')->label(FieldLabels::ja('homepage'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('jj')->label(FieldLabels::ja('jj'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('linktime')->label(FieldLabels::ja('linktime')),
                Forms\Components\TextInput::make('logo')->label(FieldLabels::ja('logo'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('name')->label(FieldLabels::ja('name'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('site')->label(FieldLabels::ja('site'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('allow')->label(FieldLabels::ja('allow'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')->label(FieldLabels::ja('email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('hits')->label(FieldLabels::ja('hits'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('homepage')->label(FieldLabels::ja('homepage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('linktime')->label(FieldLabels::ja('linktime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('logo')->label(FieldLabels::ja('logo'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->label(FieldLabels::ja('name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('site')->label(FieldLabels::ja('site'))
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
