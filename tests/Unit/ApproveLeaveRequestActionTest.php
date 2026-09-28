<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Models\User;
use Modules\Leave\Actions\ApproveLeaveRequestAction;
use Modules\Leave\Events\LeaveRequestApproved;
use Modules\Leave\Models\LeaveRequest;
use Modules\Leave\Services\V1\LeaveBalanceService;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

describe('ApproveLeaveRequestAction Unit Tests', function () {

    afterEach(function () {
        Mockery::close();
    });

    test('successfully approves pending leave request and updates balance without DB hit', function () {
        Event::fake([LeaveRequestApproved::class]);

        /** @var User|\Mockery\MockInterface */
        $approver = Mockery::mock(User::class);
        $approver->shouldReceive('getAttribute')->with('id')->andReturn(10);
        $approver->shouldReceive('hasRole')->with('hr-admin')->andReturn(true);

        /** @var LeaveRequest|\Mockery\MockInterface */
        $leaveRequest = Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->exists = true;

        $leaveRequest->forceFill([
            'status' => 'pending',
            'employee_id' => 1,
            'leave_type_id' => 2,
            'days_count' => 5,
            'start_date' => '2026-05-01',
        ]);

        $leaveRequest->shouldReceive('save')->andReturn(true);
        $leaveRequest->shouldReceive('update')->andReturn(true);

        /** @var LeaveBalanceService|\Mockery\MockInterface */
        $balanceService = Mockery::mock(LeaveBalanceService::class);
        $balanceService->shouldReceive('getBalance')
            ->once()
            ->with(1, 2, 2026)
            ->andReturn([
                'allocated_days' => 20,
                'used_days' => 2,
                'remaining_days' => 18,
            ]);

        $balanceService->shouldReceive('deductBalance')
            ->zeroOrMoreTimes()
            ->with(1, 2, 5)
            ->andReturn(true);

        // Expectation for invalidateCache call
        $balanceService->shouldReceive('invalidateCache')
            ->once()
            ->with(1, 2026)
            ->andReturn(true);

        $action = new ApproveLeaveRequestAction($balanceService);
        $result = $action->execute($leaveRequest, $approver);

        expect($result)->toBe($leaveRequest);

        Event::assertDispatched(LeaveRequestApproved::class, function ($event) use ($leaveRequest) {
            return $event->leaveRequest === $leaveRequest;
        });
    });

    test('throws exception when request status is already approved', function () {
        Event::fake();

        /** @var User|\Mockery\MockInterface */
        $approver = Mockery::mock(User::class);

        /** @var LeaveRequest|\Mockery\MockInterface */
        $leaveRequest = Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->forceFill(['status' => 'approved']);

        /** @var LeaveBalanceService|\Mockery\MockInterface */
        $balanceService = Mockery::mock(LeaveBalanceService::class);

        $action = new ApproveLeaveRequestAction($balanceService);

        expect(fn () => $action->execute($leaveRequest, $approver))
            ->toThrow(\RuntimeException::class, 'This request is already approved.');

        Event::assertNotDispatched(LeaveRequestApproved::class);
    });

    test('throws exception when remaining balance is insufficient', function () {
        Event::fake();

        /** @var User|\Mockery\MockInterface */
        $approver = Mockery::mock(User::class);

        /** @var LeaveRequest|\Mockery\MockInterface */
        $leaveRequest = Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->forceFill([
            'status' => 'pending',
            'employee_id' => 1,
            'leave_type_id' => 2,
            'days_count' => 15,
            'start_date' => '2026-05-01',
        ]);

        /** @var LeaveBalanceService|\Mockery\MockInterface */
        $balanceService = Mockery::mock(LeaveBalanceService::class);
        $balanceService->shouldReceive('getBalance')
            ->once()
            ->with(1, 2, 2026)
            ->andReturn([
                'remaining_days' => 2,
            ]);

        $action = new ApproveLeaveRequestAction($balanceService);

        expect(fn () => $action->execute($leaveRequest, $approver))
            ->toThrow(\RuntimeException::class, 'Remaining leave balance is insufficient for this request.');

        Event::assertNotDispatched(LeaveRequestApproved::class);
    });

    test('throws exception when leave balance record is null', function () {
        Event::fake();

        /** @var User|\Mockery\MockInterface */
        $approver = Mockery::mock(User::class);

        /** @var LeaveRequest|\Mockery\MockInterface */
        $leaveRequest = Mockery::mock(LeaveRequest::class)->makePartial();
        $leaveRequest->forceFill([
            'status' => 'pending',
            'employee_id' => 1,
            'leave_type_id' => 2,
            'days_count' => 2,
            'start_date' => '2026-05-01',
        ]);

        /** @var LeaveBalanceService|\Mockery\MockInterface */
        $balanceService = Mockery::mock(LeaveBalanceService::class);
        $balanceService->shouldReceive('getBalance')
            ->once()
            ->with(1, 2, 2026)
            ->andReturn(null);

        $action = new ApproveLeaveRequestAction($balanceService);

        expect(fn () => $action->execute($leaveRequest, $approver))
            ->toThrow(\RuntimeException::class);

        Event::assertNotDispatched(LeaveRequestApproved::class);
    });
});
