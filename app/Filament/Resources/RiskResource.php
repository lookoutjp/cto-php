<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\RiskResource\Pages;
use App\Models\Risk;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RiskResource extends Resource
{
    protected static ?string $model = Risk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'リスク';

    protected static ?int $navigationSort = 210;

    protected static ?string $modelLabel = 'リスク';

    protected static ?string $pluralModelLabel = 'リスク';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('acteddate')->label(FieldLabels::ja('acteddate')),
                Forms\Components\TextInput::make('approver')->label(FieldLabels::ja('approver'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('area')->label(FieldLabels::ja('area'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('completion_date')->label(FieldLabels::ja('completion_date')),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dotoday')->label(FieldLabels::ja('dotoday')),
                Forms\Components\DateTimePicker::make('duedate')->label(FieldLabels::ja('duedate')),
                Forms\Components\TextInput::make('impact2cost')->label(FieldLabels::ja('impact2cost'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('impact2quality')->label(FieldLabels::ja('impact2quality'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('impact2schedule')->label(FieldLabels::ja('impact2schedule'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('impact2scope')->label(FieldLabels::ja('impact2scope'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('indicator')->label(FieldLabels::ja('indicator'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('maker')->label(FieldLabels::ja('maker'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('monitoreddate')->label(FieldLabels::ja('monitoreddate')),
                Forms\Components\TextInput::make('monitorfrequency')->label(FieldLabels::ja('monitorfrequency'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('probability')->label(FieldLabels::ja('probability'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate')->label(FieldLabels::ja('renewdate')),
                Forms\Components\Textarea::make('situation')->label(FieldLabels::ja('situation'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')->label(FieldLabels::ja('status'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('strategy')->label(FieldLabels::ja('strategy'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('team_id')->label(FieldLabels::ja('team_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('trigger')->label(FieldLabels::ja('trigger'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('unit')->label(FieldLabels::ja('unit'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('acteddate')->label(FieldLabels::ja('acteddate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')->label(FieldLabels::ja('approver'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('area')->label(FieldLabels::ja('area'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completion_date')->label(FieldLabels::ja('completion_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dotoday')->label(FieldLabels::ja('dotoday'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duedate')->label(FieldLabels::ja('duedate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2cost')->label(FieldLabels::ja('impact2cost'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2quality')->label(FieldLabels::ja('impact2quality'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2schedule')->label(FieldLabels::ja('impact2schedule'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2scope')->label(FieldLabels::ja('impact2scope'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('indicator')->label(FieldLabels::ja('indicator'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maker')->label(FieldLabels::ja('maker'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('monitoreddate')->label(FieldLabels::ja('monitoreddate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monitorfrequency')->label(FieldLabels::ja('monitorfrequency'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('probability')->label(FieldLabels::ja('probability'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('renewdate')->label(FieldLabels::ja('renewdate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->label(FieldLabels::ja('status'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('strategy')->label(FieldLabels::ja('strategy'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_id')->label(FieldLabels::ja('team_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')->label(FieldLabels::ja('title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')->label(FieldLabels::ja('unit'))
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
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRisks::route('/'),
            'create' => Pages\CreateRisk::route('/create'),
            'edit' => Pages\EditRisk::route('/{record}/edit'),
        ];
    }
}
