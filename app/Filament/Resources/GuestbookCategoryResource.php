<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestbookCategoryResource\Pages;
use App\Filament\Resources\GuestbookCategoryResource\RelationManagers;
use App\Models\GuestbookCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GuestbookCategoryResource extends Resource
{
    protected static ?string $model = GuestbookCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('intro')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('madetime'),
                Forms\Components\Textarea::make('member')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(225),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('madetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
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
            'index' => Pages\ListGuestbookCategories::route('/'),
            'create' => Pages\CreateGuestbookCategory::route('/create'),
            'edit' => Pages\EditGuestbookCategory::route('/{record}/edit'),
        ];
    }
}
