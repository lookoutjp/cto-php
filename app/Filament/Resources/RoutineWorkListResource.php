<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoutineWorkListResource\Pages;
use App\Filament\Resources\RoutineWorkListResource\RelationManagers;
use App\Models\RoutineWorkList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoutineWorkListResource extends Resource
{
    protected static ?string $model = RoutineWorkList::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('acteddate'),
                Forms\Components\DateTimePicker::make('actiondate'),
                Forms\Components\DateTimePicker::make('add_date_time'),
                Forms\Components\TextInput::make('approver')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('category')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('circle')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('circle_number')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('completioncriteria')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('completion_date'),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dotoday'),
                Forms\Components\TextInput::make('hours_et')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('hours_et_actual')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('junban')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('maker')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('person_do')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate'),
                Forms\Components\TextInput::make('routine_work_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('situation')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('team_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('acteddate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('actiondate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('add_date_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('circle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('circle_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('completion_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dotoday')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_et')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_et_actual')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_do')
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('routine_work_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team_id')
                    ->numeric()
                    ->sortable(),
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
            'index' => Pages\ListRoutineWorkLists::route('/'),
            'create' => Pages\CreateRoutineWorkList::route('/create'),
            'edit' => Pages\EditRoutineWorkList::route('/{record}/edit'),
        ];
    }
}
