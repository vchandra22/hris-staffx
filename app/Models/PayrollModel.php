<?php

namespace App\Models;

use App\Repository\CrudInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollModel extends Model implements CrudInterface
{
    use HasFactory, SoftDeletes;

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
        'deductions',
        'net_salary'
    ];

    protected $casts = [
        'month' => 'integer',
        'year' => 'integer',
        'base_salary' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(EmployeeModel::class, 'employee_id', 'id');
    }

    public function getAll(array $filter, int $page, int $itemPerPage, string $sort)
    {
        $payroll = $this->query();

        if (!empty($filter['employee_id'])) {
            $payroll->where('employee_id', $filter['employee_id']);
        }

        if (!empty($filter['month'])) {
            $payroll->where('month', $filter['month']);
        }

        if (!empty($filter['year'])) {
            $payroll->where('year', $filter['year']);
        }

        if (!empty($filter['salary_range'])) {
            $payroll->whereBetween('net_salary', [
                $filter['salary_range']['min'] ?? 0,
                $filter['salary_range']['max'] ?? PHP_FLOAT_MAX
            ]);
        }

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
        return $this->findOrFail($id);
    }

    public function store(array $payload): object
    {
        // Calculate net salary if not provided
        if (!isset($payload['net_salary'])) {
            $payload['net_salary'] = $payload['base_salary'] - ($payload['deductions'] ?? 0);
        }
        return $this->create($payload);
    }

    public function edit(array $payload, string $id): object
    {
        // Recalculate net salary if base_salary or deductions changed
        if (isset($payload['base_salary']) || isset($payload['deductions'])) {
            $current = $this->find($id);
            $payload['net_salary'] =
                ($payload['base_salary'] ?? $current->base_salary) -
                ($payload['deductions'] ?? $current->deductions);
        }
        return $this->find($id)->update($payload);
    }

    public function drop(string $id): bool
    {
        return $this->find($id)->delete();
    }

    // Additional helper methods
    public function calculateNetSalary(): float
    {
        return $this->base_salary - $this->deductions;
    }

    public function getTotalByYear(int $year): float
    {
        return $this->where('year', $year)
                    ->sum('net_salary');
    }

    public function getTotalByMonth(int $year, int $month): float
    {
        return $this->where('year', $year)
                    ->where('month', $month)
                    ->sum('net_salary');
    }

    public function getYearlyReport(int $year): array
    {
        return $this->selectRaw('
            month,
            COUNT(*) as total_employees,
            SUM(base_salary) as total_base_salary,
            SUM(deductions) as total_deductions,
            SUM(net_salary) as total_net_salary
        ')
        ->where('year', $year)
        ->groupBy('month')
        ->orderBy('month')
        ->get()
        ->toArray();
    }
}
