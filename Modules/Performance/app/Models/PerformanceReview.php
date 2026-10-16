<?php

namespace Modules\Performance\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
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

    /**
     * Scope a query to only include reviews visible to the given user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if (! $user->hasRole('hr-admin') && $user->employee) {
            return $query->where('manager_id', $user->employee->id);
        }

        return $query;
    }
}
