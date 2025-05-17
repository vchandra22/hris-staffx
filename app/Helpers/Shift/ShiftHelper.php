<?php

namespace App\Helpers\Shift;

use App\Helpers\Venturo;
use App\Models\ShiftModel;
use App\Models\EmployeeModel;

class ShiftHelper extends Venturo
{
    private $shift;
    private $employee;

    public function __construct()
    {
        $this->shift = new ShiftModel();
        $this->employee = new EmployeeModel();
    }

    /**
     * Get all shift records with filtering and pagination
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $shifts = $this->shift->query()
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
            ->orderBy($filter['sort_by'] ?? 'date', $filter['sort_desc'] ?? 'desc');

        return $shifts->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Get shift record by ID
     *
     * @param string $id
     * @return ShiftModel
     */
    public function getById(string $id): ShiftModel
    {
        return $this->shift->with(['employee.user'])->findOrFail($id);
    }

    /**
     * Create new shift record
     *
     * @param array $payload
     * @return ShiftModel
     */
    public function store(array $payload): ShiftModel
    {
        try {
            $this->beginTransaction();

            // Validate employee exists
            if (!empty($payload['employee_id'])) {
                $this->employee->findOrFail($payload['employee_id']);
            }

            // Create shift record
            $shift = $this->shift->store($payload);

            $this->commitTransaction();

            return $shift->load('employee.user');
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Update shift record
     *
     * @param string $id
     * @param array $payload
     * @return ShiftModel
     */
    public function update(string $id, array $payload): ShiftModel
    {
        try {
            $this->beginTransaction();

            $shift = $this->shift->findOrFail($id);

            // Update shift data
            $shift->edit($payload, $id);

            $this->commitTransaction();

            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Delete shift record
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $shift = $this->shift->findOrFail($id);
            $result = $shift->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Restore deleted shift record
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $shift = $this->shift->withTrashed()->findOrFail($id);
            $result = $shift->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }
}
