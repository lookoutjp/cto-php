<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberRoomResource\Pages;
use App\Models\Member;
use App\Models\MemberRoom;
use App\Support\CurrentSite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * member_room（テナントごとの会員権限, ninshou）。旧 admin_kengen.asp 相当。
 *
 * member_room は BelongsToSite を使っていない（Member::rooms() 等がサイト横断で
 * 参照するため）。そのためこのリソース自身で「自サイト分のみ」に絞る必要がある。
 * これを怠ると、あるサイトの管理員が他サイト（本番 www 含む）の member_room を
 * 直接編集して自分に ninshou=-1（管理員権限）を付与できてしまう。
 */
class MemberRoomResource extends Resource
{
    protected static ?string $model = MemberRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! ($user instanceof Member && $user->isSuperAdmin())) {
            $query->where('site_id', app(CurrentSite::class)->id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        $isSuperAdmin = auth()->user() instanceof Member && auth()->user()->isSuperAdmin();

        return $form
            ->schema([
                Forms\Components\TextInput::make('legacy_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('member_id')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('ninshou')
                    ->required()
                    ->numeric()
                    ->rules(['in:-1,0,1'])
                    ->helperText('-1: 管理員 / 1: 参加者 / 0: 閲覧のみ'),
                Forms\Components\TextInput::make('site_id')
                    ->required()
                    ->maxLength(50)
                    ->default(fn () => app(CurrentSite::class)->id())
                    // スーパー管理者のみ他サイトへの割り当てが可能。それ以外は現在のサイト固定。
                    ->disabled(fn () => ! $isSuperAdmin)
                    ->dehydrated(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('legacy_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site_id')
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
            'index' => Pages\ListMemberRooms::route('/'),
            'create' => Pages\CreateMemberRoom::route('/create'),
            'edit' => Pages\EditMemberRoom::route('/{record}/edit'),
        ];
    }
}
