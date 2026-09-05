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
                Forms\Components\Textarea::make('name')
                    ->label('タイトル')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('comment')->label(FieldLabels::ja('comment'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('time')
                    ->label('日時')
                    ->maxLength(50)
                    ->default(null),
                // 権限(ninshou)は非表示。DB既定値 0（マイグレーション + モデルの $attributes）。
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('タイトル')
                    ->searchable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('comment')->label(FieldLabels::ja('comment'))
                    ->limit(50),
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('time')->label('日時')
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
