<?php

namespace App\Helpers\Announcement;

use App\Helpers\Venturo;
use App\Models\AnnouncementModel;

class AnnouncementHelper extends Venturo
{
    private $announcement;

    public function __construct()
    {
        $this->announcement = new AnnouncementModel();
    }

    /**
     * Get all announcements with pagination and optional filtering.
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $announcements = $this->announcement->query()
            ->when(isset($filter['search']), function ($query) use ($filter) {
                return $query->where('title', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('content', 'like', '%' . $filter['search'] . '%');
            })
            ->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $announcements->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Store a new announcement.
     *
     * @param array $payload
     * @return AnnouncementModel
     */
    public function store(array $payload): AnnouncementModel
    {
        try {
            $this->beginTransaction();

            $announcement = $this->announcement->create($payload);

            $this->commitTransaction();
            return $announcement;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Get announcement detail by ID.
     *
     * @param string $id
     * @return AnnouncementModel
     */
    public function getById(string $id): AnnouncementModel
    {
        return $this->announcement->findOrFail($id);
    }

    /**
     * Update an announcement.
     *
     * @param string $id
     * @param array $payload
     * @return AnnouncementModel
     */
    public function update(string $id, array $payload): AnnouncementModel
    {
        try {
            $this->beginTransaction();

            $announcement = $this->announcement->findOrFail($id);
            $announcement->update($payload);

            $this->commitTransaction();
            return $announcement->fresh();
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Soft delete an announcement.
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $announcement = $this->announcement->findOrFail($id);
            $result = $announcement->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted announcement.
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $announcement = $this->announcement->withTrashed()->findOrFail($id);
            $result = $announcement->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Check if an announcement title already exists.
     *
     * @param string $title
     * @param string|null $excludeId
     * @return bool
     */
    public function isTitleExists(string $title, ?string $excludeId = null): bool
    {
        return $this->announcement->where('title', $title)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();
    }
}
