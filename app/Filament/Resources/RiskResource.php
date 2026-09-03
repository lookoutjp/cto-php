<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\RiskResource\Pages;
use App\Models\Risk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RiskResource extends Resource
{
    protected static ?string $model = Risk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('acteddate'),
                Forms\Components\TextInput::make('approver')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('area')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('category')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('completion_date'),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dotoday'),
                Forms\Components\DateTimePicker::make('duedate'),
                Forms\Components\TextInput::make('impact2cost')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('impact2quality')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('impact2schedule')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('impact2scope')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('indicator')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('maker')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('monitoreddate'),
                Forms\Components\TextInput::make('monitorfrequency')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('person_do')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('probability')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate'),
                Forms\Components\Textarea::make('situation')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('strategy')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('team_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('trigger')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('unit')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('acteddate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('area')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('completion_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dotoday')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duedate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2quality')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2schedule')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('impact2scope')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('indicator')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('maker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('monitoreddate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('monitorfrequency')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('person_do')
                    ->searchable(),
                Tables\Columns\TextColumn::make('probability')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('renewdate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('strategy')
                    ->searchable(),
                Tables\Columns\TextColumn::make('team_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
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
