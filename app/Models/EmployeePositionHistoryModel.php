<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePositionHistoryModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_employee_position_history';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'employee_id',
        'position_id',
        'department_id',
        'start_date',
        'end_date',
        'is_current',
        'salary',
        'notes',
        'approved_by',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationship dengan model karyawan
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    // Relationship dengan model posisi
    public function position()
    {
        return $this->belongsTo(PositionModel::class, 'position_id', 'id');
    }

    // Relationship dengan model departemen
    public function department()
    {
        return $this->belongsTo(DepartmentModel::class, 'department_id', 'id');
    }

    // Relationship dengan user yang menyetujui
    public function approver()
    {
        return $this->belongsTo(UserModel::class, 'approved_by', 'id');
    }

    // Relationship dengan user yang membuat record
    public function creator()
    {
        return $this->belongsTo(UserModel::class, 'created_by', 'id');
    }

    /**
     * Mengambil daftar riwayat posisi dengan filter
     *
     * @param array $filter
     * @param int $page
     * @param int $itemPerPage
     * @param string $sort
     * @return object
     */
    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $history = $this->query()
            ->with(['employee.user', 'position', 'department', 'approver', 'creator']);

        // Filter berdasarkan karyawan
        if (!empty($filter['employee_id'])) {
            $history->where('employee_id', $filter['employee_id']);
        }

        // Filter berdasarkan nama karyawan
        if (!empty($filter['employee_name'])) {
            $history->whereHas('employee.user', function ($query) use ($filter) {
                $query->where('name', 'LIKE', '%' . $filter['employee_name'] . '%');
            });
        }

        // Filter berdasarkan departemen
        if (!empty($filter['department_id'])) {
            $history->where('department_id', $filter['department_id']);
        }

        // Filter berdasarkan posisi
        if (!empty($filter['position_id'])) {
            $history->where('position_id', $filter['position_id']);
        }

        // Filter berdasarkan posisi aktif
        if (isset($filter['is_current'])) {
            $history->where('is_current', (bool) $filter['is_current']);
        }

        // Filter berdasarkan tanggal mulai
        if (!empty($filter['start_date_from'])) {
            $history->whereDate('start_date', '>=', $filter['start_date_from']);
        }

        if (!empty($filter['start_date_to'])) {
            $history->whereDate('start_date', '<=', $filter['start_date_to']);
        }

        // Pengurutan data
        $sort = $sort ?: 'start_date,desc';
        $sortArray = explode(',', $sort);
        $history->orderBy($sortArray[0], $sortArray[1]);

        return $history->paginate($itemPerPage);
    }

    /**
     * Mengambil riwayat posisi berdasarkan ID
     *
     * @param string $id
     * @return object
     */
    public function getById(string $id): object
    {
        return $this->with([
            'employee.user',
            'position',
            'department',
            'approver',
            'creator'
        ])->findOrFail($id);
    }

    /**
     * Menyimpan riwayat posisi baru
     *
     * @param array $payload
     * @return object
     */
    public function store(array $payload): object
    {
        // Nonaktifkan posisi sebelumnya jika ini adalah posisi aktif baru
        if (isset($payload['is_current']) && $payload['is_current']) {
            self::deactivatePreviousPosition($payload['employee_id']);
        }

        return $this->create($payload);
    }

    /**
     * Memperbarui riwayat posisi
     *
     * @param array $payload
     * @param string $id
     * @return object
     */
    public function edit(array $payload, string $id): object
    {
        $positionHistory = $this->findOrFail($id);

        // Jika status is_current diubah menjadi true, nonaktifkan posisi aktif lainnya
        if (isset($payload['is_current']) && $payload['is_current'] && !$positionHistory->is_current) {
            self::deactivatePreviousPosition($positionHistory->employee_id);
        }

        $positionHistory->update($payload);
        return $positionHistory;
    }

    /**
     * Menghapus riwayat posisi
     *
     * @param string $id
     * @return bool
     */
    public function drop(string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Method untuk mendapatkan riwayat posisi berdasarkan employee_id
     *
     * @param string $employeeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHistoryByEmployeeId(string $employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Method untuk mendapatkan posisi terkini berdasarkan employee_id
     *
     * @param string $employeeId
     * @return self|null
     */
    public static function getCurrentPositionByEmployeeId(string $employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->where('is_current', true)
            ->first();
    }

    /**
     * Method untuk menonaktifkan posisi sebelumnya
     *
     * @param string $employeeId
     * @return bool
     */
    public static function deactivatePreviousPosition(string $employeeId)
    {
        return self::where('employee_id', $employeeId)
            ->where('is_current', true)
            ->update([
                'is_current' => false,
                'end_date' => now()
            ]);
    }

    /**
     * Method untuk memindahkan karyawan ke posisi baru
     *
     * @param array $data
     * @return self
     */
    public static function createNewPosition(array $data)
    {
        // Pastikan untuk menonaktifkan posisi sebelumnya terlebih dahulu
        self::deactivatePreviousPosition($data['employee_id']);

        // Buat record posisi baru
        return self::create([
            'employee_id' => $data['employee_id'],
            'position_id' => $data['position_id'],
            'department_id' => $data['department_id'],
            'start_date' => $data['start_date'] ?? now(),
            'is_current' => true,
            'salary' => $data['salary'] ?? null,
            'notes' => $data['notes'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->id()
        ]);
    }
}
