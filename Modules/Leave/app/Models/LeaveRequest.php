<?php

namespace Modules\Leave\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Auth\Models\User;
use Modules\Employees\Models\Employee;
use Modules\Leave\Database\Factories\LeaveRequestFactory;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days_count',
        'status',
        'manager_approved_by',
        'manager_approved_at',
        'admin_approved_by',
        'admin_approved_at',
        'rejection_reason',
        'document_path',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'leave_type_id' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'days_count' => 'decimal:2',
            'manager_approved_by' => 'integer',
            'manager_approved_at' => 'datetime',
            'admin_approved_by' => 'integer',
            'admin_approved_at' => 'datetime',
        ];
    }


    protected static function newFactory(): LeaveRequestFactory
    {
        return LeaveRequestFactory::new();
    }
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function managerApprover(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'manager_approved_by'
        );
    }

    public function adminApprover(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'admin_approved_by'
        );
    }
}
