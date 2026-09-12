<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberRoomResource\Pages;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Models\Room;
use App\Support\CurrentSite;
use App\Support\FieldLabels;
use App\Support\MemberOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * member_room（テナントごとの会員権限, ninshou）。旧 admin_kengen.asp 相当。
 *
 * member_room は BelongsToSite を使っていない（Member::rooms() 等がサイト横断で
 * 参照するため）。そのためこのリソース自身で「自サイト分のみ」に絞る必要がある。
 * これを怠ると、あるサイトの管理員が他サイト（本番 www 含む）の member_room を
 * 直接編集して自分に ninshou=-1（管理員権限）を付与できてしまう。
 *
 * 加入申請（applied_at 付き・未承認）の行も含めて表示し、「承認 / 却下」アクションで
 * 処理する（MemberRoom の confirmed グローバルスコープをここでは外す）。
 */
class MemberRoomResource extends Resource
{
    protected static ?string $model = MemberRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '会員権限';

    protected static ?int $navigationSort = 140;

    protected static ?string $modelLabel = '会員権限';

    protected static ?string $pluralModelLabel = '会員権限';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScope('confirmed');
        $user = auth()->user();

        if (! ($user instanceof Member && $user->isSuperAdmin())) {
            $query->where('site_id', app(CurrentSite::class)->id());
        }

        return $query;
    }

    /** 承認待ちの件数をナビにバッジ表示。 */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()->pendingRequests()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        $isSuperAdmin = auth()->user() instanceof Member && auth()->user()->isSuperAdmin();

        return $form
            ->schema([
                Forms\Components\Select::make('member_id')
                    ->label('会員（メールアドレスで検索）')
                    ->required()
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search): array => MemberOptions::searchByEmail($search))
                    ->getOptionLabelUsing(fn ($value): ?string => MemberOptions::emailOptionLabel(Member::find($value)))
                    // 会員・サイトは新規作成時にのみ指定する。既存の権限行では変更不可（表示のみ）。
                    ->disabled(fn (string $operation) => $operation === 'edit')
                    ->dehydrated(),
                Forms\Components\Placeholder::make('member_email')
                    ->label(FieldLabels::ja('email'))
                    ->content(fn (?MemberRoom $record) => $record?->member?->email ?? '—')
                    // 編集時のみ表示（新規作成は上の会員選択で email を扱うため不要）。
                    ->visible(fn (string $operation) => $operation === 'edit'),
                Forms\Components\Select::make('site_id')->label(FieldLabels::ja('site_id'))
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->options(fn () => Room::query()->orderBy('site_id')->get(['site_id', 'sitename'])
                        ->mapWithKeys(fn (Room $r) => [
                            $r->site_id => trim((string) $r->sitename) !== ''
                                ? $r->sitename.'（'.$r->site_id.'）'
                                : $r->site_id,
                        ])
                        ->all())
                    ->default(fn () => app(CurrentSite::class)->id())
                    // 編集時は変更不可。新規作成時もスーパー管理者以外は現在のサイト固定。
                    ->disabled(fn (string $operation) => $operation === 'edit' || ! $isSuperAdmin)
                    ->dehydrated(),
                Forms\Components\Radio::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->required()
                    ->options([
                        '0' => '閲覧のみ（コンテンツ閲覧だけ）',
                        '1' => '参加者（プロジェクト機能が使える）',
                        '-1' => '管理員（サイトを管理できる）',
                    ])
                    ->formatStateUsing(fn ($state) => $state === null ? null : (string) $state)
                    ->dehydrateStateUsing(fn ($state) => (int) $state),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('site_id')->label(FieldLabels::ja('site_id'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('member.email')->label(FieldLabels::ja('email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')->label('会員')
                    ->formatStateUsing(fn (MemberRoom $record) => $record->member?->name)
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->badge()
                    ->state(fn (MemberRoom $record) => $record->ninshouLabel())
                    ->color(fn (MemberRoom $record) => match (true) {
                        $record->isPending() => 'warning',
                        (int) $record->ninshou === -1 => 'danger',
                        (int) $record->ninshou === 1 => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('applied_at')->label('加入申請日時')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('approved_at')->label('承認日時')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            // 初期表示の並び順: サイトID → 会員 → 権限 → 加入申請日時 → 承認日時
            ->defaultSort(fn (Builder $query): Builder => $query
                ->orderBy('site_id')
                ->orderBy('member_id')
                ->orderBy('ninshou')
                ->orderBy('applied_at')
                ->orderBy('approved_at'))
            ->filters([
                Tables\Filters\SelectFilter::make('state')
                    ->label('状態')
                    ->options([
                        'pending' => '承認待ち',
                        'approved' => '承認済み',
                    ])
                    ->query(fn (Builder $query, array $data) => match ($data['value'] ?? null) {
                        'pending' => $query->pendingRequests(),
                        'approved' => $query->where(fn (Builder $q) => $q
                            ->whereNull('applied_at')->orWhereNotNull('approved_at')),
                        default => $query,
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('承認')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MemberRoom $record) => $record->isPending())
                    ->form([
                        Forms\Components\Radio::make('ninshou')
                            ->label('権限レベル')
                            ->required()
                            ->default('1')
                            ->options([
                                '0' => '閲覧のみ（コンテンツ閲覧だけ）',
                                '1' => '参加者（プロジェクト機能が使える）',
                                '-1' => '管理員（サイトを管理できる）',
                            ]),
                    ])
                    ->action(function (MemberRoom $record, array $data) {
                        $record->forceFill([
                            'ninshou' => (int) $data['ninshou'],
                            'approved_at' => now(),
                        ])->save();
                    })
                    ->successNotificationTitle('加入を承認しました。'),
                Tables\Actions\Action::make('reject')
                    ->label('却下')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (MemberRoom $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->modalDescription('この加入申請を却下します。会員は再度申請できます。')
                    ->action(fn (MemberRoom $record) => $record->delete())
                    ->successNotificationTitle('加入申請を却下しました。'),
                Tables\Actions\EditAction::make()
                    ->visible(fn (MemberRoom $record) => ! $record->isPending()),
            ], position: ActionsPosition::BeforeColumns)
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
            'index' => Pages\ListMemberRooms::route('/'),
            'create' => Pages\CreateMemberRoom::route('/create'),
            'edit' => Pages\EditMemberRoom::route('/{record}/edit'),
        ];
    }
}
