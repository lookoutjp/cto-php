<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('address')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('addressread')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('answer')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('appeal')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('code')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('dayphone')
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('hp')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('id')
                    ->label('ID')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('introduce')
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('loginedtime'),
                Forms\Components\TextInput::make('login_error_times')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('magazine')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('name')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('nameread')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('online')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('pointm')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('pointmtime'),
                Forms\Components\TextInput::make('question')
                    ->maxLength(250)
                    ->default(null),
                Forms\Components\TextInput::make('regtime')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('sex')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('timerenew'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('addressread')
                    ->searchable(),
                Tables\Columns\TextColumn::make('answer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('appeal')
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('dayphone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('hp')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('loginedtime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('login_error_times')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('magazine')
                    ->searchable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nameread')
                    ->searchable(),
                Tables\Columns\TextColumn::make('online')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pointm')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pointmtime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('question')
                    ->searchable(),
                Tables\Columns\TextColumn::make('regtime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sex')
                    ->searchable(),
                Tables\Columns\TextColumn::make('timerenew')
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
