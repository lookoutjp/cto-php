<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyChoiceResource\Pages;
use App\Filament\Resources\SurveyChoiceResource\RelationManagers;
use App\Models\SurveyChoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SurveyChoiceResource extends Resource
{
    protected static ?string $model = SurveyChoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('choice_explain')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('choice_number')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('choice_title')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('survey_id')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('choice_number')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('choice_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('survey_id')
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
            'index' => Pages\ListSurveyChoices::route('/'),
            'create' => Pages\CreateSurveyChoice::route('/create'),
            'edit' => Pages\EditSurveyChoice::route('/{record}/edit'),
        ];
    }
}
