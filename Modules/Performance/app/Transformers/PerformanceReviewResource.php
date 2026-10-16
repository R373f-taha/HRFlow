<?php

namespace Modules\Performance\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => data_get($this->resource, 'id'),
            'overall_rating' => data_get($this->resource, 'overall_rating'),
            'strengths' => data_get($this->resource, 'strengths'),

            'goals' => data_get($this->resource, 'goals'),
            'areas_for_improvements' => data_get($this->resource, 'areas_for_improvements'),
            'employee' => [
                'id' => data_get($this->resource, 'employee.id'),
                'name' => data_get($this->resource, 'employee.name'),
            ],
            'manager' => [
                'id' => data_get($this->resource, 'manager.id'),
                'name' => data_get($this->resource, 'manager.name'),
            ],
            'performance_cycle' => [
                'id' => data_get($this->resource, 'performance_cycle.id'),
                'title' => data_get($this->resource, 'performance_cycle.title'),
            ],
        ];
    }
}
