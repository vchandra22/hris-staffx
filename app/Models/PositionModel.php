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
    public function employees()
    {
        return $this->hasMany(EmployeeModel::class, 'position_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $position = $this->query();

        if (!empty($filter['name'])) {
            $position->where('name', 'LIKE', '%' . $filter['name'] . '%');
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
