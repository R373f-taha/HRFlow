<?php

namespace Modules\Organization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Employees\Models\Employee;
use Modules\Organization\Database\Factories\DepartmentFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'parent_id',
        'manager_id',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'manager_id' => 'integer',
        ];
    }

    protected static function newFactory(): DepartmentFactory
    {
        return DepartmentFactory::new();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        );
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class,
            'manager_id'
        );
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function jobTitles(): HasMany
    {
        return $this->hasMany(JobTitle::class);
    }
}
