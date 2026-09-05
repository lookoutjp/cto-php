<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GuestbookResource\Pages;
use App\Models\Guestbook;
use App\Models\GuestbookCategory;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class GuestbookResource extends Resource
{
    protected static ?string $model = Guestbook::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '掲示板';

    protected static ?int $navigationSort = 90;

    protected static ?string $modelLabel = '掲示板';

    protected static ?string $pluralModelLabel = '掲示板';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category')
                    ->label('カテゴリ')
                    ->options(fn () => GuestbookCategory::query()
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(fn (GuestbookCategory $c) => [$c->id => $c->displayName()]))
                    ->native(false)
                    ->searchable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpanFull(),

                TiptapEditor::make('content')->label(FieldLabels::ja('content'))
                    ->profile('default')
                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('user_name')->label(FieldLabels::ja('user_name'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('homepage')->label(FieldLabels::ja('homepage'))
                    ->maxLength(50)
                    ->default(null),

                // 表示順(orders) / インデント数(space_num) / スレッド先頭ID(top) / 親(parent) /
                // 作成日(create_date) は編集画面では非表示。

                Forms\Components\DateTimePicker::make('answer_date')->label(FieldLabels::ja('answer_date')),

                TiptapEditor::make('revert')->label(FieldLabels::ja('revert'))
                    ->profile('default')
                    ->extraInputAttributes(['style' => 'min-height: 12rem;'])
                    ->columnSpanFull(),

                Forms\Components\DateTimePicker::make('revert_date')->label(FieldLabels::ja('revert_date')),
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
