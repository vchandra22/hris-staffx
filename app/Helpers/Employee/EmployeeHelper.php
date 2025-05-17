<?php

namespace App\Helpers\Employee;

use App\Helpers\User\UserHelper;
use App\Helpers\Venturo;
use App\Models\EmployeeModel;
use App\Models\EmployeePositionHistoryModel;
use Illuminate\Support\Facades\Hash;

class EmployeeHelper extends Venturo
{
    private $employee;
    private $user;
    private $positionHistory;

    public function __construct()
    {
        $this->employee = new EmployeeModel();
        $this->user = new UserHelper();
        $this->positionHistory = new EmployeePositionHistoryModel();
    }

    /**
     * Mengambil daftar employee dengan paginasi
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $employees = $this->employee->query()
            ->with(['user', 'department', 'position', 'currentPosition'])
            ->when(!empty($filter['search']), function ($query) use ($filter) {
                return $query->whereHas('user', function ($userQuery) use ($filter) {
                    $userQuery->where('name', 'like', '%' . $filter['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filter['search'] . '%');
                });
            })
            ->when(!empty($filter['department_id']), function ($query) use ($filter) {
                return $query->whereHas('currentPosition', function ($positionQuery) use ($filter) {
                    $positionQuery->where('department_id', $filter['department_id']);
                });
            })
            ->when(!empty($filter['position_id']), function ($query) use ($filter) {
                return $query->whereHas('currentPosition', function ($positionQuery) use ($filter) {
                    $positionQuery->where('position_id', $filter['position_id']);
                });
            })->orderBy($filter['sort_by'] ?? 'created_at', $filter['sort_desc'] ?? 'desc');

        return $employees->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Menyimpan data employee dan user baru
     *
     * @param array $payload
     * @return EmployeeModel
     */
    public function store(array $payload): EmployeeModel
    {
        try {
            $this->beginTransaction();

            // Prepare user data
            $userData = [
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => Hash::make($payload['password'] ?? 'password'), // Default password jika tidak diset
                'phone_number' => $payload['phone_number'] ?? null,
                'm_user_roles_id' => $payload['role_id'] ?? config('constants.roles.staff'),
                'photo' => $payload['photo'] ?? null
            ];

            // Create user
            $userResult = $this->user->create($userData);

            if (!$userResult['status']) {
                throw new \Exception($userResult['error']);
            }

            // Mengambil data user dari result
            $userData = $userResult['data'];

            // Prepare employee data
            $employeeData = [
                'user_id' => $userData['id'],
                'birth_place' => $payload['birth_place'] ?? null,
                'birth_date' => $payload['birth_date'] ?? null,
                'address' => $payload['address'] ?? null,
                'hire_date' => $payload['hire_date'],
            ];

            // Create employee
            $employee = $this->employee->store($employeeData);

            // Tambahkan data posisi awal jika disertakan
            if (isset($payload['position_id']) && isset($payload['department_id'])) {
                $this->positionHistory->store([
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

            $this->commitTransaction();

            return $employee->load(['user', 'currentPosition.department', 'currentPosition.position']);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengambil detail employee berdasarkan ID
     *
     * @param string $id
     * @return EmployeeModel
     */
    public function getById(string $id): EmployeeModel
    {
        return $this->employee->with(['user', 'currentPosition.department', 'currentPosition.position'])->findOrFail($id);
    }

    /**
     * Memperbarui data employee
     *
     * @param string $id
     * @param array $payload
     * @return EmployeeModel
     */
    public function update(string $id, array $payload): EmployeeModel
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->with(['user', 'currentPosition'])->findOrFail($id);

            // Update user data if provided
            if (!empty(array_intersect(array_keys($payload), ['name', 'email', 'password', 'photo', 'role_id', 'phone_number']))) {
                $userData = array_filter([
                    'name' => $payload['name'] ?? null,
                    'email' => $payload['email'] ?? null,
                    'password' => isset($payload['password']) ? Hash::make($payload['password']) : null,
                    'phone_number' => $payload['phone_number'] ?? null,
                    'm_user_roles_id' => $payload['role_id'] ?? null,
                    'photo' => $payload['photo'] ?? null
                ]);

                $this->user->update($userData, $employee->user->id);
            }

            // Update employee data
            $employeeData = array_filter([
                'birth_place' => $payload['birth_place'] ?? null,
                'birth_date' => $payload['birth_date'] ?? null,
                'address' => $payload['address'] ?? null,
                'hire_date' => $payload['hire_date'] ?? null,
            ]);

            // Update employee basic info
            $employee->update($employeeData);

            // Check if position data needs to be updated
            $positionChanged = false;
            $currentPosition = $employee->currentPosition;

            if (isset($payload['position_id']) && $currentPosition && $payload['position_id'] != $currentPosition->position_id) {
                $positionChanged = true;
            }

            if (isset($payload['department_id']) && $currentPosition && $payload['department_id'] != $currentPosition->department_id) {
                $positionChanged = true;
            }

            if (isset($payload['salary']) && $currentPosition && $payload['salary'] != $currentPosition->salary) {
                $positionChanged = true;
            }

            // Jika ada perubahan posisi, buat riwayat baru
            if ($positionChanged) {
                $this->positionHistory->store([
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

            $this->commitTransaction();

            return $this->getById($id);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Menghapus data employee (soft delete)
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->with('user')->findOrFail($id);

            if ($employee->user) {
                $this->user->delete($employee->user->id);
            }

            $result = $employee->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Memulihkan data employee yang telah dihapus
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $employee = $this->employee->withTrashed()
                ->with(['user' => function ($query) {
                    $query->withTrashed();
                }])
                ->findOrFail($id);

            if ($employee->user) {
                $this->user->restore($employee->user->id);
            }

            $result = $employee->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }
}
