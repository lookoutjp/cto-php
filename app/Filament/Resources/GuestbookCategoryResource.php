<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestbookCategoryResource\Pages;
use App\Models\GuestbookCategory;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuestbookCategoryResource extends Resource
{
    protected static ?string $model = GuestbookCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '掲示板カテゴリ';

    protected static ?int $navigationSort = 80;

    protected static ?string $modelLabel = '掲示板カテゴリ';

    protected static ?string $pluralModelLabel = '掲示板カテゴリ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('intro')->label(FieldLabels::ja('intro'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('madetime')->label(FieldLabels::ja('madetime')),
                Forms\Components\Textarea::make('member')->label(FieldLabels::ja('member'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('name')->label(FieldLabels::ja('name'))
                    ->required()
                    ->maxLength(225),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('madetime')->label(FieldLabels::ja('madetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label(FieldLabels::ja('name'))
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
