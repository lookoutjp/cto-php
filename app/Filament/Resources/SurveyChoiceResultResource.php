<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyChoiceResultResource\Pages;
use App\Models\SurveyChoiceResult;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SurveyChoiceResultResource extends Resource
{
    protected static ?string $model = SurveyChoiceResult::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'サーベイ回答結果';

    protected static ?string $modelLabel = 'サーベイ回答結果';

    protected static ?string $pluralModelLabel = 'サーベイ回答結果';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('choice_number')->label(FieldLabels::ja('choice_number'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dt')->label(FieldLabels::ja('dt')),
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
                Tables\Columns\TextColumn::make('choice_number')->label(FieldLabels::ja('choice_number'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dt')->label(FieldLabels::ja('dt'))
                    ->dateTime()
                    ->sortable(),
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
            'index' => Pages\ListSurveyChoiceResults::route('/'),
            'create' => Pages\CreateSurveyChoiceResult::route('/create'),
            'edit' => Pages\EditSurveyChoiceResult::route('/{record}/edit'),
        ];
    }
}
