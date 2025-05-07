<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Department\DepartmentHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentRequest;
use App\Http\Resources\Department\DepartmentCollection;
use App\Http\Resources\Department\DepartmentResource;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    private $department;

    public function __construct()
    {
        $this->department = new DepartmentHelper();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filter = [
            'search' => $request->search ?? '',
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $departments = $this->department->getAll($filter);

        return response()->success(new DepartmentCollection($departments));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only(['name', 'description']);

        if ($this->department->isNameExists($payload['name'])) {
            return response()->failed(['Nama department sudah digunakan']);
        }

        $department = $this->department->store($payload);

        if (!$department) {
            return response()->failed(['Gagal menambahkan department']);
        }

        return response()->success(new DepartmentResource($department), 'Department berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $department = $this->department->getById($id);

        if (!$department) {
            return response()->failed(['Data department tidak ditemukan'], 404);
        }

        return response()->success(new DepartmentResource($department));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only(['name', 'description']);

        $department = $this->department->update($id, $payload);

        if (!$department) {
            return response()->failed(['Gagal mengubah department']);
        }

        return response()->success(new DepartmentResource($department), 'Department berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $department = $this->department->delete($id);

        if (!$department) {
            return response()->failed(['Data department tidak ditemukan']);
        }

        return response()->success(null, 'Department berhasil dihapus');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $department = $this->department->restore($id);

        if (!$department) {
            return response()->failed(['Data department tidak ditemukan']);
        }

        return response()->success(null, 'Department berhasil dipulihkan');
    }
}
