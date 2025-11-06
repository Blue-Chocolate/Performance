<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BlogResource extends \Filament\Resources\Resource
{
    protected static ?string $model = Blog::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Title is required
                Forms\Components\TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true),

                // Short description up to 255
                Forms\Components\TextInput::make('description')
                    ->label('Short Description')
                    ->maxLength(255),

                // Main content with toolbar
                Forms\Components\RichEditor::make('content')
                    ->label('Content')
                    ->columnSpanFull()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'strike',
                        'h2',
                        'h3',
                        'blockquote',
                        'link',
                        'orderedList',
                        'bulletList',
                        'codeBlock',
                        'hr',
                        'undo',
                        'redo',
                    ]),

                // Author name
                Forms\Components\TextInput::make('author')
                    ->label('Author')
                    ->maxLength(120),

                // Image stored at storage/app/public/blogs/images with public disk
                Forms\Components\FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->directory('blogs/images')
                    ->disk('public')
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9')
                    ->maxSize(5120),

                // Published date time
                Forms\Components\DateTimePicker::make('published_at')
                    ->label('Published At')
                    ->seconds(false)
                    ->native(false),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->limit(40)
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Short Description')
                    ->toggleable()
                    ->limit(60),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->disk('public'),

                Tables\Columns\TextColumn::make('author')
                    ->label('Author')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Show only published where published_at is not null and not in future
                Tables\Filters\TernaryFilter::make('published')
                    ->label('Published')
                    ->trueLabel('Published only')
                    ->falseLabel('Unpublished only')
                    ->queries(
                        true: fn (Builder $q) => $q->whereNotNull('published_at')->where('published_at', '<=', now()),
                        false: fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '>', now()),
                        blank: fn (Builder $q) => $q
                    ),

                // Filter by a created range
                Tables\Filters\Filter::make('created_range')
                    ->label('Created Range')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From'),
                        Forms\Components\DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogs::route('/'),
            'create' => Pages\CreateBlog::route('/create'),
            'view' => Pages\ViewBlog::route('/{record}'),
            'edit' => Pages\EditBlog::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'description', 'author'];
    }

    public static function getLabel(): string
    {
        return 'Blog';
    }

    public static function getPluralLabel(): string
    {
        return 'Blogs';
    } }