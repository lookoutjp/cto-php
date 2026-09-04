<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileTagResource\Pages;
use App\Models\FileTag;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FileTagResource extends Resource
{
    protected static ?string $model = FileTag::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'ファイルタグ';

    protected static ?string $modelLabel = 'ファイルタグ';

    protected static ?string $pluralModelLabel = 'ファイルタグ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('adddt')->label(FieldLabels::ja('adddt')),
                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('tag_id')->label(FieldLabels::ja('tag_id'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('tag_id_father')->label(FieldLabels::ja('tag_id_father'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('tagname')->label(FieldLabels::ja('tagname'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adddt')->label(FieldLabels::ja('adddt'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('tag_id')->label(FieldLabels::ja('tag_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tag_id_father')->label(FieldLabels::ja('tag_id_father'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tagname')->label(FieldLabels::ja('tagname'))
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
            'index' => Pages\ListFileTags::route('/'),
            'create' => Pages\CreateFileTag::route('/create'),
            'edit' => Pages\EditFileTag::route('/{record}/edit'),
        ];
    }
}
