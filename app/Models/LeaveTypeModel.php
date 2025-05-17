<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveTypeModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_leave_types';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'annual_allowance',
        'requires_approval',
        'minimum_notice_days',
        'maximum_days_per_request',
        'carried_forward',
        'carry_forward_max_days',
        'requires_attachment',
        'half_day_allowed'
    ];

    // Relationships
    // Relasi ke LeaveRequestModel
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequestModel::class, 'leave_type_id', 'id');
    }

    // Mendapatkan jumlah permintaan cuti untuk tipe ini
    public function getRequestCountAttribute()
    {
        return $this->leaveRequests()->count();
    }

    // CRUD methods
    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $leaveType = $this->query();

        if (!empty($filter['name'])) {
            $leaveType->where('name', 'LIKE', '%' . $filter['name'] . '%');
        }

        if (isset($filter['requires_approval'])) {
            $leaveType->where('requires_approval', $filter['requires_approval']);
        }

        $sort = $sort ?: 'id,asc';
        $sortArray = explode(',', $sort);
        $leaveType->orderBy($sortArray[0], $sortArray[1]);

        return $leaveType->paginate($itemPerPage);
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
        $leaveType = $this->findOrFail($id);
        $leaveType->update($payload);
        return $leaveType;
    }

    public function drop(string $id): bool
    {
        return $this->find($id)->delete();
    }
}
