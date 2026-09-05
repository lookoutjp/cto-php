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

    protected static ?int $navigationSort = 110;

    protected static ?string $modelLabel = 'リンク集';

    protected static ?string $pluralModelLabel = 'リンク集';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->label(FieldLabels::ja('name'))
                    ->maxLength(255)
                    ->default(null)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('com')->label(FieldLabels::ja('com'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('logo')->label('ロゴ画像アドレス')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('site')->label(FieldLabels::ja('site'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('homepage')->label(FieldLabels::ja('homepage'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('jj')->label('サイト紹介')
                    ->columnSpanFull(),
                Forms\Components\Checkbox::make('allow')->label('許可')
                    ->helperText('チェックすると「許可」になります。')
                    ->default(false)
                    ->formatStateUsing(fn ($state) => (string) $state === '1')
                    ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0')
                    ->columnSpanFull(),
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
