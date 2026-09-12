<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LevelResource\Pages;
use App\Models\Level;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class LevelResource extends Resource
{
    protected static ?string $model = Level::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '組織階層';

    protected static ?int $navigationSort = 340;

    protected static ?string $modelLabel = '組織階層';

    protected static ?string $pluralModelLabel = '組織階層';

    /** 一覧画面は使わない（組織図ページ `/admin/org-chart` に置き換え）。 */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

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
                    // 組織図の「サブレベルを追加」から ?father= で指定された値、
                    // または直前の作成で使った親レベル（連続作成用）を初期値にする。
                    ->default(fn () => (string) session('levels.create.father', '0'))
                    ->dehydrateStateUsing(fn ($state) => (int) $state),
                // レベルは常に自動採番（既存の最大値+1）。手動変更はさせない。
                Forms\Components\TextInput::make('level')->label(FieldLabels::ja('level'))
                    ->required()
                    ->numeric()
                    ->default(fn (): int => ((int) Level::query()->max('level')) + 1)
                    ->disabled()
                    ->dehydrated()
                    ->helperText('自動採番されます（既存の最大値 + 1）。変更はできません。'),
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
                    Tables\Actions\DeleteBulkAction::make()
                        // サブレベルが残っている状態で削除すると、それらが孤児（親不明）になってしまうため禁止する。
                        ->before(function (Tables\Actions\DeleteBulkAction $action, Collection $records) {
                            $blocked = $records->filter(
                                fn (Level $l) => Level::query()->where('fatherlevel', $l->level)->exists()
                            );

                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('削除できません')
                                    ->body('サブレベルを持つレベルが含まれています: '.$blocked->pluck('levelname')->implode('、'))
                                    ->send();

                                $action->halt();
                            }
                        }),
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
