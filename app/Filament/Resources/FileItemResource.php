<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileItemResource\Pages;
use App\Filament\Resources\FileItemResource\RelationManagers;
use App\Models\FileItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FileItemResource extends Resource
{
    protected static ?string $model = FileItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('adddt'),
                Forms\Components\TextInput::make('fileext')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('filename')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('intro')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('member_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('renban')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('tag_id')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adddt')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fileext')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('renban')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListFileItems::route('/'),
            'create' => Pages\CreateFileItem::route('/create'),
            'edit' => Pages\EditFileItem::route('/{record}/edit'),
        ];
    }
}
