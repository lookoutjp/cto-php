<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MessageItemResource\Pages;
use App\Models\MessageItem;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class MessageItemResource extends Resource
{
    protected static ?string $model = MessageItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'メッセージ';

    protected static ?int $navigationSort = 150;

    protected static ?string $modelLabel = 'メッセージ';

    protected static ?string $pluralModelLabel = 'メッセージ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('from')->label(FieldLabels::ja('from'))
                    ->maxLength(225)
                    ->default(null)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('to')->label(FieldLabels::ja('to'))
                    ->maxLength(225)
                    ->default(null)
                    ->columnSpanFull(),
                TiptapEditor::make('content')->label(FieldLabels::ja('content'))
                    ->profile('default')
                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('time')->label(FieldLabels::ja('time'))
                    ->columnSpanFull(),
                Forms\Components\Checkbox::make('readed')->label(FieldLabels::ja('readed')),
                Forms\Components\Checkbox::make('delete_from')->label(FieldLabels::ja('delete_from'))
                    ->helperText('チェックすると「削除」になります。'),
                Forms\Components\Checkbox::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->helperText('チェックすると「削除」になります。'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('delete_from')->label(FieldLabels::ja('delete_from'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('from')->label(FieldLabels::ja('from'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('readed')->label(FieldLabels::ja('readed'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time')->label(FieldLabels::ja('time'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('to')->label(FieldLabels::ja('to'))
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
