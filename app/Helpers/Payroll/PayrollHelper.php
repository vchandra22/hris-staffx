<?php

namespace App\Helpers\Payroll;

use App\Helpers\Venturo;
use App\Models\PayrollModel;

class PayrollHelper extends Venturo
{
    private $payroll;

    public function __construct()
    {
        $this->payroll = new PayrollModel();
    }

    /**
     * Mengambil daftar payroll dengan paginasi
     *
     * @param array $filter
     * @return object
     */
    public function getAll(array $filter = []): object
    {
        $payrolls = $this->payroll->query()
            ->with(['employee.user', 'employee.department', 'employee.position'])
            ->when(!empty($filter['employee_id']), function ($query) use ($filter) {
                return $query->where('employee_id', $filter['employee_id']);
            })
            ->when(!empty($filter['month']), function ($query) use ($filter) {
                return $query->where('month', $filter['month']);
            })
            ->when(!empty($filter['year']), function ($query) use ($filter) {
                return $query->where('year', $filter['year']);
            })
            ->when(!empty($filter['salary_range']), function ($query) use ($filter) {
                return $query->whereBetween('net_salary', [
                    $filter['salary_range']['min'] ?? 0,
                    $filter['salary_range']['max'] ?? PHP_FLOAT_MAX
                ]);
            })
            ->when(isset($filter['has_overtime']), function ($query) use ($filter) {
                return $filter['has_overtime']
                    ? $query->where('overtime_hours', '>', 0)
                    : $query->where('overtime_hours', 0);
            })
            ->orderBy($filter['sort_by'] ?? 'year', $filter['sort_desc'] ?? 'desc')
            ->when($filter['sort_by'] === 'year', function ($query) use ($filter) {
                return $query->orderBy('month', $filter['sort_desc'] ?? 'desc');
            });

        return $payrolls->paginate($filter['per_page'] ?? 10);
    }

    /**
     * Menyimpan data payroll baru
     *
     * @param array $payload
     * @return PayrollModel
     */
    public function store(array $payload): PayrollModel
    {
        try {
            $this->beginTransaction();

            if ($this->isPayrollExists($payload['employee_id'], $payload['year'], $payload['month'])) {
                throw new \Exception('Payroll untuk karyawan ini pada periode tersebut sudah ada');
            }

            $payroll = $this->payroll->store($payload);

            $this->commitTransaction();
            return $payroll->load('employee.user', 'employee.department', 'employee.position');
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengambil detail payroll berdasarkan ID
     *
     * @param string $id
     * @return PayrollModel
     */
    public function getById(string $id): PayrollModel
    {
        return $this->payroll->with(['employee.user', 'employee.department', 'employee.position'])->findOrFail($id);
    }

    /**
     * Memperbarui data payroll
     *
     * @param string $id
     * @param array $payload
     * @return PayrollModel
     */
    public function update(string $id, array $payload): PayrollModel
    {
        try {
            $this->beginTransaction();

            $payroll = $this->payroll->findOrFail($id);

            if (isset($payload['employee_id']) || isset($payload['month']) || isset($payload['year'])) {
                if ($this->isPayrollExists(
                    $payload['employee_id'] ?? $payroll->employee_id,
                    $payload['year'] ?? $payroll->year,
                    $payload['month'] ?? $payroll->month,
                    $id
                )) {
                    throw new \Exception('Payroll untuk karyawan ini pada periode tersebut sudah ada');
                }
            }

            $payroll = $this->payroll->edit($payload, $id);

            $this->commitTransaction();
            return $payroll->fresh(['employee.user', 'employee.department', 'employee.position']);
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Menghapus data payroll (soft delete)
     *
     * @param string $id
     * @return bool
     */
    public function delete(string $id): bool
    {
        try {
            $this->beginTransaction();

            $payroll = $this->payroll->findOrFail($id);
            $result = $payroll->delete();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Memulihkan data payroll yang telah dihapus
     *
     * @param string $id
     * @return bool
     */
    public function restore(string $id): bool
    {
        try {
            $this->beginTransaction();

            $payroll = $this->payroll->withTrashed()->findOrFail($id);
            $result = $payroll->restore();

            $this->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $this->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Mengecek apakah payroll sudah ada untuk karyawan pada periode tertentu
     *
     * @param string $employeeId
     * @param int $year
     * @param int $month
     * @param string|null $excludeId
     * @return bool
     */
    private function isPayrollExists(string $employeeId, int $year, int $month, ?string $excludeId = null): bool
    {
        return $this->payroll->where('employee_id', $employeeId)
            ->where('year', $year)
            ->where('month', $month)
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->exists();
    }
}
