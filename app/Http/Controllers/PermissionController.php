<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use ApiResponse;
    protected PermissionService $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json([
            'permissions' => $this->service->getAll()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate(['permission' => 'required|string|unique:permissions,name']);
        return response()->json([
            'success'   => true,
            'permission'      => $this->service->create($validated['permission']),
        ]);
    }

    public function show(string $permission)
    {
        return response()->json([
            'users' => $this->service->find($permission)
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate(['permission' => 'required|string|unique:permissions,name']);
        $permission = $this->service->update($id, $validated['permission']);
        return response()->json([
            'success'   => true,
            'role'      => $permission,
        ]);
    }

    public function destroy(string $id)
    {
        try {
            $this->service->delete($id);
            return response()->json([
                'success' => true,
                'message' => "Successfully deleted permission",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function assignPermissionToRole(Request $request, string $role)
    {
        $validated = $request->validate(['permission' => 'required|string|exists:permissions,name']);
        try {
            $permission = $this->service->givePermissionToRole($role, $validated['permission']);
            return response()->json([
                'success' => true,
                'message' => "Successfully assigned permission to $permission->name",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function revokePermissionToRole(Request $request, string $role)
    {
        $validated = $request->validate(['permission' => 'required|string|exists:permissions,name']);
        $permission = $this->service->deletePermissionToRole($role, $validated['permission']);
        return response()->json([
            'success' => true,
            'message' => "Successfully revoked permission to $permission->name",
        ]);
    }
}
