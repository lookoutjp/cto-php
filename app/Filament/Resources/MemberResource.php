<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Support\CurrentSite;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = '会員';

    protected static ?int $navigationSort = 130;

    protected static ?string $modelLabel = '会員';

    protected static ?string $pluralModelLabel = '会員';

    /**
     * 会員（members）はプラットフォーム全体で一元管理する。
     *   - 既定サイト（cto.jp）の管理画面 / スーパー管理者 … 全会員が見える
     *   - それ以外のサイトの管理画面 … そのサイトで登録した or 加入申請した会員のみ
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();
        $siteId = app(CurrentSite::class)->idOrNull();
        $seesAll = ($user instanceof Member && $user->isSuperAdmin())
            || $siteId === config('app.default_site', 'www');

        if (! $seesAll && $siteId !== null) {
            $memberIds = MemberRoom::query()
                ->withoutGlobalScope('confirmed')
                ->where('site_id', $siteId)
                ->pluck('member_id');

            $query->whereIn('member_id', $memberIds);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('signup_site')->label('登録元サイト')
                    ->maxLength(50)
                    ->helperText('会員が最初に登録したサイトの site_id。')
                    ->default(null),
                Forms\Components\TextInput::make('appeal')->label(FieldLabels::ja('appeal'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('question')->label(FieldLabels::ja('question'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('answer')->label(FieldLabels::ja('answer'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('name')->label(FieldLabels::ja('name'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('nameread')->label(FieldLabels::ja('nameread'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sex')->label(FieldLabels::ja('sex'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('hp')->label(FieldLabels::ja('hp'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('code')->label('郵便番号')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('address')->label(FieldLabels::ja('address'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('addressread')->label(FieldLabels::ja('addressread'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('phone')->label(FieldLabels::ja('phone'))
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('dayphone')->label(FieldLabels::ja('dayphone'))
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('magazine')->label(FieldLabels::ja('magazine'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('online')->label(FieldLabels::ja('online'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('pointm')->label(FieldLabels::ja('pointm'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('pointmtime')->label(FieldLabels::ja('pointmtime')),
                Forms\Components\TextInput::make('regtime')->label('サインアップ日時')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('loginedtime')->label(FieldLabels::ja('loginedtime')),
                Forms\Components\TextInput::make('login_error_times')->label(FieldLabels::ja('login_error_times'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('timerenew')->label(FieldLabels::ja('timerenew')),
                Forms\Components\Textarea::make('introduce')->label(FieldLabels::ja('introduce'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('password')->label(FieldLabels::ja('password'))
                    ->password()
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('signup_site')->label('登録元サイト')
                    ->badge()
                    ->placeholder('—')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site_memberships')->label('所属 / 申請サイト')
                    ->state(fn (Member $record) => MemberRoom::query()
                        ->withoutGlobalScope('confirmed')
                        ->where('member_id', $record->member_id)
                        ->orderBy('site_id')
                        ->get()
                        ->map(fn (MemberRoom $mr) => $mr->site_id.($mr->isPending() ? '（承認待ち）' : ' ['.$mr->ninshouLabel().']'))
                        ->implode(' / '))
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('appeal')->label(FieldLabels::ja('appeal'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('question')->label(FieldLabels::ja('question'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('answer')->label(FieldLabels::ja('answer'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')->label(FieldLabels::ja('name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('nameread')->label(FieldLabels::ja('nameread'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sex')->label(FieldLabels::ja('sex'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')->label(FieldLabels::ja('email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('hp')->label(FieldLabels::ja('hp'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')->label('郵便番号')
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')->label(FieldLabels::ja('address'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('addressread')->label(FieldLabels::ja('addressread'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(FieldLabels::ja('phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('dayphone')->label(FieldLabels::ja('dayphone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('magazine')->label(FieldLabels::ja('magazine'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('online')->label(FieldLabels::ja('online'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pointm')->label(FieldLabels::ja('pointm'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pointmtime')->label(FieldLabels::ja('pointmtime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('regtime')->label('サインアップ日時')
                    ->searchable(),
                Tables\Columns\TextColumn::make('loginedtime')->label(FieldLabels::ja('loginedtime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('login_error_times')->label(FieldLabels::ja('login_error_times'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('timerenew')->label(FieldLabels::ja('timerenew'))
                    ->dateTime()
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
