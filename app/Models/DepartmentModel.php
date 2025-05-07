<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepartmentModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_departments';
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
        return $this->hasMany(EmployeePositionHistoryModel::class, 'department_id', 'id');
    }

    // Relasi untuk mendapatkan semua karyawan yang saat ini berada di departemen ini
    public function employees()
    {
        return $this->hasManyThrough(
            EmployeeModel::class,
            EmployeePositionHistoryModel::class,
            'department_id', // Foreign key pada EmployeePositionHistoryModel
            'id', // Foreign key pada EmployeeModel
            'id', // Local key pada DepartmentModel
            'employee_id' // Local key pada EmployeePositionHistoryModel
        )->where('m_employee_position_history.is_current', true);
    }

    // Relasi untuk mendapatkan semua karyawan (historis) di departemen ini
    public function allEmployees()
    {
        return $this->belongsToMany(
            EmployeeModel::class,
            'm_employee_position_history',
            'department_id',
            'employee_id'
        )
            ->withPivot(['position_id', 'start_date', 'end_date', 'is_current', 'salary'])
            ->withTimestamps();
    }

    // Mendapatkan jumlah karyawan aktif di departemen
    public function getEmployeeCountAttribute()
    {
        return $this->positionHistories()->where('is_current', true)->count();
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $department = $this->query();

        if (!empty($filter['name'])) {
            $department->where('name', 'LIKE', '%' . $filter['name'] . '%');
        }

        $sort = $sort ?: 'id,asc';
        $sortArray = explode(',', $sort);
        $department->orderBy($sortArray[0], $sortArray[1]);

        return $department->paginate($itemPerPage);
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
