<?php

namespace Modules\Reports\Actions;

use App\Support\Cache\CacheSupport;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollRun;

class GetPayrollSummaryReportAction
{
    protected const CACHE_TTL = 3600;
    public function execute(?int $year = null, ?int $month = null): array
    {
         $cacheKey = "reports.payroll_summary.y{$year}_m{$month}";

        return CacheSupport::remember($cacheKey, self::CACHE_TTL, function () use ($year, $month) {
        $query = DB::table('payslips')
            ->join('payroll_runs', 'payslips.payroll_run_id', '=', 'payroll_runs.id')
            ->where('payroll_runs.status', 'finalized');

       if ($year) {
                $query->whereYear('payroll_runs.created_at', $year);
            }

            if ($month) {
                $query->whereMonth('payroll_runs.created_at', $month);
            }
        $summary = $query->select(
            DB::raw('SUM(payslips.basic_salary) as total_basic_salary'),
            DB::raw('SUM(payslips.total_allowances) as total_allowances'),
            DB::raw('SUM(payslips.total_deductions) as total_deductions'),
            DB::raw('SUM(payslips.unpaid_leave_deduction) as total_unpaid_leave_deduction'),
            DB::raw('SUM(payslips.net_salary) as total_net_salary'),
            DB::raw('COUNT(payslips.id) as total_payslips')
        )->first();

        return [
            'year' => $year,
            'month' => $month,
            'total_basic_salary' => (float) ($summary->total_basic_salary ?? 0),
            'total_allowances' => (float) ($summary->total_allowances ?? 0),
            'total_deductions' => (float) ($summary->total_deductions ?? 0),
            'total_unpaid_leave_deduction' => (float) ($summary->total_unpaid_leave_deduction ?? 0),
            'total_net_salary' => (float) ($summary->total_net_salary ?? 0),
            'processed_payslips_count' => (int) ($summary->total_payslips ?? 0),
        ];
    });}
}
