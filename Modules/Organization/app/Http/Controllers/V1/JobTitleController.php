<?php

namespace Modules\Organization\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Organization\Actions\CreateJobTitleAction;
use Modules\Organization\Actions\DeleteJobTitleAction;
use Modules\Organization\Actions\UpdateJobTitleAction;
use Modules\Organization\Http\Requests\StoreJobTitleRequest;
use Modules\Organization\Http\Requests\UpdateJobTitleRequest;
use Modules\Organization\Models\JobTitle;
use Modules\Organization\Services\V1\JobTitleService;
use Modules\Organization\Transformers\JobTitleResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class JobTitleController extends Controller
{
    use AuthorizesRequests;

   public $service;

    public function __construct(JobTitleService $service)
    {
        $this->service = $service;
    }
    public function index(JobTitleService $service): AnonymousResourceCollection
    {
        $this->authorize('viewAny', JobTitle::class);

        return JobTitleResource::collection($service->getAllCached());
    }

    /**
     * Retrieve job titles for a specific department.
     */
    public function show(int $departmentId): AnonymousResourceCollection
    {
        $this->authorize('viewAny', JobTitle::class);

        return JobTitleResource::collection($this->service->getByDepartmentCached($departmentId));
    }

    public function store(
        StoreJobTitleRequest $request,
        CreateJobTitleAction $action
    ): JsonResponse {
        $this->authorize('create', JobTitle::class);

        $jobTitle = $action->execute($request->validated());

        return (new JobTitleResource($jobTitle))
            ->response()
            ->setStatusCode(201);
    }

    public function update(
        UpdateJobTitleRequest $request,
        JobTitle $jobTitle,
        UpdateJobTitleAction $action
    ): JobTitleResource {
        $this->authorize('update', $jobTitle);

        $updatedJobTitle = $action->execute($jobTitle, $request->validated());

        return new JobTitleResource($updatedJobTitle);
    }

    public function destroy(
        JobTitle $jobTitle,
        DeleteJobTitleAction $action
    ): JsonResponse {
        $this->authorize('delete', $jobTitle);

        $action->execute($jobTitle);

        return response()->json(null, 204);
    }
}
