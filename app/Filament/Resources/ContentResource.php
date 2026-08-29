<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContentResource\Pages;
use App\Filament\Resources\ContentResource\RelationManagers;
use App\Models\Content;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('adddatetime'),
                Forms\Components\TextInput::make('addtime')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('clicks')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('commentok')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('content_sort')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('createdt'),
                Forms\Components\TextInput::make('delitiji')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('edittime'),
                Forms\Components\Textarea::make('explain')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('hlsyosailink')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('introduce')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('junban')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('keyword')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('member_id')
                    ->maxLength(225)
                    ->default(null),
                Forms\Components\Textarea::make('name')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('nameintro')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ninshou')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('ok')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('okngflag')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('oktime')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('owner')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('recommend')
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('recommend_date'),
                Forms\Components\TextInput::make('survey_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('syokai')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('syosai')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('title2')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adddatetime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('addtime')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commentok')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content_sort')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdt')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delitiji')
                    ->searchable(),
                Tables\Columns\TextColumn::make('edittime')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ok')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('okngflag')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('oktime')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('recommend')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recommend_date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('survey_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('syokai')
                    ->searchable(),
                Tables\Columns\TextColumn::make('syosai')
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
            'index' => Pages\ListContents::route('/'),
            'create' => Pages\CreateContent::route('/create'),
            'edit' => Pages\EditContent::route('/{record}/edit'),
        ];
    }
}
