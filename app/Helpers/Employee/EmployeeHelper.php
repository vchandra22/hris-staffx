<?php

namespace App\Helpers\Employee;

use App\Helpers\Venturo;
use App\Models\EmployeeModel;

class EmployeeHelper extends Venturo
{
    private $employee;

    public function __construct()
    {
        $this->employee = new EmployeeModel();
    }

    /**
     * Mengambil daftar employee dengan paginasi
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $employees = $this->employee->query()
            ->with(['user', 'department', 'position'])
            ->when(isset($filter['search']), function ($query) use ($filter) {
                return $query->whereHas('user', function ($userQuery) use ($filter) {
                    $userQuery->where('name', 'like', '%' . $filter['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filter['search'] . '%');
                });
            })
            ->when(isset($filter['department_id']), function ($query) use ($filter) {
                return $query->where('department_id', $filter['department_id']);
            })
            ->when(isset($filter['position_id']), function ($query) use ($filter) {
                return $query->where('position_id', $filter['position_id']);
            })
            ->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $employees->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Menyimpan data employee baru
     *
     * @param array $payload
     * @return EmployeeModel
     */
    public function store(array $payload): EmployeeModel
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->create($payload);

            $this->commitTransaction();
            return $employee;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengambil detail employee berdasarkan ID
     *
     * @param string $id
     * @return EmployeeModel
     */
    public function getById(string $id): EmployeeModel
    {
        return $this->employee->with(['user', 'department', 'position'])->findOrFail($id);
    }

    /**
     * Memperbarui data employee
     *
     * @param string $id
     * @param array $payload
     * @return EmployeeModel
     */
    public function update(string $id, array $payload): EmployeeModel
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->findOrFail($id);
            $employee->update($payload);

            $this->commitTransaction();
            return $employee->fresh();
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Menghapus data employee (soft delete)
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->findOrFail($id);
            $result = $employee->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Memulihkan data employee yang telah dihapus
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->withTrashed()->findOrFail($id);
            $result = $employee->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengecek apakah email sudah digunakan
     *
     * @param string $email
     * @param string|null $excludeId
     * @return bool
     */
    public function isEmailExists(string $email, ?string $excludeId = null): bool
    {
        return $this->employee->whereHas('user', function ($query) use ($email, $excludeId) {
            $query->where('email', $email)
                ->when($excludeId, function ($q) use ($excludeId) {
                    return $q->where('id', '!=', $excludeId);
                });
        })->exists();
    }
}
