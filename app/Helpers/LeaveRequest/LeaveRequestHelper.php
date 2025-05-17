<?php

namespace App\Helpers\LeaveRequest;

use App\Helpers\Venturo;
use App\Models\LeaveRequestModel;
use App\Models\EmployeeModel;

class LeaveRequestHelper extends Venturo
{
    private $leaveRequest;
    private $employee;

    public function __construct()
    {
        $this->leaveRequest = new LeaveRequestModel();
        $this->employee = new EmployeeModel();
    }

    /**
     * Get all leave request records with filtering and pagination
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $leaveRequests = $this->leaveRequest->query()
            ->with(['employee.user', 'leaveType', 'approver'])
            ->when(!empty($filter['search']), function ($query) use ($filter) {
                return $query->where('reason', 'like', '%' . $filter['search'] . '%')
                    ->orWhereHas('employee.user', function ($userQuery) use ($filter) {
                        $userQuery->where('name', 'like', '%' . $filter['search'] . '%');
                    });
            })
            ->when(!empty($filter['employee_id']), function ($query) use ($filter) {
                return $query->where('employee_id', $filter['employee_id']);
            })
            ->when(!empty($filter['status']), function ($query) use ($filter) {
                return $query->where('status', $filter['status']);
            })
            ->when(!empty($filter['start_date']), function ($query) use ($filter) {
                return $query->where('start_date', '>=', $filter['start_date']);
            })
            ->when(!empty($filter['end_date']), function ($query) use ($filter) {
                return $query->where('end_date', '<=', $filter['end_date']);
            })
            ->when(!empty($filter['date_range']), function ($query) use ($filter) {
                return $query->where(function ($q) use ($filter) {
                    $q->whereBetween('start_date', $filter['date_range'])
                      ->orWhereBetween('end_date', $filter['date_range']);
                });
            })
            ->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $leaveRequests->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Get leave request record by ID
     *
     * @param string $id
     * @return LeaveRequestModel
     */
    public function getById(string $id): LeaveRequestModel
    {
        return $this->leaveRequest->with(['employee.user', 'leaveType', 'approver'])->findOrFail($id);
    }

    /**
     * Create new leave request record
     *
     * @param array $payload
     * @return LeaveRequestModel
     */
    public function store(array $payload): LeaveRequestModel
    {
        try {
            $this->beginTransaction();

            // Validate employee exists
            if (!empty($payload['employee_id'])) {
                $this->employee->findOrFail($payload['employee_id']);
            }

            // Calculate total days if not provided
            if (empty($payload['total_days']) && !empty($payload['start_date']) && !empty($payload['end_date'])) {
                $startDate = new \DateTime($payload['start_date']);
                $endDate = new \DateTime($payload['end_date']);
                $interval = $startDate->diff($endDate);
                $payload['total_days'] = $interval->days + 1;

                // Adjust for half day if applicable
                if (!empty($payload['half_day']) && $payload['half_day']) {
                    $payload['total_days'] -= 0.5;
                }
            }

            // Set default status if not provided
            if (empty($payload['status'])) {
                $payload['status'] = LeaveRequestModel::STATUS_PENDING;
            }

            // Create leave request record
            $leaveRequest = $this->leaveRequest->store($payload);

            $this->commitTransaction();

            return $leaveRequest->load('employee.user', 'leaveType', 'approver');
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Update leave request record
     *
     * @param string $id
     * @param array $payload
     * @return LeaveRequestModel
     */
    public function update(string $id, array $payload): LeaveRequestModel
    {
        try {
            $this->beginTransaction();

            $leaveRequest = $this->leaveRequest->findOrFail($id);

            // Recalculate total days if dates changed
            if ((isset($payload['start_date']) || isset($payload['end_date'])) &&
                (isset($payload['start_date']) && isset($payload['end_date']))) {
                $startDate = new \DateTime($payload['start_date']);
                $endDate = new \DateTime($payload['end_date']);
                $interval = $startDate->diff($endDate);
                $payload['total_days'] = $interval->days + 1;

                // Adjust for half day if applicable
                if (isset($payload['half_day']) && $payload['half_day']) {
                    $payload['total_days'] -= 0.5;
                }
            }

            // Update leave request data
            $leaveRequest->edit($payload, $id);

            $this->commitTransaction();

            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Delete leave request record
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $leaveRequest = $this->leaveRequest->findOrFail($id);
            $result = $leaveRequest->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Restore deleted leave request record
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $leaveRequest = $this->leaveRequest->withTrashed()->findOrFail($id);
            $result = $leaveRequest->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Approve a leave request
     *
     * @param string $id
     * @param string $approvedBy
     * @return LeaveRequestModel
     */
    public function approve(string $id, string $approvedBy): LeaveRequestModel
    {
        try {
            $this->beginTransaction();

            $leaveRequest = $this->leaveRequest->findOrFail($id);

            $payload = [
                'status' => LeaveRequestModel::STATUS_APPROVED,
                'approved_by' => $approvedBy,
                'approved_at' => now()
            ];

            $leaveRequest->edit($payload, $id);

            $this->commitTransaction();

            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Reject a leave request
     *
     * @param string $id
     * @param string $rejectionReason
     * @return LeaveRequestModel
     */
    public function reject(string $id, string $rejectionReason): LeaveRequestModel
    {
        try {
            $this->beginTransaction();

            $leaveRequest = $this->leaveRequest->findOrFail($id);

            $payload = [
                'status' => LeaveRequestModel::STATUS_REJECTED,
                'rejection_reason' => $rejectionReason
            ];

            $leaveRequest->edit($payload, $id);

            $this->commitTransaction();

            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }
}
