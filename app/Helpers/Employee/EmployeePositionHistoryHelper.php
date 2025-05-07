<?php

namespace App\Helpers\Employee;

use App\Models\DepartmentModel;
use App\Models\EmployeeModel;
use App\Models\EmployeePositionHistoryModel;
use App\Models\PositionModel;
use Illuminate\Support\Facades\DB;

class EmployeePositionHistoryHelper
{
    protected $model;
    protected $employeeModel;
    protected $departmentModel;
    protected $positionModel;

    public function __construct()
    {
        $this->model = new EmployeePositionHistoryModel();
        $this->employeeModel = new EmployeeModel();
        $this->departmentModel = new DepartmentModel();
        $this->positionModel = new PositionModel();
    }

    /**
     * Get all position histories with optional filtering
     */
    public function getAll(array $filter = [])
    {
        $page = $filter['page'] ?? 1;
        $perPage = $filter['per_page'] ?? 10;
        $sort = $filter['sort'] ?? 'start_date,desc';

        $query = $this->model->with(['employee.user', 'department', 'position']);

        // Filter berdasarkan nama karyawan
        if (!empty($filter['employee_name'])) {
            $query->whereHas('employee.user', function ($q) use ($filter) {
                $q->where('name', 'LIKE', '%' . $filter['employee_name'] . '%');
            });
        }

        // Filter berdasarkan departemen
        if (!empty($filter['department_id'])) {
            $query->where('department_id', $filter['department_id']);
        }

        // Filter berdasarkan posisi
        if (!empty($filter['position_id'])) {
            $query->where('position_id', $filter['position_id']);
        }

        // Filter berdasarkan status current
        if (isset($filter['is_current'])) {
            $query->where('is_current', $filter['is_current']);
        }

        // Filter berdasarkan tanggal mulai
        if (!empty($filter['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filter['start_date_from']);
        }

        if (!empty($filter['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filter['start_date_to']);
        }

        // Pengurutan data
        $sortParts = explode(',', $sort);
        if (count($sortParts) === 2) {
            $query->orderBy($sortParts[0], $sortParts[1]);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get position histories for a specific employee
     */
    public function getByEmployeeId(string $employeeId, array $filter = [])
    {
        $page = $filter['page'] ?? 1;
        $perPage = $filter['per_page'] ?? 10;
        $sort = $filter['sort'] ?? 'start_date,desc';

        $query = $this->model->with(['department', 'position'])
            ->where('employee_id', $employeeId);

        // Filter berdasarkan departemen
        if (!empty($filter['department_id'])) {
            $query->where('department_id', $filter['department_id']);
        }

        // Filter berdasarkan posisi
        if (!empty($filter['position_id'])) {
            $query->where('position_id', $filter['position_id']);
        }

        // Filter berdasarkan status current
        if (isset($filter['is_current'])) {
            $query->where('is_current', $filter['is_current']);
        }

        // Filter berdasarkan tanggal mulai
        if (!empty($filter['start_date_from'])) {
            $query->whereDate('start_date', '>=', $filter['start_date_from']);
        }

        if (!empty($filter['start_date_to'])) {
            $query->whereDate('start_date', '<=', $filter['start_date_to']);
        }

        // Pengurutan data
        $sortParts = explode(',', $sort);
        if (count($sortParts) === 2) {
            $query->orderBy($sortParts[0], $sortParts[1]);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get a specific position history by ID
     */
    public function getById(string $id)
    {
        return $this->model->with(['employee.user', 'department', 'position'])
            ->findOrFail($id);
    }

    /**
     * Create a new position history record
     */
    public function store(array $payload)
    {
        DB::beginTransaction();
        try {
            // Jika ini akan menjadi posisi current, nonaktifkan posisi current lainnya
            if (isset($payload['is_current']) && $payload['is_current']) {
                $this->deactivateCurrentPositions($payload['employee_id']);
            }

            // Simpan riwayat posisi baru
            $positionHistory = $this->model->createNewPosition($payload);

            DB::commit();
            return $positionHistory;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing position history record
     */
    public function update(string $id, array $payload)
    {
        DB::beginTransaction();
        try {
            $positionHistory = $this->model->findOrFail($id);
            $isCurrentChanged = isset($payload['is_current']) &&
                                $payload['is_current'] != $positionHistory->is_current;

            // Jika posisi akan dijadikan current, nonaktifkan posisi current lainnya
            if ($isCurrentChanged && $payload['is_current']) {
                $this->deactivateCurrentPositions($positionHistory->employee_id);
            }

            // Update posisi
            $positionHistory->update($payload);

            // Jika tanggal akhir diisi, otomatis tandai bukan current
            if (isset($payload['end_date']) && $payload['end_date']) {
                $positionHistory->is_current = false;
                $positionHistory->save();
            }

            DB::commit();
            return $positionHistory->fresh(['employee.user', 'department', 'position']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete a position history record
     */
    public function delete(string $id)
    {
        $positionHistory = $this->model->findOrFail($id);

        // Jika ini posisi current, batalkan penghapusan
        if ($positionHistory->is_current) {
            throw new \Exception('Tidak dapat menghapus posisi yang sedang aktif');
        }

        return $positionHistory->delete();
    }

    /**
     * Restore a soft-deleted position history record
     */
    public function restore(string $id)
    {
        return $this->model->withTrashed()->findOrFail($id)->restore();
    }

    /**
     * Deactivate all current positions for an employee
     */
    protected function deactivateCurrentPositions(string $employeeId)
    {
        return $this->model->where('employee_id', $employeeId)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'end_date' => now()
            ]);
    }

    /**
     * Calculate position duration for all positions of an employee
     */
    public function getPositionDuration(string $employeeId)
    {
        $history = $this->model->with(['department', 'position'])
            ->where('employee_id', $employeeId)
            ->orderBy('start_date', 'asc')
            ->get();

        $result = [];
        foreach ($history as $position) {
            $endDate = $position->end_date ?? ($position->is_current ? now() : null);

            $durationInDays = null;
            if ($endDate) {
                $startDate = new \DateTime($position->start_date);
                $endDateObj = new \DateTime($endDate);
                $interval = $startDate->diff($endDateObj);
                $durationInDays = $interval->days;
            }

            $result[] = [
                'id' => $position->id,
                'department' => $position->department->name,
                'position' => $position->position->name,
                'start_date' => $position->start_date,
                'end_date' => $position->end_date,
                'is_current' => $position->is_current,
                'duration_days' => $durationInDays,
                'duration_formatted' => $this->formatDuration($durationInDays),
                'salary' => $position->salary,
                'notes' => $position->notes
            ];
        }

        return $result;
    }

    /**
     * Format duration in days to human-readable format
     */
    protected function formatDuration($days)
    {
        if (!$days) return null;

        $years = floor($days / 365);
        $months = floor(($days % 365) / 30);
        $remainingDays = $days % 30;

        $result = '';
        if ($years > 0) {
            $result .= $years . ' tahun ';
        }
        if ($months > 0) {
            $result .= $months . ' bulan ';
        }
        if ($remainingDays > 0) {
            $result .= $remainingDays . ' hari';
        }

        return trim($result);
    }

    /**
     * Get statistics about employee count by department
     */
    public function getEmployeeCountByDepartment()
    {
        $departmentStats = $this->departmentModel->withCount([
            'positionHistories as employee_count' => function ($query) {
                $query->where('is_current', true);
            }
        ])->get();

        return $departmentStats->map(function ($department) {
            return [
                'id' => $department->id,
                'name' => $department->name,
                'employee_count' => $department->employee_count
            ];
        });
    }

    /**
     * Get statistics about employee count by position
     */
    public function getEmployeeCountByPosition()
    {
        $positionStats = $this->positionModel->withCount([
            'positionHistories as employee_count' => function ($query) {
                $query->where('is_current', true);
            }
        ])->get();

        return $positionStats->map(function ($position) {
            return [
                'id' => $position->id,
                'name' => $position->name,
                'employee_count' => $position->employee_count
            ];
        });
    }

    /**
     * Get organization structure
     */
    public function getOrganizationStructure()
    {
        $departments = $this->departmentModel->with(['positionHistories' => function ($query) {
            $query->where('is_current', true)
                ->with(['position', 'employee.user']);
        }])->get();

        $structure = [];
        foreach ($departments as $department) {
            $positionData = [];
            $positionGroups = $department->positionHistories->groupBy('position_id');

            foreach ($positionGroups as $positionId => $histories) {
                $positionName = $histories->first()->position->name;
                $employees = [];

                foreach ($histories as $history) {
                    if ($history->employee && $history->employee->user) {
                        $employees[] = [
                            'id' => $history->employee->id,
                            'name' => $history->employee->user->name,
                            'salary' => $history->salary
                        ];
                    }
                }

                $positionData[] = [
                    'id' => $positionId,
                    'name' => $positionName,
                    'employee_count' => count($employees),
                    'employees' => $employees
                ];
            }

            $structure[] = [
                'id' => $department->id,
                'name' => $department->name,
                'positions' => $positionData
            ];
        }

        return $structure;
    }

    /**
     * Update salary for an employee without changing position
     */
    public function updateSalary(string $employeeId, float $newSalary, array $additionalData = [])
    {
        DB::beginTransaction();
        try {
            // Ambil posisi current karyawan
            $currentPosition = $this->model->where('employee_id', $employeeId)
                ->where('is_current', true)
                ->first();

            if (!$currentPosition) {
                DB::rollBack();
                return false;
            }

            // Tandai posisi lama bukan current
            $currentPosition->update([
                'is_current' => false,
                'end_date' => $additionalData['start_date'] ?? now()
            ]);

            // Buat posisi baru dengan gaji baru
            $newPosition = $this->model->createNewPosition([
                'employee_id' => $employeeId,
                'position_id' => $currentPosition->position_id,
                'department_id' => $currentPosition->department_id,
                'start_date' => $additionalData['start_date'] ?? now(),
                'is_current' => true,
                'salary' => $newSalary,
                'notes' => $additionalData['notes'] ?? 'Perubahan gaji',
                'created_by' => auth()->id() ?? null,
                'approved_by' => $additionalData['approved_by'] ?? auth()->id() ?? null
            ]);

            DB::commit();
            return $newPosition;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get salary history for an employee
     */
    public function getSalaryHistory(string $employeeId)
    {
        $history = $this->model->with(['department', 'position'])
            ->where('employee_id', $employeeId)
            ->orderBy('start_date', 'desc')
            ->get();

        $result = [];
        $previousSalary = null;

        foreach ($history as $index => $position) {
            $salaryChange = null;
            $salaryChangePercentage = null;

            // Hitung perubahan gaji dari posisi sebelumnya
            if ($index < count($history) - 1 && $history[$index + 1]->salary > 0) {
                $previousSalary = $history[$index + 1]->salary;
                $salaryChange = $position->salary - $previousSalary;
                $salaryChangePercentage = ($salaryChange / $previousSalary) * 100;
            }

            $result[] = [
                'id' => $position->id,
                'department' => $position->department->name,
                'position' => $position->position->name,
                'start_date' => $position->start_date,
                'end_date' => $position->end_date,
                'is_current' => $position->is_current,
                'salary' => $position->salary,
                'salary_change' => $salaryChange,
                'salary_change_percentage' => $salaryChangePercentage,
                'notes' => $position->notes
            ];
        }

        return $result;
    }

    /**
     * Get salary comparison by department
     */
    public function getSalaryComparisonByDepartment()
    {
        $departments = $this->departmentModel->withCount([
            'positionHistories as employee_count' => function ($query) {
                $query->where('is_current', true);
            }
        ])->get();

        $results = [];
        foreach ($departments as $department) {
            $salaryData = $this->model->where('department_id', $department->id)
                ->where('is_current', true)
                ->get(['salary']);

            $salaries = $salaryData->pluck('salary')->filter()->toArray();

            $avgSalary = count($salaries) > 0 ? array_sum($salaries) / count($salaries) : 0;
            $maxSalary = count($salaries) > 0 ? max($salaries) : 0;
            $minSalary = count($salaries) > 0 ? min($salaries) : 0;

            $results[] = [
                'id' => $department->id,
                'name' => $department->name,
                'employee_count' => $department->employee_count,
                'avg_salary' => $avgSalary,
                'max_salary' => $maxSalary,
                'min_salary' => $minSalary,
            ];
        }

        return $results;
    }

    /**
     * Get salary comparison by position
     */
    public function getSalaryComparisonByPosition()
    {
        $positions = $this->positionModel->withCount([
            'positionHistories as employee_count' => function ($query) {
                $query->where('is_current', true);
            }
        ])->get();

        $results = [];
        foreach ($positions as $position) {
            $salaryData = $this->model->where('position_id', $position->id)
                ->where('is_current', true)
                ->get(['salary']);

            $salaries = $salaryData->pluck('salary')->filter()->toArray();

            $avgSalary = count($salaries) > 0 ? array_sum($salaries) / count($salaries) : 0;
            $maxSalary = count($salaries) > 0 ? max($salaries) : 0;
            $minSalary = count($salaries) > 0 ? min($salaries) : 0;

            $results[] = [
                'id' => $position->id,
                'name' => $position->name,
                'employee_count' => $position->employee_count,
                'avg_salary' => $avgSalary,
                'max_salary' => $maxSalary,
                'min_salary' => $minSalary,
            ];
        }

        return $results;
    }

    /**
     * Get salary increase percentage for an employee in a given time period
     */
    public function getSalaryIncreasePercentage(string $employeeId, string $startDate, string $endDate = null)
    {
        $endDate = $endDate ?? now()->toDateString();

        $history = $this->model->with(['department', 'position'])
            ->where('employee_id', $employeeId)
            ->where('start_date', '>=', $startDate)
            ->where(function ($query) use ($endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->orWhereNull('end_date');
            })
            ->orderBy('start_date', 'asc')
            ->get();

        if ($history->isEmpty()) {
            return [
                'initial_salary' => 0,
                'current_salary' => 0,
                'increase_amount' => 0,
                'increase_percentage' => 0,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'salary_history' => []
            ];
        }

        $initialSalary = $history->first()->salary;
        $currentSalary = $history->last()->salary;
        $increaseAmount = $currentSalary - $initialSalary;
        $increasePercentage = $initialSalary > 0 ? ($increaseAmount / $initialSalary) * 100 : 0;

        $salaryHistory = $history->map(function ($position) {
            return [
                'start_date' => $position->start_date,
                'end_date' => $position->end_date,
                'department' => $position->department->name,
                'position' => $position->position->name,
                'salary' => $position->salary,
                'notes' => $position->notes
            ];
        });

        return [
            'initial_salary' => $initialSalary,
            'current_salary' => $currentSalary,
            'increase_amount' => $increaseAmount,
            'increase_percentage' => $increasePercentage,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'salary_history' => $salaryHistory
        ];
    }
}
