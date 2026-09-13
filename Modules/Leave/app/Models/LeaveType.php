<?php

namespace Modules\Leave\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Leave\Database\Factories\LeaveTypeFactory;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'annual_days',
        'is_paid',
        'requires_document',
    ];

    protected function casts(): array
    {
        return [
            'annual_days' => 'integer',
            'is_paid' => 'boolean',
            'requires_document' => 'boolean',
        ];
    }

    protected static function newFactory(): LeaveTypeFactory
    {
        return LeaveTypeFactory::new();
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
