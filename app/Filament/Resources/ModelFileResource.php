<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModelFileResource\Pages;
use App\Models\ModelFile;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ModelFileResource extends Resource
{
    protected static ?string $model = ModelFile::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Models';
    protected static ?string $pluralModelLabel = 'Models';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Model Name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->nullable(),

                Forms\Components\FileUpload::make('file_path')
                    ->label('File (PDF, DOCX, Excel, PTXX)')
                    ->disk('public')
                    ->directory('models')
                    ->visibility('public')
                    // ->preserveFilenames()
                    ->acceptedFileTypes([
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    ])
                    ->maxSize(50000)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Model Name')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('File')
                    ->formatStateUsing(fn($state) =>
                        $state
                            ? "<a href='" . asset('storage/' . $state) . "' target='_blank' class='text-primary-600 underline'>Download</a>"
                            : 'No File'
                    )
                    ->html(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModelFiles::route('/'),
            'create' => Pages\CreateModelFile::route('/create'),
            'edit' => Pages\EditModelFile::route('/{record}/edit'),
        ];
    }
}
