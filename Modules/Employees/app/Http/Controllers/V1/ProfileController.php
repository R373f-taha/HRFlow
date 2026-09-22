<?php

namespace Modules\Employees\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Modules\Employees\Actions\UpdateEmployeeProfileAction;
use Modules\Employees\Http\Requests\UpdateProfileRequest;
use Modules\Employees\Resources\EmployeeResource;
use Modules\Employees\Services\V1\GetEmployeeProfileService;

class ProfileController extends Controller
{
    use AuthorizesRequests;

    /**
     * Retrieve the authenticated user's profile.
     */
public function show(Request $request, GetEmployeeProfileService $service)
    {
        $profileData = $service->execute($request->user());

        if (! $profileData) {
            return response()->json([
                'success' => false,
                'message' => 'Employee profile not found for this user.',
            ], 404);
        }

        return response()->json([
            'data' => $profileData,
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(
        UpdateProfileRequest $request,
        UpdateEmployeeProfileAction $action
    ): EmployeeResource {
        $employee = $action->execute($request->user(), $request->validated());

        return new EmployeeResource($employee);
    }
}
