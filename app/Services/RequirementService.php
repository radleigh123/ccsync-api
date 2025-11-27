<?php

namespace App\Services;

use App\Models\Requirement;

class RequirementService
{
    public function getAll()
    {
        return Requirement::with(['semester', 'offerings'])->get();
    }

    public function create(array $data)
    {
        return Requirement::create($data)->load('semester');
    }

    public function find(string $id)
    {
        return Requirement::with(['semester'])->findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $req = Requirement::findOrFail($id);
        $req->update($data);

        return $req->load(['semester']);
    }

    public function delete(string $id)
    {
        return Requirement::findOrFail($id)->delete();
    }

    public function paginate($page = null, $perPage = null)
    {
        return Requirement::paginate(perPage: $perPage, page: $page)
            ->toResourceCollection();
    }
}
