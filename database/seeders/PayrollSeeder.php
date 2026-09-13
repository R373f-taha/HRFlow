<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Employees\Models\Employee;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\PayslipDeduction;
use Modules\Payroll\Models\SalaryStructure;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('status', 'active')->get();

        foreach ($employees as $employee) {
            SalaryStructure::factory()->create([
                'employee_id' => $employee->id,//Here we provide the real Employee FK.
                'effective_from' => now()
                    ->subMonths(6)
                    ->startOfMonth()
                    ->format('Y-m-d'),
            ]);
    /*
             * 40% chance to create another salary structure.
             *
             * This simulates salary history.
             */
            if (fake()->boolean(40)) {
                SalaryStructure::factory()->create([
                    'employee_id' => $employee->id,
                    'effective_from' => now()
                        ->subMonths(2)
                        ->startOfMonth()
                        ->format('Y-m-d'),
                ]);
            }
        }

        $runs = collect();

        /*
         * Create one Payroll Run for every month.
         */
        for ($month = 1; $month <= 12; $month++) {
            $runs->push(
                PayrollRun::create([
                    'year' => now()->year,
                    'month' => $month,
                        /*
                     * We mark seeded runs as finalized
                     * so we have ready-made payroll data for testing.
                     */
                    'status' => 'finalized',
                    'processed_at' => now(),
                    'finalized_at' => now(),
                ])
            );
        }
  /*         For  every pay roll
             * ...create a Payslip for every employee.
             */
        foreach ($runs as $run) {
            foreach ($employees as $employee) {
                          /*
                 * Find the most recent Salary Structure.
                 *
                 * This is important because an employee may have
                 * more than one salary structure.
                 *
                 * Example:
                 *
                 * March salary
                 * July salary
                 *
                 * orderByDesc() puts July first.
                 */
                $salary = $employee
                    ->salaryStructures()
                    ->orderByDesc('effective_from')
                    ->first();

                $basic = (float) $salary->basic_salary;

                $allowances =
                    (float) $salary->housing_allowance
                    + (float) $salary->transport_allowance
                    + (float) $salary->other_allowances;

                $deductions = fake()->randomFloat(
                    2,
                    0,
                    min(500, $basic * 0.2)
                );

                $unpaidLeave = fake()->randomFloat(
                    2,
                    0,
                    min(300, $basic * 0.1)
                );

                $net = max(
                    0,
                    $basic
                    + $allowances
                    - $deductions
                    - $unpaidLeave
                );
                /* Create the Payslip.
                 *
                 * PayslipFactory has:
                 *
                 * payroll_run_id = null
                 * employee_id = null
                 *
                 * But we are NOT using PayslipFactory here.
                 *
                 * We create it manually and provide both FKs.
                 */
                $payslip = Payslip::create([
                    'payroll_run_id' => $run->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $basic,
                    'total_allowances' => $allowances,
                    'total_deductions' => $deductions,
                    'unpaid_leave_deduction' => $unpaidLeave,
                    'net_salary' => $net,
                ]);

                if ($deductions > 0) {
                    PayslipDeduction::create([
                        'payslip_id' => $payslip->id,
                        'name' => 'Other Deduction',
                        'reason' => 'Seeded payroll deduction',
                        'amount' => $deductions,
                    ]);
                }
            }
        }
    }
}
