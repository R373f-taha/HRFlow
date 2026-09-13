<?php

namespace Modules\Employees\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Auth\Models\User;
use Modules\Leave\Models\LeaveBalance;
use Modules\Leave\Models\LeaveRequest;
use Modules\Organization\Models\Department;
use Modules\Organization\Models\JobTitle;
use Modules\Attendance\Models\Attendance;
use Modules\Employees\Database\Factories\EmployeeFactory;
use Modules\Payroll\Models\Payslip;

use Modules\Payroll\Models\SalaryStructure;
use Modules\Performance\Models\PerformanceReview;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_number',
        'department_id',
        'job_title_id',
        'manager_id',
        'employment_type',
        'hire_date',
        'termination_date',
        'termination_reason',
        'status',
        'national_id',
        'phone',
        'address',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'department_id' => 'integer',
            'job_title_id' => 'integer',
            'manager_id' => 'integer',
            'hire_date' => 'date',
            'termination_date' => 'date',
        ];
    }

    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function jobTitle(): BelongsTo
    {
        return $this->belongsTo(JobTitle::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'manager_id'
        );
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(
            self::class,
            'manager_id'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(SalaryStructure::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }
}
