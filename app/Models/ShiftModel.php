<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'end_time'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
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

        $sort = $sort ?: 'date,desc';
        $sortArray = explode(',', $sort);
        $shift->orderBy($sortArray[0], $sortArray[1]);

        return $shift->paginate($itemPerPage);
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
