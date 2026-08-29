<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoutineWorkResource\Pages;
use App\Filament\Resources\RoutineWorkResource\RelationManagers;
use App\Models\RoutineWork;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoutineWorkResource extends Resource
{
    protected static ?string $model = RoutineWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('duedate'),
                Forms\Components\DateTimePicker::make('godate'),
                Forms\Components\TextInput::make('hours_e_month')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('hours_et')
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
                Forms\Components\Textarea::make('situation')
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('approver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('circle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('circle_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duedate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('godate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_e_month')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_et')
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
            'index' => Pages\ListRoutineWorks::route('/'),
            'create' => Pages\CreateRoutineWork::route('/create'),
            'edit' => Pages\EditRoutineWork::route('/{record}/edit'),
        ];
    }
}
