<?php

namespace Modules\Performance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Employees\Models\Employee;
use Modules\Performance\Models\PerformanceReview;
use Modules\Performance\Transformers\PerformanceReviewResource;

class GetEmployeePerformanceHistoryController extends Controller
{
    use AuthorizesRequests;
    public function __invoke($id)
    {
        $employee = Employee::findOrFail($id);

        try {

            $this->authorize('viewPerformanceHistory', [PerformanceReview::class, $employee]);
        }
         catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }
        $reviews = PerformanceReview::with(['performanceCycle', 'manager'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->get();

        return response()->json([
            'data' => PerformanceReviewResource::collection($reviews),
        ]);
    }
}
