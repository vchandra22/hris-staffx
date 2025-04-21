<?php

namespace App\Models;

use App\Http\Traits\Uuid;
use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes, Uuid;

    protected $table = 'm_payrolls';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'employee_id',
        'month',
        'year',
        'base_salary',
        'overtime_hours',
        'overtime_rate',
        'overtime_pay',
        'deductions',
        'net_salary'
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'base_salary' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2'
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $payroll = $this->query()->with('employee');

        // Filter by employee
        if (!empty($filter['employee_id'])) {
            $payroll->where('employee_id', $filter['employee_id']);
        }

        // Filter by month
        if (!empty($filter['month'])) {
            $payroll->where('month', $filter['month']);
        }

        // Filter by year
        if (!empty($filter['year'])) {
            $payroll->where('year', $filter['year']);
        }

        // Filter by salary range
        if (!empty($filter['salary_range'])) {
            $payroll->whereBetween('net_salary', [
                $filter['salary_range']['min'] ?? 0,
                $filter['salary_range']['max'] ?? PHP_FLOAT_MAX
            ]);
        }

        // Filter by overtime
        if (isset($filter['has_overtime'])) {
            if ($filter['has_overtime']) {
                $payroll->where('overtime_hours', '>', 0);
            } else {
                $payroll->where('overtime_hours', 0);
            }
        }

        // Sorting
        $sort = $sort ?: 'year,desc,month,desc';
        $sortParts = explode(',', $sort);
        for ($i = 0; $i < count($sortParts); $i += 2) {
            if (isset($sortParts[$i + 1])) {
                $payroll->orderBy($sortParts[$i], $sortParts[$i + 1]);
            }
        }

        return $payroll->paginate($itemPerPage);
    }

    public function getById(string $id): object
    {
        return $this->with('employee')->findOrFail($id);
    }

    private function calculateOvertimePay(float $hours, float $rate): float
    {
        return round($hours * $rate, 2);
    }

    private function calculateNetSalary(float $baseSalary, float $overtimePay, float $deductions): float
    {
        return round($baseSalary + $overtimePay - $deductions, 2);
    }

    public function store(array $payload): object
    {
        // Calculate overtime pay
        $payload['overtime_pay'] = $this->calculateOvertimePay(
            $payload['overtime_hours'] ?? 0,
            $payload['overtime_rate'] ?? 0
        );

        // Calculate net salary
        $payload['net_salary'] = $this->calculateNetSalary(
            $payload['base_salary'],
            $payload['overtime_pay'],
            $payload['deductions'] ?? 0
        );

        return $this->create($payload);
    }

    public function edit(array $payload, string $id): object
    {
        $current = $this->find($id);

        // Calculate overtime pay if hours or rate changed
        if (isset($payload['overtime_hours']) || isset($payload['overtime_rate'])) {
            $payload['overtime_pay'] = $this->calculateOvertimePay(
                $payload['overtime_hours'] ?? $current->overtime_hours,
                $payload['overtime_rate'] ?? $current->overtime_rate
            );
        }

        // Calculate net salary if any component changed
        if (isset($payload['base_salary']) || isset($payload['overtime_pay']) || isset($payload['deductions'])) {
            $payload['net_salary'] = $this->calculateNetSalary(
                $payload['base_salary'] ?? $current->base_salary,
                $payload['overtime_pay'] ?? $current->overtime_pay,
                $payload['deductions'] ?? $current->deductions
            );
        }

        return tap($current)->update($payload);
    }

    public function drop(string $id): bool
    {
        return $this->find($id)->delete();
    }

    // Additional helper methods for reporting and analysis
    public function getTotalByYear(int $year): array
    {
        return $this->where('year', $year)
            ->selectRaw('
                SUM(base_salary) as total_base_salary,
                SUM(overtime_pay) as total_overtime,
                SUM(deductions) as total_deductions,
                SUM(net_salary) as total_net_salary,
                COUNT(*) as total_payrolls
            ')
            ->first()
            ->toArray();
    }

    public function getTotalByMonth(int $year, int $month): array
    {
        return $this->where('year', $year)
            ->where('month', $month)
            ->selectRaw('
                SUM(base_salary) as total_base_salary,
                SUM(overtime_pay) as total_overtime,
                SUM(deductions) as total_deductions,
                SUM(net_salary) as total_net_salary,
                COUNT(*) as total_payrolls
            ')
            ->first()
            ->toArray();
    }

    public function getMonthlyReport(int $year, int $month): array
    {
        return $this->with('employee')
            ->where('year', $year)
            ->where('month', $month)
            ->select([
                'employee_id',
                'base_salary',
                'overtime_hours',
                'overtime_rate',
                'overtime_pay',
                'deductions',
                'net_salary'
            ])
            ->get()
            ->toArray();
    }

    public function getYearlyReport(int $year): array
    {
        return $this->selectRaw('
            month,
            COUNT(*) as total_employees,
            SUM(base_salary) as total_base_salary,
            SUM(overtime_hours) as total_overtime_hours,
            SUM(overtime_pay) as total_overtime_pay,
            SUM(deductions) as total_deductions,
            SUM(net_salary) as total_net_salary,
            AVG(net_salary) as average_salary,
            MAX(net_salary) as highest_salary,
            MIN(net_salary) as lowest_salary
        ')
            ->where('year', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function getEmployeeYearlyPayroll(string $employeeId, int $year): array
    {
        return $this->where('employee_id', $employeeId)
            ->where('year', $year)
            ->orderBy('month')
            ->get()
            ->toArray();
    }

    public function checkExistingPayroll(string $employeeId, int $year, int $month): bool
    {
        return $this->where('employee_id', $employeeId)
            ->where('year', $year)
            ->where('month', $month)
            ->exists();
    }

    public function getOvertimeStats(int $year, int $month = null): array
    {
        $query = $this->where('year', $year)
            ->where('overtime_hours', '>', 0);

        if ($month) {
            $query->where('month', $month);
        }

        return $query->selectRaw('
            COUNT(*) as total_employees_with_overtime,
            SUM(overtime_hours) as total_overtime_hours,
            SUM(overtime_pay) as total_overtime_pay,
            AVG(overtime_hours) as average_overtime_hours,
            AVG(overtime_pay) as average_overtime_pay,
            MAX(overtime_pay) as highest_overtime_pay
        ')
            ->first()
            ->toArray();
    }
}
