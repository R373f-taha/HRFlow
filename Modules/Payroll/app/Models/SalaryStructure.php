<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Employees\Models\Employee;
use Modules\Payroll\Database\Factories\SalaryStructureFactory;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'basic_salary',
        'housing_allowance',
        'transport_allowance',
        'other_allowances',
        'effective_from',
    ];

    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'basic_salary' => 'decimal:2',
            'housing_allowance' => 'decimal:2',
            'transport_allowance' => 'decimal:2',
            'other_allowances' => 'decimal:2',
            'effective_from' => 'date',
        ];
    }

 

    protected static function newFactory(): SalaryStructureFactory
    {
        return SalaryStructureFactory::new();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
