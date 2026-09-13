<?php

namespace Modules\Performance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Employees\Models\Employee;
use Modules\Performance\Database\Factories\PerformanceReviewFactory;

class PerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'performance_cycle_id',
        'employee_id',
        'manager_id',
        'overall_rating',
        'strengths',
        'areas_for_improvement',
        'goals',
    ];

    protected function casts(): array
    {
        return [
            'performance_cycle_id' => 'integer',
            'employee_id' => 'integer',
            'manager_id' => 'integer',
            'overall_rating' => 'integer',
        ];
    }

    protected static function newFactory(): PerformanceReviewFactory
    {
        return PerformanceReviewFactory::new();
    }
    public function performanceCycle(): BelongsTo
    {
        return $this->belongsTo(PerformanceCycle::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'manager_id'
        );
    }
}
