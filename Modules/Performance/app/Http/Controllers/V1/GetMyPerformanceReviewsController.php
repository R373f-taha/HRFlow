<?php

namespace Modules\Performance\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Performance\Services\V1\GetMyPerformanceReviewsService;

class GetMyPerformanceReviewsController extends Controller
{
    public function __invoke(Request $request, GetMyPerformanceReviewsService $service)
    {
        $data = $service->execute($request->user());

        if ($data === null) {
            return response()->json([
                'message' => 'No employee profile associated with this account.',
            ], 404);
        }

         if (empty($data)) {
            return response()->json([
                'message' => 'No performance reviews found for this employee.',
            ], 404);
        }


        return response()->json(['data' => $data]);
    }
}
