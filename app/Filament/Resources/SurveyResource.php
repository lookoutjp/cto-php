<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyResource\Pages;
use App\Filament\Resources\SurveyResource\RelationManagers;
use App\Models\Survey;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('answer_due_date'),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('member_id')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('open_yn')
                    ->required(),
                Forms\Components\TextInput::make('selectable_numbers')
                    ->numeric()
                    ->default(null),
                Forms\Components\Toggle::make('specify_yn')
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('answer_due_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\IconColumn::make('open_yn')
                    ->boolean(),
                Tables\Columns\TextColumn::make('selectable_numbers')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('specify_yn')
                    ->boolean(),
                Tables\Columns\TextColumn::make('title')
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
            'index' => Pages\ListSurveys::route('/'),
            'create' => Pages\CreateSurvey::route('/create'),
            'edit' => Pages\EditSurvey::route('/{record}/edit'),
        ];
    }
}
