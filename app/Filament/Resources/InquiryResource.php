<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InquiryResource\Pages;
use App\Models\Inquiry;
use App\Support\FieldLabels;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use FilamentTiptapEditor\TiptapEditor;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'お問い合わせ';

    protected static ?int $navigationSort = 100;

    protected static ?string $modelLabel = 'お問い合わせ';

    protected static ?string $pluralModelLabel = 'お問い合わせ';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('customer_name')->label('お名前')
                    ->maxLength(50)
                    ->default(null)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('customer_nameread')->label(FieldLabels::ja('customer_nameread'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('email')->label(FieldLabels::ja('email'))
                    ->email()
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
                Forms\Components\TextInput::make('code')->label('郵便番号')
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\TextInput::make('address')->label(FieldLabels::ja('address'))
                    ->maxLength(250)
                    ->default(null)
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('title')->label(FieldLabels::ja('title'))
                    ->maxLength(50)
                    ->default(null)
                    ->columnSpanFull(),
                TiptapEditor::make('remark')->label('内容')
                    ->profile('default')
                    ->columnSpanFull(),

                Forms\Components\Radio::make('state')->label('対応状況')
                    ->options([
                        0 => '処理待ち',
                        1 => '処理済',
                        2 => 'ゴミ箱',
                    ])
                    ->default(0)
                    ->formatStateUsing(fn ($state) => (int) $state)
                    ->dehydrateStateUsing(fn ($state) => (int) $state)
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('treated_remark')->label(FieldLabels::ja('treated_remark'))
                    ->columnSpanFull(),
                Forms\Components\DateTimePicker::make('treated_date')->label(FieldLabels::ja('treated_date')),

                Forms\Components\TextInput::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->maxLength(50)
                    ->default(null),
                Forms\Components\DateTimePicker::make('create_date')->label(FieldLabels::ja('create_date')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('address')->label(FieldLabels::ja('address'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('code')->label(FieldLabels::ja('code'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('create_date')->label(FieldLabels::ja('create_date'))
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_name')->label(FieldLabels::ja('customer_name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_nameread')->label(FieldLabels::ja('customer_nameread'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('dayphone')->label(FieldLabels::ja('dayphone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')->label(FieldLabels::ja('email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('member_id')->label(FieldLabels::ja('member_id'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')->label(FieldLabels::ja('phone'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('state')->label(FieldLabels::ja('state'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')->label(FieldLabels::ja('title'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('treated_date')->label(FieldLabels::ja('treated_date'))
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
            'index' => Pages\ListInquiries::route('/'),
            'create' => Pages\CreateInquiry::route('/create'),
            'edit' => Pages\EditInquiry::route('/{record}/edit'),
        ];
    }
}
