<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PodcastResource\Pages;
use App\Models\Podcast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PodcastResource extends Resource
{
    protected static ?string $model = Podcast::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->label('Description')
                    ->rows(3)
                    ->nullable(),

                Forms\Components\FileUpload::make('cover_image')
                    ->label('Cover Image')
                    ->disk('public')
                    ->directory('podcasts/covers')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->image()
                    ->imageEditor()
                    ->maxSize(5000) // 5MB
                    ->nullable(),

                Forms\Components\FileUpload::make('audio_path')
                    ->label('Audio File')
                    ->disk('public')
                    ->directory('podcasts/audios')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav'])
                    ->maxSize(20000)
                    ->nullable(),

                Forms\Components\FileUpload::make('video_path')
                    ->label('Video File')
                    ->disk('public')
                    ->directory('podcasts/videos')
                    ->visibility('public')
                    ->preserveFilenames()
                    ->acceptedFileTypes(['video/mp4', 'video/webm'])
                    ->maxSize(50000) // 50MB
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('Cover')
                    ->disk('public')
                    ->width(100)
                    ->height(100),

                // مشغل صوت
                Tables\Columns\TextColumn::make('audio_path')
                    ->label('Audio')
                    ->formatStateUsing(fn($state) => 
                        $state
                            ? "<audio controls src='" . asset('storage/' . $state) . "' style='width: 150px;'></audio>"
                            : 'No Audio'
                    )
                    ->html(),

                // مشغل فيديو
                Tables\Columns\TextColumn::make('video_path')
                    ->label('Video')
                    ->formatStateUsing(fn($state) => 
                        $state
                            ? "<video controls src='" . asset('storage/' . $state) . "' style='width: 150px; height: 100px;'></video>"
                            : 'No Video'
                    )
                    ->html(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created'),
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
            'index' => Pages\ListPodcasts::route('/'),
            'create' => Pages\CreatePodcast::route('/create'),
            'edit' => Pages\EditPodcast::route('/{record}/edit'),
        ];
    }
}
