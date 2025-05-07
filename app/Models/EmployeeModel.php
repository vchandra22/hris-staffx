<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'hire_date',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected $appends = [
        'current_department_id',
        'current_position_id',
        'current_salary',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(UserModel::class, 'user_id', 'id');
    }

    // Relasi untuk mendapatkan semua riwayat posisi
    public function positionHistory()
    {
        return $this->hasMany(EmployeePositionHistoryModel::class, 'employee_id', 'id')
            ->orderBy('start_date', 'desc');
    }

    // Relasi untuk mendapatkan posisi terkini
    public function currentPosition()
    {
        return $this->hasOne(EmployeePositionHistoryModel::class, 'employee_id', 'id')
            ->where('is_current', true);
    }

    // Accessor untuk mendapatkan department_id saat ini
    public function getCurrentDepartmentIdAttribute()
    {
        return $this->currentPosition?->department_id;
    }

    // Accessor untuk mendapatkan position_id saat ini
    public function getCurrentPositionIdAttribute()
    {
        return $this->currentPosition?->position_id;
    }

    // Accessor untuk mendapatkan salary saat ini
    public function getCurrentSalaryAttribute()
    {
        return $this->currentPosition?->salary;
    }

    // Relasi untuk mendapatkan department saat ini
    public function department()
    {
        return $this->hasOneThrough(
            DepartmentModel::class,
            EmployeePositionHistoryModel::class,
            'employee_id', // Foreign key pada EmployeePositionHistoryModel
            'id', // Foreign key pada DepartmentModel
            'id', // Local key pada EmployeeModel
            'department_id' // Local key pada EmployeePositionHistoryModel
        )->where('m_employee_position_history.is_current', true);
    }

    // Relasi untuk mendapatkan position saat ini
    public function position()
    {
        return $this->hasOneThrough(
            PositionModel::class,
            EmployeePositionHistoryModel::class,
            'employee_id', // Foreign key pada EmployeePositionHistoryModel
            'id', // Foreign key pada PositionModel
            'id', // Local key pada EmployeeModel
            'position_id' // Local key pada EmployeePositionHistoryModel
        )->where('m_employee_position_history.is_current', true);
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $employee = $this->query();

        // Join dengan tabel users untuk pencarian berdasarkan nama
        if (!empty($filter['name'])) {
            $employee->whereHas('user', function ($query) use ($filter) {
                $query->where('name', 'LIKE', '%' . $filter['name'] . '%');
            });
        }

        // Filter berdasarkan department_id menggunakan relasi currentPosition
        if (!empty($filter['department_id'])) {
            $employee->whereHas('currentPosition', function ($query) use ($filter) {
                $query->where('department_id', $filter['department_id']);
            });
        }

        // Filter berdasarkan position_id menggunakan relasi currentPosition
        if (!empty($filter['position_id'])) {
            $employee->whereHas('currentPosition', function ($query) use ($filter) {
                $query->where('position_id', $filter['position_id']);
            });
        }

        // Filter berdasarkan tanggal masuk
        if (!empty($filter['hire_date_from'])) {
            $employee->whereDate('hire_date', '>=', $filter['hire_date_from']);
        }

        if (!empty($filter['hire_date_to'])) {
            $employee->whereDate('hire_date', '<=', $filter['hire_date_to']);
        }

        // Pengurutan data
        $sort = $sort ?: 'id,asc';
        $sortArray = explode(',', $sort);

        // Jika pengurutan berdasarkan departemen atau posisi, gunakan join
        if (in_array($sortArray[0], ['department', 'position'])) {
            $employee->join('m_employee_position_history as history', 'history.employee_id', '=', 'm_employees.id')
                ->where('history.is_current', true);

            if ($sortArray[0] === 'department') {
                $employee->join('m_departments as dept', 'dept.id', '=', 'history.department_id')
                    ->orderBy('dept.name', $sortArray[1])
                    ->select('m_employees.*');
            } else if ($sortArray[0] === 'position') {
                $employee->join('m_positions as pos', 'pos.id', '=', 'history.position_id')
                    ->orderBy('pos.name', $sortArray[1])
                    ->select('m_employees.*');
            }
        } else {
            $employee->orderBy($sortArray[0], $sortArray[1]);
        }

        return $employee->paginate($itemPerPage);
    }

    public function getById(string $id): object
    {
        return $this->with(['user', 'currentPosition.department', 'currentPosition.position'])->findOrFail($id);
    }

    public function store(array $payload): object
    {
        // Pisahkan data untuk tabel m_employees dan riwayat posisi
        $employeeData = array_intersect_key($payload, array_flip([
            'id', 'user_id', 'birth_place', 'birth_date', 'address', 'hire_date'
        ]));

        // Simpan data dasar karyawan
        $employee = $this->create($employeeData);

        // Jika terdapat data posisi, simpan ke dalam riwayat posisi
        if (isset($payload['position_id']) && isset($payload['department_id'])) {
            // Simpan riwayat posisi awal
            EmployeePositionHistoryModel::createNewPosition([
                'employee_id' => $employee->id,
                'position_id' => $payload['position_id'],
                'department_id' => $payload['department_id'],
                'start_date' => $payload['hire_date'] ?? now(),
                'is_current' => true,
                'salary' => $payload['salary'] ?? null,
                'notes' => 'Initial position',
                'created_by' => auth()->id() ?? null,
                'approved_by' => auth()->id() ?? null
            ]);
        }

        return $employee;
    }

    public function edit(array $payload, string $id): object
    {
        $employee = $this->find($id);

        // Pisahkan data untuk tabel m_employees dan riwayat posisi
        $employeeData = array_intersect_key($payload, array_flip([
            'birth_place', 'birth_date', 'address', 'hire_date'
        ]));

        // Update data karyawan
        $employee->update($employeeData);

        // Jika terdapat perubahan posisi, departemen, atau gaji
        if (isset($payload['position_id']) || isset($payload['department_id']) || isset($payload['salary'])) {
            $currentPosition = $employee->currentPosition;

            // Periksa apakah posisi berubah
            $positionChanged = false;

            if (isset($payload['position_id']) && $currentPosition && $payload['position_id'] != $currentPosition->position_id) {
                $positionChanged = true;
            }

            if (isset($payload['department_id']) && $currentPosition && $payload['department_id'] != $currentPosition->department_id) {
                $positionChanged = true;
            }

            if (isset($payload['salary']) && $currentPosition && $payload['salary'] != $currentPosition->salary) {
                $positionChanged = true;
            }

            // Jika posisi berubah, buat riwayat baru
            if ($positionChanged) {
                EmployeePositionHistoryModel::createNewPosition([
                    'employee_id' => $id,
                    'position_id' => $payload['position_id'] ?? $currentPosition->position_id,
                    'department_id' => $payload['department_id'] ?? $currentPosition->department_id,
                    'start_date' => $payload['position_start_date'] ?? now(),
                    'is_current' => true,
                    'salary' => $payload['salary'] ?? $currentPosition->salary,
                    'notes' => $payload['position_notes'] ?? 'Position updated',
                    'created_by' => auth()->id() ?? null,
                    'approved_by' => $payload['approved_by'] ?? auth()->id() ?? null
                ]);
            }
        }

        return $employee;
    }

    public function drop(string $id): bool
    {
        return $this->find($id)->delete();
    }

    // Method tambahan untuk mutasi karyawan
    public function mutate(string $id, array $positionData): bool
    {
        // Cari data karyawan
        $employee = $this->find($id);

        if (!$employee) {
            return false;
        }

        // Buat riwayat posisi baru
        EmployeePositionHistoryModel::createNewPosition(array_merge(
            ['employee_id' => $id],
            $positionData
        ));

        return true;
    }

    // Method untuk mendapatkan riwayat posisi karyawan
    public function getPositionHistory(string $id)
    {
        $employee = $this->with(['positionHistory.department', 'positionHistory.position'])->find($id);

        if (!$employee) {
            return collect();
        }

        return $employee->positionHistory;
    }
}
