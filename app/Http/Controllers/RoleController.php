<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\ApiResponse;
use App\Services\RoleService;

class RoleController extends Controller
{
    use ApiResponse;
    protected RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            'roles' => $this->service->getAll()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate(['role' => 'required|string|unique:roles,name']);
        return response()->json([
            'success'   => true,
            'role'      => $this->service->create($validated['role']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $role)
    {
        return response()->json([
            'users' => $this->service->find($role)
        ]);
    }

    // BUG: Be careful, updating main roles, it will break the system
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'role' => 'required|string|unique:roles,name',
        ]);
        $role = $this->service->update($id, $validated['role']);
        return response()->json([
            'success'   => true,
            'role'      => $role,
        ]);
    }

    // BUG: Be careful, deleting main roles, it will break the system
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted role",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
