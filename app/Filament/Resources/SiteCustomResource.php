<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteCustomResource\Pages;
use App\Filament\Resources\SiteCustomResource\RelationManagers;
use App\Models\SiteCustom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SiteCustomResource extends Resource
{
    protected static ?string $model = SiteCustom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('custcont')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('f1')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('custname')
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
            'index' => Pages\ListSiteCustoms::route('/'),
            'create' => Pages\CreateSiteCustom::route('/create'),
            'edit' => Pages\EditSiteCustom::route('/{record}/edit'),
        ];
    }
}
