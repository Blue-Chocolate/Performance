<?php

namespace App\Filament\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use Filament\Resources\Pages\Page;
use App\Models\Organization;
use App\Models\AxisResponse;
use Filament\Notifications\Notification;


class ViewOrganization extends Page
{
    protected static string $resource = OrganizationResource::class;

    protected static string $view = 'filament.resources.organization-resource.pages.view-organization';

    public Organization $organization;
    public array $responses = [];

    public function mount($record): void
    {
        $this->organization = Organization::with(['axesResponses.axis'])->findOrFail($record);

        foreach ($this->organization->axesResponses as $response) {
            $this->responses[$response->id]['admin_score'] = $response->admin_score;
        }
    }

    public function updateScore($id)
{
    $response = AxisResponse::find($id);

    if ($response) {
        $response->update([
            'admin_score' => $this->responses[$id]['admin_score'],
        ]);

        // تحديث النتيجة النهائية
        $this->organization->refresh();

        Notification::make()
            ->title('Score updated successfully!')
            ->success()
            ->send();
    }
}

    public function getTitle(): string
    {
        return $this->organization->name ?? 'Organization Details';
    }
}
