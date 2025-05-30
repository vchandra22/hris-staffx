<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleModel extends Model implements CrudInterface
{
    use HasFactory;
    use SoftDeletes;
    use Uuid;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'access',
    ];

    protected $table = 'm_user_roles';

    public function drop(string $id)
    {
        $role = $this->find($id);
        if (!$role) {
            return false;
        }
        return $role->delete();
    }

    public function edit(array $payload, string $id)
    {
        $role = $this->find($id);
        if (!$role) {
            return null;
        }
        $role->update($payload);
        return $role;
    }

    public function getAll(array $filter, int $page = 1, int $itemPerPage = 0, string $sort = '')
    {
        $role = $this->query();

        if (! empty($filter['name'])) {
            $role->where('name', 'LIKE', '%'.$filter['name'].'%');
        }

        $total = $role->count();

        if (!empty($sort)) {
            $role->orderByRaw($sort);
        }

        if ($itemPerPage > 0) {
            $skip = ($page * $itemPerPage) - $itemPerPage;
            $role->skip($skip)->take($itemPerPage);
        }

        $list = $role->get();

        return [
            'total' => $total,
            'data' => $list,
        ];
    }

    public function getById(string $id)
    {
        return $this->find($id);
    }

    public function store(array $payload)
    {
        return $this->create($payload);
    }
}
