<?php

namespace App\Filament\Resources;

use App\Filament\RelationManagers\AttachmentsRelationManager;
use App\Filament\Resources\ContentResource\Pages;
use App\Models\Content;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'コンテンツ';

    protected static ?string $modelLabel = 'コンテンツ';

    protected static ?string $pluralModelLabel = 'コンテンツ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('adddatetime')->label(FieldLabels::ja('adddatetime')),
                Forms\Components\TextInput::make('addtime')->label(FieldLabels::ja('addtime'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('clicks')->label(FieldLabels::ja('clicks'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('commentok')->label(FieldLabels::ja('commentok'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('content_sort')->label(FieldLabels::ja('content_sort'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('createdt')->label(FieldLabels::ja('createdt')),
                Forms\Components\TextInput::make('delitiji')->label(FieldLabels::ja('delitiji'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('edittime')->label(FieldLabels::ja('edittime')),
                Forms\Components\Textarea::make('explain')->label(FieldLabels::ja('explain'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('hlsyosailink')->label(FieldLabels::ja('hlsyosailink'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('introduce')->label(FieldLabels::ja('introduce'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('keyword')->label(FieldLabels::ja('keyword'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(225)
                    ->default(null),
                Forms\Components\Textarea::make('name')->label(FieldLabels::ja('name'))
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('nameintro')->label(FieldLabels::ja('nameintro'))
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('ok')->label(FieldLabels::ja('ok'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('okngflag')->label(FieldLabels::ja('okngflag'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('oktime')->label(FieldLabels::ja('oktime'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('owner')->label(FieldLabels::ja('owner'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('recommend')->label(FieldLabels::ja('recommend'))
                    ->numeric()
                    ->default(null),
                Forms\Components\DateTimePicker::make('recommend_date')->label(FieldLabels::ja('recommend_date')),
                Forms\Components\TextInput::make('survey_id')->label(FieldLabels::ja('survey_id'))
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('syokai')->label(FieldLabels::ja('syokai'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('syosai')->label(FieldLabels::ja('syosai'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\Textarea::make('title2')->label(FieldLabels::ja('title2'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('adddatetime')->label(FieldLabels::ja('adddatetime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('addtime')->label(FieldLabels::ja('addtime'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clicks')->label(FieldLabels::ja('clicks'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('commentok')->label(FieldLabels::ja('commentok'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('content_sort')->label(FieldLabels::ja('content_sort'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('createdt')->label(FieldLabels::ja('createdt'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('delitiji')->label(FieldLabels::ja('delitiji'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('edittime')->label(FieldLabels::ja('edittime'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('junban')->label(FieldLabels::ja('junban'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('ninshou')->label(FieldLabels::ja('ninshou'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ok')->label(FieldLabels::ja('ok'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('okngflag')->label(FieldLabels::ja('okngflag'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('oktime')->label(FieldLabels::ja('oktime'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner')->label(FieldLabels::ja('owner'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('recommend')->label(FieldLabels::ja('recommend'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('recommend_date')->label(FieldLabels::ja('recommend_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('survey_id')->label(FieldLabels::ja('survey_id'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('syokai')->label(FieldLabels::ja('syokai'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('syosai')->label(FieldLabels::ja('syosai'))
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
            AttachmentsRelationManager::class,
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
