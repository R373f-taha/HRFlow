<?php

namespace Modules\Leave\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Employees\Models\Employee;
use Modules\Leave\Database\Factories\LeaveBalanceFactory;

class LeaveBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'allocated_days',
        'used_days',
        'remaining_days',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'leave_type_id' => 'integer',
            'year' => 'integer',
            'allocated_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'remaining_days' => 'decimal:2',
        ];

    }


    protected static function newFactory(): LeaveBalanceFactory
    {
        return LeaveBalanceFactory::new();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
