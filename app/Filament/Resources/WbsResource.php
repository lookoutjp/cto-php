<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\WbsResource\Pages;
use App\Models\Wbs;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WbsResource extends Resource
{
    protected static ?string $model = Wbs::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'WBS';

    protected static ?string $modelLabel = 'WBS';

    protected static ?string $pluralModelLabel = 'WBS';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('actualdays')->label(FieldLabels::ja('actualdays'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('approver')->label(FieldLabels::ja('approver'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('complete_date')->label(FieldLabels::ja('complete_date')),
                Forms\Components\Textarea::make('content')->label(FieldLabels::ja('content'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('deep')->label(FieldLabels::ja('deep'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('delete_to')->label(FieldLabels::ja('delete_to'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dotoday')->label(FieldLabels::ja('dotoday')),
                Forms\Components\DateTimePicker::make('duedate')->label(FieldLabels::ja('duedate')),
                Forms\Components\TextInput::make('father_id')->label(FieldLabels::ja('father_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('godate')->label(FieldLabels::ja('godate')),
                Forms\Components\TextInput::make('iscategory')->label(FieldLabels::ja('iscategory'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('jun')->label(FieldLabels::ja('jun'))
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
                Forms\Components\DateTimePicker::make('start_date')->label(FieldLabels::ja('start_date')),
                Forms\Components\TextInput::make('status')->label(FieldLabels::ja('status'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('team_id')->label(FieldLabels::ja('team_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tododays')->label(FieldLabels::ja('tododays'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('tododays_ed')->label(FieldLabels::ja('tododays_ed'))
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('actualdays')->label(FieldLabels::ja('actualdays'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')->label(FieldLabels::ja('approver'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('complete_date')->label(FieldLabels::ja('complete_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deep')->label(FieldLabels::ja('deep'))
                    ->numeric()
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
                Tables\Columns\TextColumn::make('father_id')->label(FieldLabels::ja('father_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('godate')->label(FieldLabels::ja('godate'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('iscategory')->label(FieldLabels::ja('iscategory'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jun')->label(FieldLabels::ja('jun'))
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
                Tables\Columns\TextColumn::make('start_date')->label(FieldLabels::ja('start_date'))
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
                Tables\Columns\TextColumn::make('tododays')->label(FieldLabels::ja('tododays'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tododays_ed')->label(FieldLabels::ja('tododays_ed'))
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
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWbs::route('/'),
            'create' => Pages\CreateWbs::route('/create'),
            'edit' => Pages\EditWbs::route('/{record}/edit'),
        ];
    }
}
