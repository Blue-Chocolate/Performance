<?php

namespace App\Filament\Resources;

use App\Models\Organization;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Forms\Form;
use App\Filament\Resources\OrganizationResource\Pages;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Organizations Management';
    protected static ?string $pluralLabel = 'Organizations';
    protected static ?string $label = 'Organization';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Organization Name')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
