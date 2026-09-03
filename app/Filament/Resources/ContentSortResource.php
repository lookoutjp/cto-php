<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentSortResource\Pages;
use App\Models\ContentSort;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentSortResource extends Resource
{
    protected static ?string $model = ContentSort::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('categoryimage')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('father_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('introduce')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('junban')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('koukaiflag')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('link')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('manager')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('name')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('ninshou')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('ninshouspecial')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('tobbs')
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('categoryimage')
                    ->searchable(),
                Tables\Columns\TextColumn::make('father_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('koukaiflag')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tobbs')
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
            'index' => Pages\ListContentSorts::route('/'),
            'create' => Pages\CreateContentSort::route('/create'),
            'edit' => Pages\EditContentSort::route('/{record}/edit'),
        ];
    }
}
