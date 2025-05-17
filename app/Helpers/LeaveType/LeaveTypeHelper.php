<?php

namespace App\Helpers\LeaveType;

use App\Helpers\Venturo;
use App\Models\LeaveTypeModel;

class LeaveTypeHelper extends Venturo
{
    private $leaveType;

    public function __construct()
    {
        $this->leaveType = new LeaveTypeModel();
    }

    public function getAll(array $filter = []): object
    {
        $leaveTypes = $this->leaveType->query()
            ->when(!empty($filter['search']), function ($query) use ($filter) {
                return $query->where('name', 'like', '%' . $filter['search'] . '%');
            })
            ->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $leaveTypes->paginate($filter['per_page'] ?? 10);
    }

    public function getById(string $id): LeaveTypeModel
    {
        return $this->leaveType->findOrFail($id);
    }

    public function store(array $payload): LeaveTypeModel
    {
        try {
            $this->beginTransaction();
            $leaveType = $this->leaveType->store($payload);
            $this->commitTransaction();
            return $leaveType;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    public function update(string $id, array $payload): LeaveTypeModel
    {
        try {
            $this->beginTransaction();
            $this->leaveType->edit($payload, $id);
            $this->commitTransaction();
            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();
            $leaveType = $this->leaveType->findOrFail($id);
            $result = $leaveType->delete();
            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();
            $leaveType = $this->leaveType->withTrashed()->findOrFail($id);
            $result = $leaveType->restore();
            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }
}
