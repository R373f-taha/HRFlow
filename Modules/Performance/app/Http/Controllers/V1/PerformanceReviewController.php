<?php

namespace Modules\Performance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Support\Cache\CacheSupport;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Modules\Employees\Models\Employee;
use Modules\Performance\Actions\CreatePerformanceReviewAction;
use Modules\Performance\Http\Requests\StorePerformanceReviewRequest;
use Modules\Performance\Http\Requests\UpdatePerformanceReviewRequest;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Models\PerformanceReview;
use Modules\Performance\Services\GetMyPerformanceReviewsService;
use Modules\Performance\Services\V1\PerformanceReviewService;
use Modules\Performance\Transformers\PerformanceReviewResource;

class PerformanceReviewController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected PerformanceReviewService $performanceReviewService
    ) {}
    /**
     * GET /v1/performance-reviews
     * (HR Admin & Managers viewing team reviews)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceReview::class);

        $reviews = $this->performanceReviewService->getPaginatedReviews(
            $request->user(),
            $request->all()
        );

        return PerformanceReviewResource::collection($reviews);
    }
/**
     * POST /v1/performance-reviews
     */
    public function store(StorePerformanceReviewRequest $request, CreatePerformanceReviewAction $action)
    {
        $targetEmployee = Employee::findOrFail($request->validated('employee_id'));
        $cycle = PerformanceCycle::findOrFail($request->validated('performance_cycle_id'));

        try{
        $this->authorize('create', [PerformanceReview::class, $targetEmployee, $cycle]);
        }

        catch (AuthorizationException $e) {
        return response()->json([
            'message' => $e->getMessage(),
        ], 403);
    }

        $review = $action->execute($request->user()->employee, $request->validated());

        return response()->json([
            'message' => 'Performance review submitted successfully.',
            'data' => new PerformanceReviewResource($review->load(['performanceCycle', 'manager', 'employee'])),
        ], 201);
    }


    /**
     * PUT /v1/performance-reviews/{id}
     */
    public function update(UpdatePerformanceReviewRequest $request, $id)
    {
        $review = PerformanceReview::findOrFail($id);

    try{

      $this->authorize('update', $review);
    }
    catch(AuthorizationException $e){

    return response()->json(['message'=>$e->getMessage(),
    403]);
    }

        $review->update($request->validated());

        CacheSupport::forget("employees.performance.user_{$review->employee_id}");

        return response()->json([
            'message' => 'Performance review updated successfully.',
            'data' => new PerformanceReviewResource($review->load(['performanceCycle', 'manager'])),
        ]);
    }
}


