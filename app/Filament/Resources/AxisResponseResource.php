<?php

namespace App\Filament\Resources;

use App\Models\AxisResponse;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Filament\Resources\AxisResponseResource\Pages;

class AxisResponseResource extends Resource
{
    protected static ?string $model = AxisResponse::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Organizations Management';
    protected static ?string $pluralLabel = 'Axis Responses';
    protected static ?string $label = 'Axis Response';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('organization_id')
                ->relationship('organization', 'name')
                ->label('Organization')
                ->required(),

            Forms\Components\Select::make('axis_id')
                ->relationship('axis', 'title')
                ->label('Axis')
                ->required(),

            Forms\Components\Textarea::make('response_text')
                ->label('Response')
                ->rows(3),

            Forms\Components\FileUpload::make('attachment')
                ->label('Attachment')
                ->directory('responses')
                ->nullable(),

            Forms\Components\TextInput::make('score')
                ->label('Score')
                ->numeric()
                ->minValue(0)
                ->maxValue(100)
                ->suffix('%'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('organization.name')->label('Organization')->sortable()->searchable(),
            Tables\Columns\TextColumn::make('axis.title')->label('Axis'),
            Tables\Columns\TextColumn::make('score')->label('Score')->sortable(),
            Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y'),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\ViewAction::make(),
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAxisResponses::route('/'),
            'create' => Pages\CreateAxisResponse::route('/create'),
            'edit' => Pages\EditAxisResponse::route('/{record}/edit'),
        ];
    }
}
