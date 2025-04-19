<?php

namespace App\Helpers\Department;

use App\Helpers\Venturo;
use App\Models\DepartmentModel;

class DepartmentHelper extends Venturo
{
    private $department;

    public function __construct()
    {
        $this->department = new DepartmentModel();
    }

    /**
     * Mengambil daftar department dengan paginasi
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $departments = $this->department->query()
            ->when(isset($filter['search']), function ($query) use ($filter) {
                return $query->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            })
            ->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $departments->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Menyimpan data department baru
     *
     * @param array $payload
     * @return DepartmentModel
     */
    public function store(array $payload): DepartmentModel
    {
        try {
            $this->beginTransaction();

            $department = $this->department->create($payload);

            $this->commitTransaction();
            return $department;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengambil detail department berdasarkan ID
     *
     * @param string $id
     * @return DepartmentModel
     */
    public function getById(string $id): DepartmentModel
    {
        return $this->department->findOrFail($id);
    }

    /**
     * Memperbarui data department
     *
     * @param string $id
     * @param array $payload
     * @return DepartmentModel
     */
    public function update(string $id, array $payload): DepartmentModel
    {
        try {
            $this->beginTransaction();

            $department = $this->department->findOrFail($id);
            $department->update($payload);

            $this->commitTransaction();
            return $department->fresh();
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Menghapus data department (soft delete)
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $department = $this->department->findOrFail($id);
            $result = $department->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Memulihkan data department yang telah dihapus
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $department = $this->department->withTrashed()->findOrFail($id);
            $result = $department->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengecek apakah nama department sudah digunakan
     *
     * @param string $name
     * @param string|null $excludeId
     * @return bool
     */
    public function isNameExists(string $name, ?string $excludeId = null): bool
    {
        return $this->department->where('name', $name)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();
    }
}
