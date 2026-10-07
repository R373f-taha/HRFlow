<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Modules\Employees\Models\Employee;
use Modules\Payroll\Actions\AddPayslipDeductionAction;
use Modules\Payroll\Actions\FinalizePayrollRunAction;
use Modules\Payroll\Actions\ProcessPayrollRunAction;
use Modules\Payroll\Events\PayrollFinalizedEvent;
use Modules\Payroll\Models\PayrollRun;
use Modules\Payroll\Models\Payslip;
use Modules\Payroll\Models\PayslipDeduction;
use Modules\Payroll\Models\SalaryStructure;
use Modules\Payroll\Services\V1\PayrollService;
use Tests\TestCase;

describe("Payroll / actions unit tests (No DB)", function () {
    uses(TestCase::class);

    beforeEach(function () {
        // تمرير الـ Transactions بدون التعامل مع قاعدة البيانات
        DB::shouldReceive('transaction')
            ->andReturnUsing(fn (callable $callback) => $callback());
    });

    afterEach(function () {
        Mockery::close();
    });


    test('process payroll run action throws domain exception if run is already finalized', function () {
        $payrollRun = new PayrollRun();
        $payrollRun->status = 'finalized';

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);
        $mockPayrollService->shouldReceive('flushCache')->never();

        $action = new ProcessPayrollRunAction($mockPayrollService);

        expect(fn () => $action($payrollRun))
            ->toThrow(DomainException::class, 'Cannot process a finalized payroll run.');
    });

    // --- FINALIZE PAYROLL RUN ACTION TESTS ---

    test('finalize action transitions run to finalized state and dispatches event', function () {
        Event::fake();

        /** @var PayrollRun|\Mockery\MockInterface $payrollRun */
        $payrollRun = Mockery::mock(PayrollRun::class)->makePartial();
        $payrollRun->id = 10;
        $payrollRun->status = 'processed';

        /** @var HasMany|\Mockery\MockInterface $mockHasMany */
        $mockHasMany = Mockery::mock(HasMany::class);
        /** @var Builder|\Mockery\MockInterface $mockBuilder */
        $mockBuilder = Mockery::mock(Builder::class);

        $payrollRun->shouldReceive('payslips')->andReturn($mockHasMany);

        $mockHasMany->shouldReceive('count')
            ->once()
            ->andReturn(5);

        $mockHasMany->shouldReceive('where')
            ->with('net_salary', '<', 0)
            ->once()
            ->andReturn($mockBuilder);

        $mockBuilder->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        $payrollRun->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($data) use ($payrollRun) {
                if ($data['status'] === 'finalized' && isset($data['finalized_at'])) {
                    $payrollRun->status = 'finalized';
                    return true;
                }
                return false;
            }))
            ->andReturnTrue();

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);
        $mockPayrollService->shouldReceive('flushCache')->once();

        $action = new FinalizePayrollRunAction($mockPayrollService);

        /** @var PayrollRun $payrollRun */
        $finalizedRun = $action($payrollRun);

        expect($finalizedRun->status)->toBe('finalized');
        Event::assertDispatched(PayrollFinalizedEvent::class);
    });

    test('finalize action throws domain exception when payroll run is already finalized', function () {
        $payrollRun = new PayrollRun();
        $payrollRun->status = 'finalized';

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);

        $action = new FinalizePayrollRunAction($mockPayrollService);

        expect(fn () => $action($payrollRun))
            ->toThrow(DomainException::class, 'Payroll run is already finalized.');
    });

    test('finalize action throws domain exception when payroll run status is draft', function () {
        $payrollRun = new PayrollRun();
        $payrollRun->status = 'draft';

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);

        $action = new FinalizePayrollRunAction($mockPayrollService);

        expect(fn () => $action($payrollRun))
            ->toThrow(DomainException::class, 'Payroll run must be processed before it can be finalized.');
    });

    test('finalize action throws domain exception when payroll run has no payslips', function () {
        /** @var PayrollRun|\Mockery\MockInterface $payrollRun */
        $payrollRun = Mockery::mock(PayrollRun::class)->makePartial();
        $payrollRun->status = 'processed';

        /** @var HasMany|\Mockery\MockInterface $mockHasMany */
        $mockHasMany = Mockery::mock(HasMany::class);
        $payrollRun->shouldReceive('payslips')->andReturn($mockHasMany);

        $mockHasMany->shouldReceive('count')->once()->andReturn(0);

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);

        $action = new FinalizePayrollRunAction($mockPayrollService);

        expect(fn () => $action($payrollRun))
            ->toThrow(DomainException::class, 'Cannot finalize a payroll run with no generated payslips.');
    });

    test('finalize action throws domain exception when payslip has negative net salary', function () {
        /** @var PayrollRun|\Mockery\MockInterface $payrollRun */
        $payrollRun = Mockery::mock(PayrollRun::class)->makePartial();
        $payrollRun->status = 'processed';

        /** @var HasMany|\Mockery\MockInterface $mockHasMany */
        $mockHasMany = Mockery::mock(HasMany::class);
        /** @var Builder|\Mockery\MockInterface $mockBuilder */
        $mockBuilder = Mockery::mock(Builder::class);

        $payrollRun->shouldReceive('payslips')->andReturn($mockHasMany);

        $mockHasMany->shouldReceive('count')->once()->andReturn(5);
        $mockHasMany->shouldReceive('where')->with('net_salary', '<', 0)->once()->andReturn($mockBuilder);
        $mockBuilder->shouldReceive('exists')->once()->andReturn(true);

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);

        $action = new FinalizePayrollRunAction($mockPayrollService);

        expect(fn () => $action($payrollRun))
            ->toThrow(DomainException::class, 'Cannot finalize payroll run: One or more payslips have a negative net salary.');
    });

    // --- ADD PAYSLIP DEDUCTION ACTION TESTS ---

    test('add deduction action creates deduction and recalculates net salary on mocked payslip', function () {
        /** @var PayrollRun $payrollRun */
        $payrollRun = new PayrollRun();
        $payrollRun->status = 'processed';

        /** @var Payslip|\Mockery\MockInterface $payslip */
        $payslip = Mockery::mock(Payslip::class)->makePartial();
        $payslip->id = 1;
        $payslip->basic_salary = 3000.00;
        $payslip->total_allowances = 500.00;
        $payslip->unpaid_leave_deduction = 0.00;
        $payslip->setRelation('payrollRun', $payrollRun);

        /** @var HasMany|\Mockery\MockInterface $mockDeductionsRelation */
        $mockDeductionsRelation = Mockery::mock(HasMany::class);
        $payslip->shouldReceive('deductions')->andReturn($mockDeductionsRelation);

        $mockDeduction = new PayslipDeduction(['name' => 'Late Penalty', 'amount' => 150.00]);
        $mockDeduction->amount = 150.00;

        $mockDeductionsRelation->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Late Penalty',
                'reason' => null,
                'amount' => "150.00",
            ])
            ->andReturn($mockDeduction);

        $mockDeductionsRelation->shouldReceive('sum')
            ->once()
            ->with('amount')
            ->andReturn(250.00);

        $payslip->shouldReceive('update')
            ->once()
            ->with([
                'total_deductions' => 250.00,
                'net_salary' => 3250.00,
            ])
            ->andReturnTrue();

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);
        $mockPayrollService->shouldReceive('flushCache')->once();

        $action = new AddPayslipDeductionAction($mockPayrollService);

        /** @var Payslip $payslip */
        $result = $action($payslip, [
            'name' => 'Late Penalty',
            'amount' => "150.00",
        ]);

        expect($result)->toBeInstanceOf(PayslipDeduction::class)
            ->and($result->amount)->toBe("150.00");
    });

    test('add deduction action throws domain exception on finalized payroll run without updating model', function () {
        /** @var PayrollRun $payrollRun */
        $payrollRun = new PayrollRun();
        $payrollRun->status = 'finalized';

        /** @var Payslip $payslip */
        $payslip = new Payslip();
        $payslip->setRelation('payrollRun', $payrollRun);

        /** @var PayrollService|\Mockery\MockInterface $mockPayrollService */
        $mockPayrollService = Mockery::mock(PayrollService::class);

        $action = new AddPayslipDeductionAction($mockPayrollService);

        expect(fn () => $action($payslip, [
            'name' => 'Late Penalty',
            'amount' => 150.00,
        ]))->toThrow(DomainException::class, 'Cannot add deductions to a finalized payslip.');
    });
});
