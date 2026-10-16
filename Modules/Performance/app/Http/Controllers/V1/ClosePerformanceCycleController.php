<?php


namespace Modules\Performance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Transformers\PerformanceCycleResource;

class ClosePerformanceCycleController extends Controller
{
    use AuthorizesRequests;
    public function __invoke($id)
    {
        $cycle = PerformanceCycle::findOrFail($id);

        if ($cycle->status === 'closed') {
            return response()->json([
                'message' => 'Cycle is already closed.',
            ], 422);
        }

        $this->authorize('close', $cycle);

        $cycle->update(['status' => 'closed']);

        return response()->json([
            'message' => 'Performance cycle closed successfully.',
            'data' => new PerformanceCycleResource($cycle),
        ]);
    }
}
