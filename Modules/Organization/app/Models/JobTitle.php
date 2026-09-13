<?php

namespace Modules\Organization\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Employees\Models\Employee;
use Modules\Organization\Database\Factories\JobTitleFactory;

class JobTitle extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'name',
        'grade',
    ];

    protected function casts(): array
    {
        return [
            'department_id' => 'integer',
        ];
    }


    protected static function newFactory():JobTitleFactory
    {
        return JobTitleFactory::new();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
