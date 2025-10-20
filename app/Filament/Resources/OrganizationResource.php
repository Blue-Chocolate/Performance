<?php

namespace App\Filament\Resources;

use App\Models\Organization;
use App\Models\AxisResponse;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\OrganizationResource\Pages;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
// use Filament\Infolists\Components\FileEntry; // Removed because FileEntry does not exist
use Filament\Infolists\Infolist;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Organizations Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('sector'),
            Forms\Components\DatePicker::make('established_at')->label('Established'),
            Forms\Components\TextInput::make('email'),
            Forms\Components\TextInput::make('phone'),
            Forms\Components\Textarea::make('address'),
            Forms\Components\TextInput::make('final_score')->disabled()->suffix('%'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('sector'),
                Tables\Columns\TextColumn::make('final_score')
                    ->label('Final Score')
                    ->sortable()
                    ->suffix('%'),
                Tables\Columns\TextColumn::make('created_at')->dateTime('d M Y'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make('Axes')
                ->tabs(function ($record) {
                    $axes = [
                        1 => 'القيادة والحوكمة',
                        2 => 'القدرة المالية',
                        3 => 'الأثر المجتمعي والابتكار',
                        4 => 'الاستراتيجية والابتكار الاجتماعي',
                    ];

                    return collect($axes)->map(function ($label, $axisId) use ($record) {
                        $response = AxisResponse::where('organization_id', $record->id)
                            ->where('axis_id', $axisId)
                            ->first();

                        if (!$response) {
                            return Tab::make($label)
                                ->schema([
                                    TextEntry::make('no_data')
                                        ->label('')
                                        ->default('No response yet.'),
                                ]);
                        }

                        return Tab::make($label)
                            ->schema([
                                TextEntry::make('Q1')
                                    ->default("Q1: " . ($response->q1 ?? 'N/A')),
                                TextEntry::make('Q2')
                                    ->default("Q2: " . ($response->q2 ?? 'N/A')),
                                TextEntry::make('Q3')
                                    ->default("Q3: " . ($response->q3 ?? 'N/A')),
                                TextEntry::make('Q4')
                                    ->default("Q4: " . ($response->q4 ?? 'N/A')),

                                TextEntry::make('attachment_1')
                                    ->label('Attachment 1')
                                    ->default($response->attachment_1 ? '<a href="' . asset('storage/' . $response->attachment_1) . '" target="_blank">Download</a>' : 'No attachment')
                                    ->html(),
                                TextEntry::make('attachment_2')
                                    ->label('Attachment 2')
                                    ->default($response->attachment_2 ? '<a href="' . asset('storage/' . $response->attachment_2) . '" target="_blank">Download</a>' : 'No attachment')
                                    ->html(),
                                TextEntry::make('attachment_3')
                                    ->label('Attachment 3')
                                    ->default($response->attachment_3 ? '<a href="' . asset('storage/' . $response->attachment_3) . '" target="_blank">Download</a>' : 'No attachment')
                                    ->html(),

                                TextInput::make("score_{$axisId}")
                                    ->label('Admin Score')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->default($response->admin_score)
                                    ->suffix('%')
                                    ->reactive()
                                    ->afterStateUpdated(function ($state) use ($response) {
                                        $response->update(['admin_score' => $state]);
                                    }),
                            ]);
                    })->toArray();
                }),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
            'view' => Pages\ViewOrganization::route('/{record}'),
        ];
    }
}
