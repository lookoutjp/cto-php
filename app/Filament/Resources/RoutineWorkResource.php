<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoutineWorkResource\Pages;
use App\Models\RoutineWork;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoutineWorkResource extends Resource
{
    protected static ?string $model = RoutineWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '定例作業マスター';

    protected static ?int $navigationSort = 250;

    protected static ?string $modelLabel = '定例作業マスター';

    protected static ?string $pluralModelLabel = '定例作業マスター';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('approver')->label(FieldLabels::ja('approver'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('circle')->label(FieldLabels::ja('circle'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('circle_number')->label(FieldLabels::ja('circle_number'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('completioncriteria')->label(FieldLabels::ja('completioncriteria'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('duedate')->label(FieldLabels::ja('duedate')),
                Forms\Components\DateTimePicker::make('godate')->label(FieldLabels::ja('godate')),
                Forms\Components\TextInput::make('hours_e_month')->label(FieldLabels::ja('hours_e_month'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('hours_et')->label(FieldLabels::ja('hours_et'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('maker')->label(FieldLabels::ja('maker'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate')->label(FieldLabels::ja('renewdate')),
                Forms\Components\Textarea::make('situation')->label(FieldLabels::ja('situation'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('team_id')->label(FieldLabels::ja('team_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('approver')->label(FieldLabels::ja('approver'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('circle')->label(FieldLabels::ja('circle'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('circle_number')->label(FieldLabels::ja('circle_number'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duedate')->label(FieldLabels::ja('duedate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('godate')->label(FieldLabels::ja('godate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_e_month')->label(FieldLabels::ja('hours_e_month'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hours_et')->label(FieldLabels::ja('hours_et'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maker')->label(FieldLabels::ja('maker'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')->label(FieldLabels::ja('renewdate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team_id')->label(FieldLabels::ja('team_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')->label(FieldLabels::ja('title'))
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
