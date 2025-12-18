<?php

namespace App\Http\Controllers\Requirement;

use App\Helper\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Requirement\StoreComplianceRequest;
use App\Http\Requests\Requirement\UpdateComplianceRequest;
use App\Http\Resources\Requirement\ComplianceResource;
use App\Services\ComplianceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    use ApiResponse;

    protected ComplianceService $service;

    public function __construct(ComplianceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->service->getAll();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreComplianceRequest $request)
    {
        try {
            $comp = $this->service->create($request->validated());
            return $this->success(
                $comp->toResource(),
                201,
                'Successfully created compliance.'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            return $this->success(
                new ComplianceResource($this->service->find($id)),
                200,
                "Successfully retrieved compliance"
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Compliance does not exist");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    // NOTE: Only OFFICER/ADMIN can update
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateComplianceRequest $request, string $id)
    {
        $validated = $request->validated();
        try {
            $comp = $this->service->update($id, $validated);
            return $this->success(
                new ComplianceResource($comp),
                200,
                "Successfully updated compliance."
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(message: $e->getMessage());
        } catch (\Exception $e) {
            return $this->error(message: $e->getMessage(), code: 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return $this->success(
                message: 'Compliance removed successfully.',
                code: 204,
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }

    public function getPagination(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('per_page', 20);
        try {
            return $this->service->paginate($page, $perPage);
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500,
            );
        }
    }
}
