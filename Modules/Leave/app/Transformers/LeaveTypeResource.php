<?php

namespace Modules\Leave\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'annual_days' => $this->annual_days,
            'is_paid' => (bool) $this->is_paid,
            'requires_document' => (bool) $this->requires_document,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
