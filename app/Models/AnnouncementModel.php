<?php

namespace App\Models;

use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnouncementModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes;

    protected $table = 'm_announcements';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'title',
        'content'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $announcement = $this->query();

        if (!empty($filter['search'])) {
            $search = $filter['search'];
            $announcement->where(function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%")->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($filter['date_from'])) {
            $announcement->whereDate('created_at', '>=', $filter['date_from']);
        }

        if (!empty($filter['date_to'])) {
            $announcement->whereDate('created_at', '<=', $filter['date_to']);
        }

        $sort = $sort ?: 'created_at,desc';
        $sortArray = explode(',', $sort);
        $announcement->orderBy($sortArray[0], $sortArray[1]);

        return $announcement->paginate($itemPerPage);
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
