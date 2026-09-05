<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentCommentResource\Pages;
use App\Models\ContentComment;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentCommentResource extends Resource
{
    protected static ?string $model = ContentComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'コンテンツコメント';

    protected static ?int $navigationSort = 40;

    protected static ?string $modelLabel = 'コンテンツコメント';

    protected static ?string $pluralModelLabel = 'コンテンツコメント';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('comment')->label(FieldLabels::ja('comment'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('content_id')->label(FieldLabels::ja('content_id'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('name')->label(FieldLabels::ja('name'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->numeric()
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
                Tables\Columns\TextColumn::make('content_id')->label(FieldLabels::ja('content_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListContentComments::route('/'),
            'create' => Pages\CreateContentComment::route('/create'),
            'edit' => Pages\EditContentComment::route('/{record}/edit'),
        ];
    }
}
