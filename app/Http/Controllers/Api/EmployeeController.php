<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Employee\EmployeeHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeeCreateRequest;
use App\Http\Requests\Employee\EmployeeUpdateRequest;
use App\Http\Resources\Employee\EmployeeCollection;
use App\Http\Resources\Employee\EmployeeResource;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    private $employee;

    public function __construct()
    {
        $this->employee = new EmployeeHelper();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'search' => $request->search ?? '',
            'department_id' => $request->department_id ?? '',
            'position_id' => $request->position_id ?? '',
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $employees = $this->employee->getAll($filter);

        return response()->success(new EmployeeCollection($employees));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeCreateRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            // Data User
            'name',
            'email',
            'password',
            'phone_number',
            'photo',
            'role_id',
            // Data Employee
            'birth_place',
            'birth_date',
            'address',
            'department_id',
            'position_id',
            'hire_date',
            'salary',
            // Data tambahan untuk posisi jika diperlukan
            'position_start_date',
            'position_notes',
        ]);

        try {
            $employee = $this->employee->store($payload);

            return response()->success(new EmployeeResource($employee), 'Employee berhasil ditambahkan');
        } catch (\Exception $e) {
            return response()->failed(['Gagal menambahkan employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $employee = $this->employee->getById($id);

            if (!$employee) {
                return response()->failed(['Data employee tidak ditemukan'], 404);
            }

            return response()->success(new EmployeeResource($employee));
        } catch (\Exception $e) {
            return response()->failed(['Gagal menampilkan employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeUpdateRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            // Data User
            'name',
            'email',
            'password',
            'phone_number',
            'photo',
            'role_id',

            // Data Employee
            'birth_place',
            'birth_date',
            'address',
            'department_id',
            'position_id',
            'hire_date',
            'salary',

            // Data tambahan untuk posisi jika diperlukan
            'position_start_date',
            'position_notes',
            'approved_by'
        ]);

        try {
            $employee = $this->employee->update($id, $payload);

            return response()->success(
                new EmployeeResource($employee),
                'Data employee berhasil diperbarui'
            );
        } catch (\Exception $e) {
            return response()->failed([
                'Gagal memperbarui data employee: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $employee = $this->employee->delete($id);

            if (!$employee) {
                return response()->failed(['Data employee tidak ditemukan']);
            }

            return response()->success(null, 'Employee berhasil dihapus');
        } catch (\Exception $e) {
            return response()->failed(['Gagal menghapus employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $employee = $this->employee->restore($id);

            if (!$employee) {
                return response()->failed(['Data employee tidak ditemukan']);
            }

            return response()->success(null, 'Employee berhasil dipulihkan');
        } catch (\Exception $e) {
            return response()->failed(['Gagal memulihkan employee: ' . $e->getMessage()]);
        }
    }
}
