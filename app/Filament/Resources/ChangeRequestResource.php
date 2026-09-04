<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ChangeRequestResource\Pages;
use App\Models\ChangeRequest;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ChangeRequestResource extends Resource
{
    protected static ?string $model = ChangeRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '変更管理';

    protected static ?string $modelLabel = '変更管理';

    protected static ?string $pluralModelLabel = '変更管理';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('approve_day')->label(FieldLabels::ja('approve_day')),
                Forms\Components\TextInput::make('approver')->label(FieldLabels::ja('approver'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('changemaker')->label(FieldLabels::ja('changemaker'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('do_content')->label(FieldLabels::ja('do_content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('do_hours')->label(FieldLabels::ja('do_hours'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('done_day')->label(FieldLabels::ja('done_day')),
                Forms\Components\DateTimePicker::make('dotoday')->label(FieldLabels::ja('dotoday')),
                Forms\Components\DateTimePicker::make('duedate')->label(FieldLabels::ja('duedate')),
                Forms\Components\Textarea::make('function_name')->label(FieldLabels::ja('function_name'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('hour_estimation')->label(FieldLabels::ja('hour_estimation'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('judge_day')->label(FieldLabels::ja('judge_day')),
                Forms\Components\TextInput::make('judge_person_custmer')->label(FieldLabels::ja('judge_person_custmer'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('judge_person_system')->label(FieldLabels::ja('judge_person_system'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('judge_result')->label(FieldLabels::ja('judge_result'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('maker')->label(FieldLabels::ja('maker'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('ng_reason')->label(FieldLabels::ja('ng_reason'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('occurrence_day')->label(FieldLabels::ja('occurrence_day')),
                Forms\Components\TextInput::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate')->label(FieldLabels::ja('renewdate')),
                Forms\Components\DateTimePicker::make('research_reply_day')->label(FieldLabels::ja('research_reply_day')),
                Forms\Components\TextInput::make('researcher')->label(FieldLabels::ja('researcher'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('research_result')->label(FieldLabels::ja('research_result'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('scope_of_impact')->label(FieldLabels::ja('scope_of_impact'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('stage')->label(FieldLabels::ja('stage'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('status')->label(FieldLabels::ja('status'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('team_id')->label(FieldLabels::ja('team_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('approve_day')->label(FieldLabels::ja('approve_day'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')->label(FieldLabels::ja('approver'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('category')->label(FieldLabels::ja('category'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('changemaker')->label(FieldLabels::ja('changemaker'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('do_hours')->label(FieldLabels::ja('do_hours'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('done_day')->label(FieldLabels::ja('done_day'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('dotoday')->label(FieldLabels::ja('dotoday'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('duedate')->label(FieldLabels::ja('duedate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('hour_estimation')->label(FieldLabels::ja('hour_estimation'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('judge_day')->label(FieldLabels::ja('judge_day'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('judge_person_custmer')->label(FieldLabels::ja('judge_person_custmer'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('judge_person_system')->label(FieldLabels::ja('judge_person_system'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('judge_result')->label(FieldLabels::ja('judge_result'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('maker')->label(FieldLabels::ja('maker'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('occurrence_day')->label(FieldLabels::ja('occurrence_day'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')->label(FieldLabels::ja('renewdate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('research_reply_day')->label(FieldLabels::ja('research_reply_day'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('researcher')->label(FieldLabels::ja('researcher'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('stage')->label(FieldLabels::ja('stage'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')->label(FieldLabels::ja('status'))
                    ->numeric()
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
