<?php


namespace Modules\Performance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Performance\Http\Requests\StorePerformanceCycleRequest;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Services\V1\PerformanceCycleService;
use Modules\Performance\Transformers\PerformanceCycleResource;

class PerformanceCycleController extends Controller
{

use AuthorizesRequests;
  public function __construct(
        protected PerformanceCycleService $performanceCycleService
    ) {}

    /**
     * GET /v1/performance-cycles
     */
    public function index(Request $request)
    {
        $cycles = $this->performanceCycleService->getPaginatedCycles($request->all());

        return PerformanceCycleResource::collection($cycles);
    }
    /**
     * POST /v1/performance-cycles
     */
    public function store(StorePerformanceCycleRequest $request)
    {
      $this->authorize('create', PerformanceCycle::class);
        $cycle = PerformanceCycle::create($request->validated());

        return response()->json([
            'message' => 'Performance cycle created successfully.',
            'data' => new PerformanceCycleResource($cycle),
        ], 201);
    }


}
