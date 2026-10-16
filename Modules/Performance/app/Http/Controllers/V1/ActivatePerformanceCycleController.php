<?php


namespace Modules\Performance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Performance\Models\PerformanceCycle;
use Modules\Performance\Transformers\PerformanceCycleResource;

class ActivatePerformanceCycleController extends Controller
{
   use AuthorizesRequests;
    public function __invoke($id)
    {
        $cycle = PerformanceCycle::findOrFail($id);

        if ($cycle->status === 'active') {
            return response()->json([
                'message' => 'Cycle is already active.',
            ], 422);
        }

        $this->authorize('activate', $cycle);

        $cycle->update(['status' => 'active']);

        return response()->json([
            'message' => 'Performance cycle activated successfully.',
            'data' => new PerformanceCycleResource($cycle),
        ]);
    }
}
