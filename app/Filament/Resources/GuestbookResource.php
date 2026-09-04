<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestbookResource\Pages;
use App\Models\Guestbook;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GuestbookResource extends Resource
{
    protected static ?string $model = Guestbook::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '掲示板';

    protected static ?string $modelLabel = '掲示板';

    protected static ?string $pluralModelLabel = '掲示板';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('answer_date')->label(FieldLabels::ja('answer_date')),
                Forms\Components\TextInput::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('create_date')->label(FieldLabels::ja('create_date')),
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('homepage')->label(FieldLabels::ja('homepage'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('orders')->label(FieldLabels::ja('orders'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('parent')->label(FieldLabels::ja('parent'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('revert')->label(FieldLabels::ja('revert'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('revert_date')->label(FieldLabels::ja('revert_date')),
                Forms\Components\TextInput::make('space_num')->label(FieldLabels::ja('space_num'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('top')->label(FieldLabels::ja('top'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('user_name')->label(FieldLabels::ja('user_name'))
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('answer_date')->label(FieldLabels::ja('answer_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('create_date')->label(FieldLabels::ja('create_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')->label(FieldLabels::ja('email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('homepage')->label(FieldLabels::ja('homepage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('orders')->label(FieldLabels::ja('orders'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent')->label(FieldLabels::ja('parent'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('revert_date')->label(FieldLabels::ja('revert_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('space_num')->label(FieldLabels::ja('space_num'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')->label(FieldLabels::ja('title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('top')->label(FieldLabels::ja('top'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('user_name')->label(FieldLabels::ja('user_name'))
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
