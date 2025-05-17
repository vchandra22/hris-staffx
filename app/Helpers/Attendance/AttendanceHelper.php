<?php

namespace App\Helpers\Attendance;

use App\Helpers\Venturo;
use App\Models\AttendanceModel;
use App\Models\EmployeeModel;

class AttendanceHelper extends Venturo
{
    private $attendance;
    private $employee;

    public function __construct()
    {
        $this->attendance = new AttendanceModel();
        $this->employee = new EmployeeModel();
    }

    /**
     * Get all attendance records with filtering and pagination
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $attendances = $this->attendance->query()
            ->with(['employee.user'])
            ->when(!empty($filter['search']), function ($query) use ($filter) {
                return $query->whereHas('employee.user', function ($userQuery) use ($filter) {
                    $userQuery->where('name', 'like', '%' . $filter['search'] . '%');
                });
            })
            ->when(!empty($filter['employee_id']), function ($query) use ($filter) {
                return $query->where('employee_id', $filter['employee_id']);
            })
            ->when(!empty($filter['date']), function ($query) use ($filter) {
                return $query->whereDate('date', $filter['date']);
            })
            ->when(!empty($filter['start_date']) && !empty($filter['end_date']), function ($query) use ($filter) {
                return $query->whereBetween('date', [$filter['start_date'], $filter['end_date']]);
            })
            ->when(isset($filter['status']) && !empty($filter['status']), function ($query) use ($filter) {
                return $query->where('status', $filter['status']);
            })
            ->orderBy($filter['sort_by'] ?? 'date', $filter['sort_desc'] ?? 'desc');

        return $attendances->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Get attendance record by ID
     *
     * @param string $id
     * @return AttendanceModel
     */
    public function getById(string $id): AttendanceModel
    {
        return $this->attendance->with(['employee.user'])->findOrFail($id);
    }

    /**
     * Create new attendance record
     *
     * @param array $payload
     * @return AttendanceModel
     */
    public function store(array $payload): AttendanceModel
    {
        try {
            $this->beginTransaction();

            // Validate employee exists
            if (!empty($payload['employee_id'])) {
                $this->employee->findOrFail($payload['employee_id']);
            }

            // Create attendance record
            $attendance = $this->attendance->store($payload);

            $this->commitTransaction();

            return $attendance->load('employee.user');
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Update attendance record
     *
     * @param string $id
     * @param array $payload
     * @return AttendanceModel
     */
    public function update(string $id, array $payload): AttendanceModel
    {
        try {
            $this->beginTransaction();

            $attendance = $this->attendance->findOrFail($id);

            // Update attendance data
            $attendance->edit($payload, $id);

            $this->commitTransaction();

            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Delete attendance record
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $attendance = $this->attendance->findOrFail($id);
            $result = $attendance->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Restore deleted attendance record
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $attendance = $this->attendance->withTrashed()->findOrFail($id);
            $result = $attendance->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }
}
