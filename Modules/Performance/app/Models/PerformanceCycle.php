<?php

namespace Modules\Performance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Performance\Database\Factories\PerformanceCycleFactory;

class PerformanceCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
        ];
    }


    protected static function newFactory():PerformanceCycleFactory
    {
        return PerformanceCycleFactory::new();
    }
    public function reviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }
}
