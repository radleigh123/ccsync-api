<?php

namespace App\Http\Controllers\Requirement;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Requirement\StoreRequirementRequest;
use App\Http\Requests\Requirement\UpdateRequirementRequest;
use App\Http\Resources\Requirement\RequirementResource;
use App\Services\RequirementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequirementController extends Controller
{
    use ApiResponse;

    protected RequirementService $service;

    public function __construct(RequirementService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->service->getAll()->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequirementRequest $request)
    {
        $req = $this->service->create($request->validated());
        return $this->success(
            $req->toResource(),
            201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            return $this->success(
                new RequirementResource($this->service->find($id)),
                200,
                "Successfully retrieved requirement"
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Requirement does not exist");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequirementRequest $request, string $id)
    {
        $validated = $request->validated();
        $req = $this->service->update($id, $validated);

        return $this->success(
            new RequirementResource($req),
            200,
            "Successfully updated requirement {$req->name}."
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return $this->success(
                message: 'Requirement removed successfully',
                code: 204,
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }
}
