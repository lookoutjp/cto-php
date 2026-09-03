<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ChangeRequestResource\Pages;
use App\Models\ChangeRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChangeRequestResource extends Resource
{
    protected static ?string $model = ChangeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('approve_day'),
                Forms\Components\TextInput::make('approver')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('category')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('changemaker')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('do_content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('do_hours')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('done_day'),
                Forms\Components\DateTimePicker::make('dotoday'),
                Forms\Components\DateTimePicker::make('duedate'),
                Forms\Components\Textarea::make('function_name')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('hour_estimation')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('judge_day'),
                Forms\Components\TextInput::make('judge_person_custmer')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('judge_person_system')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('judge_result')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('maker')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('ng_reason')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('occurrence_day'),
                Forms\Components\TextInput::make('person_do')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate'),
                Forms\Components\DateTimePicker::make('research_reply_day'),
                Forms\Components\TextInput::make('researcher')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('research_result')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('scope_of_impact')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('stage')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('status')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('team_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('approve_day')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('changemaker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('delete_to')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('do_hours')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('done_day')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dotoday')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duedate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hour_estimation')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('judge_day')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('judge_person_custmer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('judge_person_system')
                    ->searchable(),
                Tables\Columns\TextColumn::make('judge_result')
                    ->searchable(),
                Tables\Columns\TextColumn::make('maker')
                    ->searchable(),
                Tables\Columns\TextColumn::make('occurrence_day')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('person_do')
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('research_reply_day')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('researcher')
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')
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
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChangeRequests::route('/'),
            'create' => Pages\CreateChangeRequest::route('/create'),
            'edit' => Pages\EditChangeRequest::route('/{record}/edit'),
        ];
    }
}
