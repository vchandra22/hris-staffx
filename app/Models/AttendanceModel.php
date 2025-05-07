<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_attendance';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'status',
        'late_minutes',
        'early_leave_minutes',
        'overtime_minutes',
        'lat',
        'lng',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'lat' => 'float',
        'lng' => 'float'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    /**
     * Get all attendance records with filtering and pagination
     *
     * @param array $filter
     * @param int $page
     * @param int $itemPerPage
     * @param string $sort
     * @return LengthAwarePaginator
     */
    public function getAll(array $filter, int $page = 1, int $itemPerPage = 10, string $sort = 'date,desc'): LengthAwarePaginator
    {
        $attendance = $this->query();

        if (!empty($filter['employee_id'])) {
            $attendance->where('employee_id', $filter['employee_id']);
        }

        if (!empty($filter['date'])) {
            $attendance->whereDate('date', $filter['date']);
        }

        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $attendance->whereBetween('date', [$filter['start_date'], $filter['end_date']]);
        }

        if (isset($filter['status']) && !empty($filter['status'])) {
            $attendance->where('status', $filter['status']);
        }

        if (isset($filter['has_checkout'])) {
            if ($filter['has_checkout']) {
                $attendance->whereNotNull('check_out');
            } else {
                $attendance->whereNull('check_out');
            }
        }

        $sortArray = explode(',', $sort);
        if (count($sortArray) === 2) {
            $attendance->orderBy($sortArray[0], $sortArray[1]);
        } else {
            $attendance->orderBy('date', 'desc');
        }

        return $attendance->paginate($itemPerPage, ['*'], 'page', $page);
    }

    /**
     * Get attendance record by ID
     *
     * @param string $id
     * @return AttendanceModel
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(string $id): object
    {
        return $this->findOrFail($id);
    }

    /**
     * Create new attendance record
     *
     * @param array $payload
     * @return AttendanceModel
     */
    public function store(array $payload): object
    {
        return $this->create($payload);
    }

    /**
     * Update attendance record
     *
     * @param array $payload
     * @param string $id
     * @return bool
     */
    public function edit(array $payload, string $id): bool
    {
        $attendance = $this->findOrFail($id);
        return $attendance->update($payload);
    }

    /**
     * Delete attendance record
     *
     * @param string $id
     * @return bool
     */
    public function drop(string $id): bool
    {
        $attendance = $this->findOrFail($id);
        return $attendance->delete();
    }

    /**
     * Scope query to current day's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }

    /**
     * Scope query for attendance without checkout
     */
    public function scopeNoCheckout($query)
    {
        return $query->whereNotNull('check_in')->whereNull('check_out');
    }
}
