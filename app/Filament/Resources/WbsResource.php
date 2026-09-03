<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\WbsResource\Pages;
use App\Models\Wbs;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WbsResource extends Resource
{
    protected static ?string $model = Wbs::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('actualdays')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('approver')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\DateTimePicker::make('complete_date'),
                Forms\Components\Textarea::make('content')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('deep')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('delete_to')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('dotoday'),
                Forms\Components\DateTimePicker::make('duedate'),
                Forms\Components\TextInput::make('father_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('godate'),
                Forms\Components\TextInput::make('iscategory')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('jun')
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
                Forms\Components\DateTimePicker::make('start_date'),
                Forms\Components\TextInput::make('status')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('team_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('tododays')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('tododays_ed')
                    ->numeric()
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('actualdays')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('approver')
                    ->searchable(),
                Tables\Columns\TextColumn::make('complete_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('deep')
                    ->numeric()
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
                Tables\Columns\TextColumn::make('father_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('godate')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('iscategory')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jun')
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
                Tables\Columns\TextColumn::make('start_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('team_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tododays')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tododays_ed')
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
