<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MailListResource\Pages;
use App\Models\MailList;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MailListResource extends Resource
{
    protected static ?string $model = MailList::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'メール配信リスト';

    protected static ?int $navigationSort = 120;

    protected static ?string $modelLabel = 'メール配信リスト';

    protected static ?string $pluralModelLabel = 'メール配信リスト';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('mail_list_sort')->label(FieldLabels::ja('mail_list_sort'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('name')->label(FieldLabels::ja('name'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('remark')->label(FieldLabels::ja('remark'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('time')->label(FieldLabels::ja('time'))
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('email')->label(FieldLabels::ja('email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('mail_list_sort')->label(FieldLabels::ja('mail_list_sort'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->label(FieldLabels::ja('name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('remark')->label(FieldLabels::ja('remark'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('time')->label(FieldLabels::ja('time'))
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
            'index' => Pages\ListMailLists::route('/'),
            'create' => Pages\CreateMailList::route('/create'),
            'edit' => Pages\EditMailList::route('/{record}/edit'),
        ];
    }
}
