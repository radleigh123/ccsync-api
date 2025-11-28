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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
