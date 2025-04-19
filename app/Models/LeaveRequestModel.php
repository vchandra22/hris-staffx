<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequestModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_leave_request';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'id',
        'employee_id',
        'start_date',
        'end_date',
        'reason',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $leaveRequest = $this->query();

        if (!empty($filter['employee_id'])) {
            $leaveRequest->where('employee_id', $filter['employee_id']);
        }

        if (!empty($filter['status'])) {
            $leaveRequest->where('status', $filter['status']);
        }

        if (!empty($filter['start_date'])) {
            $leaveRequest->where('start_date', '>=', $filter['start_date']);
        }

        if (!empty($filter['end_date'])) {
            $leaveRequest->where('end_date', '<=', $filter['end_date']);
        }

        if (!empty($filter['search'])) {
            $leaveRequest->where('reason', 'LIKE', '%' . $filter['search'] . '%');
        }

        if (!empty($filter['date_range'])) {
            $leaveRequest->where(function ($query) use ($filter) {
                $query->whereBetween('start_date', $filter['date_range'])
                    ->orWhereBetween('end_date', $filter['date_range']);
            });
        }

        $sort = $sort ?: 'created_at,desc';
        $sortArray = explode(',', $sort);
        $leaveRequest->orderBy($sortArray[0], $sortArray[1]);

        return $leaveRequest->paginate($itemPerPage);
    }

    public function getById(string $id): object
    {
        return $this->findOrFail($id);
    }

    public function store(array $payload): object
    {
        if (!isset($payload['status'])) {
            $payload['status'] = self::STATUS_PENDING;
        }
        return $this->create($payload);
    }

    public function edit(array $payload, string $id): object
    {
        return $this->find($id)->update($payload);
    }

    public function drop(string $id): bool
    {
        return $this->find($id)->delete();
    }

    // Additional helper methods
    public function approve(): bool
    {
        return $this->update(['status' => self::STATUS_APPROVED]);
    }

    public function reject(): bool
    {
        return $this->update(['status' => self::STATUS_REJECTED]);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
