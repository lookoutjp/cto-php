<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Models\Member;
use App\Models\Room;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * rooms（テナント本体）。Room 自身は BelongsToSite を持てない（テナントの主体そのものなので）。
 * 素の Eloquent クエリは全テナントを返してしまうため、ここで「自分が管理するサイトのみ」に絞る。
 * これを怠ると、あるサイトの管理員が他サイト（本番 www 含む）の設定
 * （function_list・site_joutai・SMTP認証情報等）を編集できてしまう。
 */
class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'サイト設定';

    protected static ?string $modelLabel = 'サイト設定';

    protected static ?string $pluralModelLabel = 'サイト設定';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user instanceof Member && ! $user->isSuperAdmin()) {
            $query->whereIn('site_id', $user->manageableSiteIds());
        }

        return $query;
    }

    /** 新規テナント作成はスーパー管理者のみ（現状セルフサーブのテナント作成は未提供）。 */
    public static function canCreate(): bool
    {
        return auth()->user() instanceof Member && auth()->user()->isSuperAdmin();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('comaddress')->label(FieldLabels::ja('comaddress'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comemail')->label(FieldLabels::ja('comemail'))
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comfax')->label(FieldLabels::ja('comfax'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comname')->label(FieldLabels::ja('comname'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comomanager')->label(FieldLabels::ja('comomanager'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('comphone')->label(FieldLabels::ja('comphone'))
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('compostcode')->label(FieldLabels::ja('compostcode'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('copyright')->label(FieldLabels::ja('copyright'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('favicon')->label(FieldLabels::ja('favicon'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('function_list')->label(FieldLabels::ja('function_list'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('homepagemainimage')->label(FieldLabels::ja('homepagemainimage'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('komon')->label(FieldLabels::ja('komon'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('logo')->label(FieldLabels::ja('logo'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('logoheight')->label(FieldLabels::ja('logoheight'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('logowidth')->label(FieldLabels::ja('logowidth'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('manager_shouko')->label(FieldLabels::ja('manager_shouko'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('managerwords')->label(FieldLabels::ja('managerwords'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('online')->label(FieldLabels::ja('online'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('pagebackimage')->label(FieldLabels::ja('pagebackimage'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('pagebackimagerepeat')->label(FieldLabels::ja('pagebackimagerepeat'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('pagetopimage')->label(FieldLabels::ja('pagetopimage'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('pagewidth')->label(FieldLabels::ja('pagewidth'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sitebgcolor')->label(FieldLabels::ja('sitebgcolor'))
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('sitecolor')->label(FieldLabels::ja('sitecolor'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sitedomain')->label(FieldLabels::ja('sitedomain'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('siteintro')->label(FieldLabels::ja('siteintro'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('site_joutai')->label(FieldLabels::ja('site_joutai'))
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('site_mail')->label(FieldLabels::ja('site_mail'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sitename')->label(FieldLabels::ja('sitename'))
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('sitename_color')->label(FieldLabels::ja('sitename_color'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('smtpid')->label(FieldLabels::ja('smtpid'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('smtppass')->label(FieldLabels::ja('smtppass'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('smtpserver')->label(FieldLabels::ja('smtpserver'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sw_koukoku')->label(FieldLabels::ja('sw_koukoku'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('webmanager')->label(FieldLabels::ja('webmanager'))
                    ->maxLength(50)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comaddress')->label(FieldLabels::ja('comaddress'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('comemail')->label(FieldLabels::ja('comemail'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('comfax')->label(FieldLabels::ja('comfax'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('comname')->label(FieldLabels::ja('comname'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('comomanager')->label(FieldLabels::ja('comomanager'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('comphone')->label(FieldLabels::ja('comphone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('compostcode')->label(FieldLabels::ja('compostcode'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('favicon')->label(FieldLabels::ja('favicon'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('homepagemainimage')->label(FieldLabels::ja('homepagemainimage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('komon')->label(FieldLabels::ja('komon'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('logo')->label(FieldLabels::ja('logo'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('logoheight')->label(FieldLabels::ja('logoheight'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('logowidth')->label(FieldLabels::ja('logowidth'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('manager_shouko')->label(FieldLabels::ja('manager_shouko'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('online')->label(FieldLabels::ja('online'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagebackimage')->label(FieldLabels::ja('pagebackimage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagebackimagerepeat')->label(FieldLabels::ja('pagebackimagerepeat'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagetopimage')->label(FieldLabels::ja('pagetopimage'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('pagewidth')->label(FieldLabels::ja('pagewidth'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitebgcolor')->label(FieldLabels::ja('sitebgcolor'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitecolor')->label(FieldLabels::ja('sitecolor'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitedomain')->label(FieldLabels::ja('sitedomain'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('site_id')->label(FieldLabels::ja('site_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('site_joutai')->label(FieldLabels::ja('site_joutai'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('site_mail')->label(FieldLabels::ja('site_mail'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitename')->label(FieldLabels::ja('sitename'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sitename_color')->label(FieldLabels::ja('sitename_color'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('smtpid')->label(FieldLabels::ja('smtpid'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('smtppass')->label(FieldLabels::ja('smtppass'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('smtpserver')->label(FieldLabels::ja('smtpserver'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sw_koukoku')->label(FieldLabels::ja('sw_koukoku'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('webmanager')->label(FieldLabels::ja('webmanager'))
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
