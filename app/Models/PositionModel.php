<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PositionModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_positions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description'
    ];

    // Relationships
    // Relasi ke tabel riwayat posisi
    public function positionHistories()
    {
        return $this->hasMany(EmployeePositionHistoryModel::class, 'position_id', 'id');
    }

    // Relasi untuk mendapatkan semua karyawan yang saat ini berada di posisi ini
    public function employees()
    {
        return $this->hasManyThrough(
            EmployeeModel::class,
            EmployeePositionHistoryModel::class,
            'position_id', // Foreign key pada EmployeePositionHistoryModel
            'id', // Foreign key pada EmployeeModel
            'id', // Local key pada PositionModel
            'employee_id' // Local key pada EmployeePositionHistoryModel
        )->where('m_employee_position_history.is_current', true);
    }

    // Relasi untuk mendapatkan semua karyawan (historis) di posisi ini
    public function allEmployees()
    {
        return $this->belongsToMany(
            EmployeeModel::class,
            'm_employee_position_history',
            'position_id',
            'employee_id'
        )
            ->withPivot(['department_id', 'start_date', 'end_date', 'is_current', 'salary'])
            ->withTimestamps();
    }

    // Mendapatkan jumlah karyawan aktif di posisi
    public function getEmployeeCountAttribute()
    {
        return $this->positionHistories()->where('is_current', true)->count();
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $position = $this->query();

        if (!empty($filter['name'])) {
            $position->where('name', 'LIKE', '%' . $filter['name'] . '%');
        }

        if (!empty($filter['department_id'])) {
            $position->whereHas('positionHistories', function ($query) use ($filter) {
                $query->where('department_id', $filter['department_id'])
                    ->where('is_current', true);
            });
        }

        $sort = $sort ?: 'id,asc';
        $sortArray = explode(',', $sort);
        $position->orderBy($sortArray[0], $sortArray[1]);

        return $position->paginate($itemPerPage);
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
