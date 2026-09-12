<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Level;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '組織階層';

    protected static ?int $navigationSort = 340;

    protected static ?string $modelLabel = '組織階層';

    protected static ?string $pluralModelLabel = '組織階層';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('fatherlevel')->label(FieldLabels::ja('fatherlevel'))
                    ->options(fn (?Level $record) => collect(['0' => 'なし（最上位）'])
                        ->union(
                            Level::query()->orderBy('level')->get(['level', 'levelname'])
                                ->reject(fn (Level $l) => $record && (int) $l->level === (int) $record->level)
                                ->mapWithKeys(fn (Level $l) => [
                                    (string) $l->level => trim((string) $l->levelname) !== ''
                                        ? $l->levelname.'（'.$l->level.'）'
                                        : (string) $l->level,
                                ])
                        )
                        ->all())
                    ->native(false)
                    ->searchable()
                    ->default('0')
                    ->dehydrateStateUsing(fn ($state) => (int) $state),
                // レベルは新規作成時のみ自動採番（既存の最大値+1）。編集時は変更可能。
                Forms\Components\TextInput::make('level')->label(FieldLabels::ja('level'))
                    ->required()
                    ->numeric()
                    ->default(fn (): int => ((int) Level::query()->max('level')) + 1)
                    ->disabled(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated()
                    ->helperText(fn (string $operation): ?string => $operation === 'create'
                        ? '新規作成時は自動採番されます（既存の最大値 + 1）。'
                        : null),
                Forms\Components\TextInput::make('levelname')->label(FieldLabels::ja('levelname'))
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('fatherlevel')->label(FieldLabels::ja('fatherlevel'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('level')->label(FieldLabels::ja('level'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('levelname')->label(FieldLabels::ja('levelname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('site_id')->label(FieldLabels::ja('site_id'))
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
            'index' => Pages\ListLevels::route('/'),
            'create' => Pages\CreateLevel::route('/create'),
            'edit' => Pages\EditLevel::route('/{record}/edit'),
        ];
    }
}
