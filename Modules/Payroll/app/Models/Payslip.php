<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Employees\Models\Employee;
use Modules\Payroll\Database\Factories\PayslipFactory;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'unpaid_leave_deduction',
        'net_salary',
    ];

    protected function casts(): array
    {
        return [
            'payroll_run_id' => 'integer',
            'employee_id' => 'integer',
            'basic_salary' => 'decimal:2',
            'total_allowances' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'unpaid_leave_deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    protected static function newFactory(): PayslipFactory
    {
        return PayslipFactory::new();
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function deductions(): HasMany
    {
        return $this->hasMany(PayslipDeduction::class);
    }
}
