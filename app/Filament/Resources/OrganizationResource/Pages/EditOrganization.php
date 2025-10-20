<?php

namespace App\Filament\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use App\Models\Organization;
use App\Models\AxisResponse;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Mail;
use App\Mail\FinalScoreUpdatedMail;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Organization Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('sector'),
                        Forms\Components\DatePicker::make('established_at')->label('Established'),
                        Forms\Components\TextInput::make('email')->email(),
                        Forms\Components\TextInput::make('phone'),
                        Forms\Components\Textarea::make('address')->rows(3),
                        Forms\Components\TextInput::make('final_score')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->columns(2),

                Forms\Components\Tabs::make('Axes Responses')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('القيادة والحوكمة')
                            ->schema([
                                Forms\Components\Textarea::make('axis_1_response')
                                    ->label('Response')
                                    ->rows(5)
                                    ->default(fn($record) => $this->getAxisResponse($record, 1)?->response_text),
                                Forms\Components\FileUpload::make('axis_1_attachment')
                                    ->label('Attachment')
                                    ->directory('axis-attachments')
                                    ->default(fn($record) => $this->getAxisResponse($record, 1)?->attachment_1),
                                Forms\Components\TextInput::make('axis_1_score')
                                    ->label('Score')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(fn($record) => $this->getAxisResponse($record, 1)?->admin_score),
                            ]),

                        Forms\Components\Tabs\Tab::make('القدرة المالية')
                            ->schema([
                                Forms\Components\Textarea::make('axis_2_response')
                                    ->label('Response')
                                    ->rows(5)
                                    ->default(fn($record) => $this->getAxisResponse($record, 2)?->response_text),
                                Forms\Components\FileUpload::make('axis_2_attachment')
                                    ->label('Attachment')
                                    ->directory('axis-attachments')
                                    ->default(fn($record) => $this->getAxisResponse($record, 2)?->attachment_2),
                                Forms\Components\TextInput::make('axis_2_score')
                                    ->label('Score')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(fn($record) => $this->getAxisResponse($record, 2)?->admin_score),
                            ]),

                        Forms\Components\Tabs\Tab::make('الأثر المجتمعي والابتكار')
                            ->schema([
                                Forms\Components\Textarea::make('axis_3_response')
                                    ->label('Response')
                                    ->rows(5)
                                    ->default(fn($record) => $this->getAxisResponse($record, 3)?->response_text),
                                Forms\Components\FileUpload::make('axis_3_attachment')
                                    ->label('Attachment')
                                    ->directory('axis-attachments')
                                    ->default(fn($record) => $this->getAxisResponse($record, 3)?->attachment_3),
                                Forms\Components\TextInput::make('axis_3_score')
                                    ->label('Score')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(fn($record) => $this->getAxisResponse($record, 3)?->admin_score),
                            ]),

                        Forms\Components\Tabs\Tab::make('الاستراتيجية والابتكار الاجتماعي')
                            ->schema([
                                Forms\Components\Textarea::make('axis_4_response')
                                    ->label('Response')
                                    ->rows(5)
                                    ->default(fn($record) => $this->getAxisResponse($record, 4)?->response_text),
                                Forms\Components\FileUpload::make('axis_4_attachment')
                                    ->label('Attachment')
                                    ->directory('axis-attachments')
                                    ->default(fn($record) => $this->getAxisResponse($record, 4)?->attachment),
                                Forms\Components\TextInput::make('axis_4_score')
                                    ->label('Score')
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(fn($record) => $this->getAxisResponse($record, 4)?->admin_score),
                            ]),
                    ]),
            ]);
    }
    protected function afterSave(): void
{
    // تأكد أن المنظمة لديها إيميل
    if ($this->record->email) {
        Mail::to($this->record->email)->send(new FinalScoreUpdatedMail($this->record));
    }
}

    protected function getAxisResponse($record, int $axisId): ?AxisResponse
    {
        return AxisResponse::where('organization_id', $record->id)
            ->where('axis_id', $axisId)
            ->first();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // حفظ بيانات المنظمة بدون حقول المحاور
        $organizationData = collect($data)->filter(fn($value, $key) => !str_starts_with($key, 'axis_'))->toArray();

        // حفظ ردود المحاور بشكل منفصل
        for ($i = 1; $i <= 4; $i++) {
            $this->saveAxisResponse($i, [
                'response_text' => $data["axis_{$i}_response"] ?? null,
                'attachment_1' => $i === 1 ? $data["axis_{$i}_attachment"] ?? null : null,
                'attachment_2' => $i === 2 ? $data["axis_{$i}_attachment"] ?? null : null,
                'attachment_3' => $i === 3 ? $data["axis_{$i}_attachment"] ?? null : null,
                'attachment'   => $i === 4 ? $data["axis_{$i}_attachment"] ?? null : null,
                'admin_score' => $data["axis_{$i}_score"] ?? null,
            ]);
        }

        return $organizationData;
    }

    protected function saveAxisResponse(int $axisId, array $data): void
    {
        AxisResponse::updateOrCreate(
            [
                'organization_id' => $this->record->id,
                'axis_id' => $axisId,
            ],
            array_filter($data, fn($value) => $value !== null)
        );
    }
}
