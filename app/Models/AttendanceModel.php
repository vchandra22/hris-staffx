<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'check_out'
    ];

    protected $casts = [
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
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

        if (isset($filter['has_checkout'])) {
            if ($filter['has_checkout']) {
                $attendance->whereNotNull('check_out');
            } else {
                $attendance->whereNull('check_out');
            }
        }

        $sort = $sort ?: 'date,desc';
        $sortArray = explode(',', $sort);
        $attendance->orderBy($sortArray[0], $sortArray[1]);

        return $attendance->paginate($itemPerPage);
    }

    public function getById(string $id): object
    {
        return $this->findOrFail($id);
    }

    public function store(array $payload): object
    {
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
}
