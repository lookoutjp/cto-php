<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SurveyReplyListResource\Pages;
use App\Filament\Resources\SurveyReplyListResource\RelationManagers;
use App\Models\SurveyReplyList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SurveyReplyListResource extends Resource
{
    protected static ?string $model = SurveyReplyList::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('member_id')
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
                Tables\Columns\TextColumn::make('member_id')
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
            'index' => Pages\ListSurveyReplyLists::route('/'),
            'create' => Pages\CreateSurveyReplyList::route('/create'),
            'edit' => Pages\EditSurveyReplyList::route('/{record}/edit'),
        ];
    }
}
