<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentSortManagerResource\Pages;
use App\Models\ContentSort;
use App\Models\ContentSortManager;
use App\Models\Member;
use App\Support\CategoryOptions;
use App\Support\MemberOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * カテゴリ管理員（content_sort_managers）。
 *
 * - 会員が公開フロントで「カテゴリ管理員になる」と申請 → status='pending'
 * - スーパー管理員がここで承認 → status='approved'
 * - スーパー管理員が申請なしで直接指定 → create で status='approved'
 *
 * 操作できるのはスーパー管理員のみ（要望どおり）。
 */
class ContentSortManagerResource extends Resource
{
    protected static ?string $model = ContentSortManager::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'カテゴリ管理員';

    protected static ?int $navigationSort = 145;

    protected static ?string $modelLabel = 'カテゴリ管理員';

    protected static ?string $pluralModelLabel = 'カテゴリ管理員';

    public static function canAccess(): bool
    {
        return auth()->user() instanceof Member && auth()->user()->isSuperAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getEloquentQuery(): Builder
    {
        // カテゴリ管理員はサイト横断で存在しうる。管理画面では現在サイトのカテゴリ分だけ扱う。
        $siteCatIds = ContentSort::query()->pluck('id');

        return parent::getEloquentQuery()->whereIn('content_sort_id', $siteCatIds);
    }

    public static function getNavigationBadge(): ?string
    {
        $n = static::getEloquentQuery()->pendingApplications()->count();

        return $n > 0 ? (string) $n : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('content_sort_id')
                ->label('カテゴリ')
                ->required()
                ->options(fn () => CategoryOptions::indented())
                ->searchable()
                ->native(false),
            Forms\Components\Select::make('member_id')
                ->label('会員')
                ->required()
                ->options(fn () => MemberOptions::forCurrentSite())
                ->searchable()
                ->native(false),
            Forms\Components\Radio::make('status')
                ->label('状態')
                ->required()
                ->default('approved')
                ->options(['pending' => '申請中', 'approved' => '承認済み']),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.name')->label('カテゴリ')
                    ->description(fn (ContentSortManager $r) => (string) $r->content_sort_id)
                    ->searchable(),
                Tables\Columns\TextColumn::make('member_id')->label('会員')
                    ->formatStateUsing(fn ($state, ContentSortManager $r) => trim((string) $r->member?->name) !== ''
                        ? $r->member->name.'（'.$state.'）'
                        : $state)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')->label('状態')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'approved' ? '承認済み' : '申請中')
                    ->color(fn ($state) => $state === 'approved' ? 'success' : 'warning'),
                Tables\Columns\TextColumn::make('applied_at')->label('申請日時')->dateTime()->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('decided_at')->label('承認日時')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('applied_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('状態')
                    ->options(['pending' => '申請中', 'approved' => '承認済み']),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('承認')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ContentSortManager $r) => $r->isPending())
                    ->action(function (ContentSortManager $record) {
                        $record->forceFill([
                            'status' => 'approved',
                            'decided_at' => now(),
                            'decided_by' => auth()->id(),
                        ])->save();
                    })
                    ->successNotificationTitle('カテゴリ管理員として承認しました。'),
                Tables\Actions\Action::make('reject')
                    ->label('却下')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ContentSortManager $r) => $r->isPending())
                    ->requiresConfirmation()
                    ->action(fn (ContentSortManager $record) => $record->delete())
                    ->successNotificationTitle('申請を却下しました。'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContentSortManagers::route('/'),
            'create' => Pages\CreateContentSortManager::route('/create'),
            'edit' => Pages\EditContentSortManager::route('/{record}/edit'),
        ];
    }
}
