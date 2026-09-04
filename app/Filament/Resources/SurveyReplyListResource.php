<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyReplyListResource\Pages;
use App\Models\SurveyReplyList;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SurveyReplyListResource extends Resource
{
    protected static ?string $model = SurveyReplyList::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'サーベイ回答者一覧';

    protected static ?string $modelLabel = 'サーベイ回答者一覧';

    protected static ?string $pluralModelLabel = 'サーベイ回答者一覧';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('survey_id')->label(FieldLabels::ja('survey_id'))
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('survey_id')->label(FieldLabels::ja('survey_id'))
                    ->numeric()
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
            'index' => Pages\ListSurveyReplyLists::route('/'),
            'create' => Pages\CreateSurveyReplyList::route('/create'),
            'edit' => Pages\EditSurveyReplyList::route('/{record}/edit'),
        ];
    }
}
