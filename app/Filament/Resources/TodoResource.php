<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\TodoResource\Pages;
use App\Models\Todo;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TodoResource extends Resource
{
    protected static ?string $model = Todo::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'TODO';

    protected static ?int $navigationSort = 330;

    protected static ?string $modelLabel = 'TODO';

    protected static ?string $pluralModelLabel = 'TODO';

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
                Forms\Components\Textarea::make('completioncriteria')->label(FieldLabels::ja('completioncriteria'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('completion_date')->label(FieldLabels::ja('completion_date')),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dotoday')->label(FieldLabels::ja('dotoday')),
                Forms\Components\DateTimePicker::make('duedate')->label(FieldLabels::ja('duedate')),
                Forms\Components\TextInput::make('maker')->label(FieldLabels::ja('maker'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('renewdate')->label(FieldLabels::ja('renewdate')),
                Forms\Components\Textarea::make('situation')->label(FieldLabels::ja('situation'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('status')->label(FieldLabels::ja('status'))
                    ->numeric()
                    ->default(null),
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
                Tables\Columns\TextColumn::make('maker')->label(FieldLabels::ja('maker'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('person_do')->label(FieldLabels::ja('person_do'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('renewdate')->label(FieldLabels::ja('renewdate'))
                    ->dateTime()
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
            'index' => Pages\ListTodos::route('/'),
            'create' => Pages\CreateTodo::route('/create'),
            'edit' => Pages\EditTodo::route('/{record}/edit'),
        ];
    }
}
