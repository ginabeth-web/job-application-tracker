<?php

namespace App\Http\Resources;

use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Application
 */
class ApplicationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company' => $this->company,
            'role' => $this->role,
            'url' => $this->url,
            'location' => $this->location,
            'salary' => $this->salary,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'position' => $this->position,
            'applied_on' => $this->applied_on?->format('Y-m-d'),
        ];
    }
}
