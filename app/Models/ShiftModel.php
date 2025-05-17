<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Pagination\LengthAwarePaginator;

class ShiftModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_shifts';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'employee_id',
        'date',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    /**
     * Get all shift records with filtering and pagination
     *
     * @param array $filter
     * @param int $page
     * @param int $itemPerPage
     * @param string $sort
     * @return LengthAwarePaginator
     */
    public function getAll(array $filter, int $page = 1, int $itemPerPage = 10, string $sort = 'date,desc'): LengthAwarePaginator
    {
        $shift = $this->query();

        if (!empty($filter['employee_id'])) {
            $shift->where('employee_id', $filter['employee_id']);
        }

        if (!empty($filter['date'])) {
            $shift->whereDate('date', $filter['date']);
        }

        if (!empty($filter['start_date']) && !empty($filter['end_date'])) {
            $shift->whereBetween('date', [$filter['start_date'], $filter['end_date']]);
        }

        $sortArray = explode(',', $sort);
        if (count($sortArray) === 2) {
            $shift->orderBy($sortArray[0], $sortArray[1]);
        } else {
            $shift->orderBy('date', 'desc');
        }

        return $shift->paginate($itemPerPage, ['*'], 'page', $page);
    }

    /**
     * Get shift record by ID
     *
     * @param string $id
     * @return ShiftModel
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(string $id): object
    {
        return $this->findOrFail($id);
    }

    /**
     * Create new shift record
     *
     * @param array $payload
     * @return ShiftModel
     */
    public function store(array $payload): object
    {
        return $this->create($payload);
    }

    /**
     * Update shift record
     *
     * @param array $payload
     * @param string $id
     * @return bool
     */
    public function edit(array $payload, string $id): bool
    {
        $shift = $this->findOrFail($id);
        return $shift->update($payload);
    }

    /**
     * Delete shift record
     *
     * @param string $id
     * @return bool
     */
    public function drop(string $id): bool
    {
        $shift = $this->findOrFail($id);
        return $shift->delete();
    }

    /**
     * Scope query to current day's shifts
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', now()->toDateString());
    }
}
