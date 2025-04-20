<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class EmployeeModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_employees';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'birth_place',
        'birth_date',
        'address',
        'department_id',
        'position_id',
        'hire_date',
        'salary',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'salary' => 'decimal:2'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }

    public function department()
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id', 'id');
    }

    public function position()
    {
        return $this->belongsTo(PositionModel::class, 'position_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $employee = $this->query();

        if (!empty($filter['name'])) {
            $employee->whereHas('user', function ($query) use ($filter) {
                $query->where('name', 'LIKE', '%' . $filter['name'] . '%');
            });
        }

        if (!empty($filter['department_id'])) {
            $employee->where('department_id', $filter['department_id']);
        }

        if (!empty($filter['position_id'])) {
            $employee->where('position_id', $filter['position_id']);
        }

        $sort = $sort ?: 'id,asc';
        $sortArray = explode(',', $sort);
        $employee->orderBy($sortArray[0], $sortArray[1]);

        return $employee->paginate($itemPerPage);
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
