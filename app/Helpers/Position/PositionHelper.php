<?php

namespace App\Helpers\Position;

use App\Helpers\Venturo;
use App\Models\PositionModel;

class PositionHelper extends Venturo
{
    private $position;

    public function __construct()
    {
        $this->position = new PositionModel();
    }

    /**
     * Mengambil daftar position dengan paginasi
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $positions = $this->position->query()
            ->when(isset($filter['search']), function ($query) use ($filter) {
                return $query->where('name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['search'] . '%');
            })
            ->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $positions->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Menyimpan data position baru
     *
     * @param array $payload
     * @return PositionModel
     */
    public function store(array $payload): PositionModel
    {
        try {
            $this->beginTransaction();

            $position = $this->position->create($payload);

            $this->commitTransaction();
            return $position;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengambil detail position berdasarkan ID
     *
     * @param string $id
     * @return PositionModel
     */
    public function getById(string $id): PositionModel
    {
        return $this->position->findOrFail($id);
    }

    /**
     * Memperbarui data position
     *
     * @param string $id
     * @param array $payload
     * @return PositionModel
     */
    public function update(string $id, array $payload): PositionModel
    {
        try {
            $this->beginTransaction();

            $position = $this->position->findOrFail($id);
            $position->update($payload);

            $this->commitTransaction();
            return $position->fresh();
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Menghapus data position (soft delete)
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $position = $this->position->findOrFail($id);
            $result = $position->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Memulihkan data position yang telah dihapus
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $position = $this->position->withTrashed()->findOrFail($id);
            $result = $position->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengecek apakah nama position sudah digunakan
     *
     * @param string $name
     * @param string|null $excludeId
     * @return bool
     */
    public function isNameExists(string $name, ?string $excludeId = null): bool
    {
        return $this->position->where('name', $name)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();
    }
}
