<?php

namespace Modules\Payroll\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Payroll\Models\Payslip;

/**
 * @extends Factory<Payslip>
 */
class PayslipFactory extends Factory
{
    protected $model = Payslip::class;

    public function definition(): array
    {
        $basicSalary = fake()->randomFloat(
            2,
            800,
            5000
        );

        $allowances = fake()->randomFloat(
            2,
            100,
            1500
        );

        $deductions = fake()->randomFloat(
            2,
            0,
            500
        );

        $unpaidLeave = fake()->randomFloat(
            2,
            0,
            300
        );

        $netSalary =
            $basicSalary
            + $allowances
            - $deductions
            - $unpaidLeave;

        return [
            'payroll_run_id' => null,

            'employee_id' => null,

            'basic_salary' => $basicSalary,

            'total_allowances' => $allowances,

            'total_deductions' => $deductions,

            'unpaid_leave_deduction' => $unpaidLeave,

            'net_salary' => max(0, $netSalary),
        ];
    }
}
