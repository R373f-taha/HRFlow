<?php

namespace Modules\Employees\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Modules\Employees\Actions\UploadEmployeeDocumentAction;
use Modules\Employees\Http\Requests\UploadEmployeeDocumentRequest;
use Modules\Employees\Models\Employee;
use Modules\Employees\Resources\EmployeeDocumentResource;
use Modules\Employees\Services\V1\GetEmployeeDocumentsService;

class EmployeeDocumentController extends Controller
{
    use AuthorizesRequests;

    /**
     * List all documents for an employee.
     */



public function index(
    Employee $employee,
    GetEmployeeDocumentsService $service
) {
    $this->authorize('viewDocuments', $employee);

    $documents = $service->execute($employee);

    return response()->json([
        'data' => $documents,
    ]);
}
    /**
     * Upload a new document for an employee.
     */
    public function store(
        UploadEmployeeDocumentRequest $request,
        Employee $employee,
        UploadEmployeeDocumentAction $action
    ): JsonResponse {
        $this->authorize('uploadDocument', $employee);

        $document = $action->execute(
            $employee,
            $request->validated(),
            $request->file('document')
        );

        return (new EmployeeDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }
}
