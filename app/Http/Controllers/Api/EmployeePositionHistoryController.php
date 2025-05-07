<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Employee\EmployeeHelper;
use App\Helpers\Employee\EmployeePositionHistoryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\EmployeePositionHistoryRequest;
use App\Http\Resources\Employee\EmployeePositionHistoryCollection;
use App\Http\Resources\Employee\EmployeePositionHistoryResource;
use App\Http\Resources\Employee\EmployeeResource;
use Illuminate\Http\Request;

class EmployeePositionHistoryController extends Controller
{
    private $positionHistory;
    private $employeeHelper;

    public function __construct()
    {
        $this->positionHistory = new EmployeePositionHistoryHelper();
        $this->employeeHelper = new EmployeeHelper();
    }

    /**
     * Display a listing of all position histories with filtering
     */
    public function index(Request $request)
    {
        try {
            $filter = [
                'employee_name' => $request->employee_name,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'is_current' => $request->is_current,
                'start_date_from' => $request->start_date_from,
                'start_date_to' => $request->start_date_to,
                'per_page' => $request->per_page ?? 10,
                'page' => $request->page ?? 1,
                'sort' => $request->sort ?? 'start_date,desc'
            ];

            $history = $this->positionHistory->getAll($filter);

            return response()->success(new EmployeePositionHistoryCollection($history));
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil riwayat posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Display a listing of position history for an employee.
     */
    public function employeeHistory(Request $request, string $employeeId)
    {
        try {
            $filter = [
                'is_current' => $request->is_current,
                'start_date_from' => $request->start_date_from,
                'start_date_to' => $request->start_date_to,
                'department_id' => $request->department_id,
                'position_id' => $request->position_id,
                'per_page' => $request->per_page ?? 10,
                'page' => $request->page ?? 1,
                'sort' => $request->sort ?? 'start_date,desc'
            ];

            $history = $this->positionHistory->getByEmployeeId($employeeId, $filter);

            return response()->success(new EmployeePositionHistoryCollection($history));
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil riwayat posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Store a newly created position history.
     */
    public function store(EmployeePositionHistoryRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        try {
            $payload = $request->validated();

            // Set created_by to authenticated user if not provided
            if (!isset($payload['created_by']) && auth()->check()) {
                $payload['created_by'] = auth()->id();
            }

            $history = $this->positionHistory->store($payload);

            return response()->success(new EmployeePositionHistoryResource($history), 'Riwayat posisi berhasil ditambahkan');
        } catch (\Exception $e) {
            return response()->failed(['Gagal menambahkan riwayat posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified position history.
     */
    public function show(string $id)
    {
        try {
            $history = $this->positionHistory->getById($id);

            return response()->success(new EmployeePositionHistoryResource($history));
        } catch (\Exception $e) {
            return response()->failed(['Gagal menampilkan riwayat posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified position history.
     */
    public function update(EmployeePositionHistoryRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        try {
            $payload = $request->validated();
            $history = $this->positionHistory->update($id, $payload);

            return response()->success(new EmployeePositionHistoryResource($history), 'Riwayat posisi berhasil diperbarui');
        } catch (\Exception $e) {
            return response()->failed(['Gagal memperbarui riwayat posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified position history.
     */
    public function destroy(string $id)
    {
        try {
            $history = $this->positionHistory->getById($id);

            // Prevent deleting current position
            if ($history->is_current) {
                return response()->failed(['Tidak dapat menghapus posisi yang masih aktif. Harap atur posisi baru terlebih dahulu.']);
            }

            $result = $this->positionHistory->delete($id);

            if ($result) {
                return response()->success(null, 'Riwayat posisi berhasil dihapus');
            } else {
                return response()->failed(['Riwayat posisi tidak ditemukan']);
            }
        } catch (\Exception $e) {
            return response()->failed(['Gagal menghapus riwayat posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a new position for an employee (mutasi/promosi)
     */
    public function assignPosition(EmployeePositionHistoryRequest $request, string $employeeId)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        try {
            $payload = $request->validated();

            // Gunakan mutate dari EmployeeHelper
            $result = $this->employeeHelper->mutate($employeeId, $payload);

            if (!$result) {
                return response()->failed(['Karyawan tidak ditemukan']);
            }

            // Ambil data employee lengkap
            $employee = $this->employeeHelper->getById($employeeId);

            return response()->success(new EmployeeResource($employee), 'Posisi karyawan berhasil diperbarui');
        } catch (\Exception $e) {
            return response()->failed(['Gagal memperbarui posisi karyawan: ' . $e->getMessage()]);
        }
    }

    /**
     * Get all position changes for an employee with detailed information
     */
    public function employeePositionChanges(string $employeeId)
    {
        try {
            $history = $this->positionHistory->getPositionDuration($employeeId);

            return response()->success($history);
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil riwayat perubahan posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Get statistics about department rotation
     */
    public function departmentStats(Request $request)
    {
        try {
            $stats = $this->positionHistory->getEmployeeCountByDepartment();

            return response()->success($stats);
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil statistik departemen: ' . $e->getMessage()]);
        }
    }

    /**
     * Get statistics about position rotation
     */
    public function positionStats(Request $request)
    {
        try {
            $stats = $this->positionHistory->getEmployeeCountByPosition();

            return response()->success($stats);
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil statistik posisi: ' . $e->getMessage()]);
        }
    }

    /**
     * Get organization structure
     */
    public function organizationStructure()
    {
        try {
            $structure = $this->positionHistory->getOrganizationStructure();

            return response()->success($structure);
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil struktur organisasi: ' . $e->getMessage()]);
        }
    }

    /**
     * Update salary for an employee without changing position
     */
    public function updateSalary(Request $request, string $employeeId)
    {
        // Validasi request
        $request->validate([
            'salary' => 'required|numeric|min:0|decimal:0,2',
            'start_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'approved_by' => 'nullable|uuid|exists:m_user,id',
        ]);

        try {
            $payload = [
                'start_date' => $request->start_date,
                'notes' => $request->notes ?? 'Perubahan gaji',
                'approved_by' => $request->approved_by,
            ];

            $result = $this->positionHistory->updateSalary($employeeId, $request->salary, $payload);

            if (!$result) {
                return response()->failed(['Karyawan tidak ditemukan atau tidak memiliki posisi aktif']);
            }

            // Ambil data employee lengkap
            $employee = $this->employeeHelper->getById($employeeId);

            return response()->success(new EmployeeResource($employee), 'Gaji karyawan berhasil diperbarui');
        } catch (\Exception $e) {
            return response()->failed(['Gagal memperbarui gaji karyawan: ' . $e->getMessage()]);
        }
    }

    /**
     * Get salary history for an employee
     */
    public function salaryHistory(string $employeeId)
    {
        try {
            $history = $this->positionHistory->getSalaryHistory($employeeId);

            return response()->success(new EmployeePositionHistoryCollection($history));
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil riwayat gaji: ' . $e->getMessage()]);
        }
    }

    /**
     * Get salary comparison by department
     */
    public function salaryComparisonByDepartment()
    {
        try {
            $comparison = $this->positionHistory->getSalaryComparisonByDepartment();

            return response()->success($comparison);
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil perbandingan gaji: ' . $e->getMessage()]);
        }
    }

    /**
     * Get salary comparison by position
     */
    public function salaryComparisonByPosition()
    {
        try {
            $comparison = $this->positionHistory->getSalaryComparisonByPosition();

            return response()->success($comparison);
        } catch (\Exception $e) {
            return response()->failed(['Gagal mengambil perbandingan gaji: ' . $e->getMessage()]);
        }
    }
}
