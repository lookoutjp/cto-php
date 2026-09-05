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

    protected static ?int $navigationSort = 70;

    protected static ?string $modelLabel = 'ファイルタグ';

    protected static ?string $pluralModelLabel = 'ファイルタグ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tagname')
                    ->label('タグ名')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Forms\Components\Select::make('tag_id_father')
                    ->label('親タグ')
                    ->options(fn (?FileTag $record) => FileTag::query()
                        ->when($record, fn ($q) => $q->where('tag_id', '<>', $record->tag_id))
                        ->orderBy('tagname')
                        ->pluck('tagname', 'tag_id'))
                    ->searchable()
                    ->native(false)
                    ->placeholder('（なし）')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tagname')->label('タグ名')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tag_id_father')->label('親タグ')
                    ->formatStateUsing(fn ($state) => FileTag::query()->where('tag_id', $state)->value('tagname') ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('adddt')->label(FieldLabels::ja('adddt'))
                    ->dateTime('Y/m/d')
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
            'index' => Pages\ListFileTags::route('/'),
            'create' => Pages\CreateFileTag::route('/create'),
            'edit' => Pages\EditFileTag::route('/{record}/edit'),
        ];
    }
}
