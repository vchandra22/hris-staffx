<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Payroll\PayrollHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollRequest;
use App\Http\Resources\Payroll\PayrollCollection;
use App\Http\Resources\Payroll\PayrollResource;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    private $payroll;

    public function __construct()
    {
        $this->payroll = new PayrollHelper();
    }

    /**
     * Display a listing of payrolls
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filter = [
            'employee_id' => $request->employee_id,
            'month' => $request->month,
            'year' => $request->year,
            'salary_range' => [
                'min' => $request->min_salary,
                'max' => $request->max_salary
            ],
            'has_overtime' => $request->has_overtime,
            'sort_by' => $request->sort_by ?? 'year',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $payrolls = $this->payroll->getAll($filter);

        return response()->success(new PayrollCollection($payrolls));
    }

    /**
     * Store a newly created payroll
     *
     * @param PayrollRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(PayrollRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'month',
            'year',
            'base_salary',
            'overtime_hours',
            'overtime_rate',
            'deductions'
        ]);

        try {
            $payroll = $this->payroll->store($payload);

            return response()->success(new PayrollResource($payroll), 'Payroll berhasil ditambahkan');
        } catch (\Exception $e) {
            return response()->failed(['Gagal menambahkan payroll: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified payroll
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id)
    {
        try {
            $payroll = $this->payroll->getById($id);

            if (!$payroll) {
                return response()->failed(['Data payroll tidak ditemukan'], 404);
            }

            return response()->success(new PayrollResource($payroll));
        } catch (\Exception $e) {
            return response()->failed(['Gagal menampilkan payroll: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified payroll
     *
     * @param PayrollRequest $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PayrollRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        try {
            $payload = $request->only([
                'employee_id',
                'month',
                'year',
                'base_salary',
                'overtime_hours',
                'overtime_rate',
                'deductions'
            ]);

            $payroll = $this->payroll->update($id, $payload);

            return response()->success(new PayrollResource($payroll), 'Payroll berhasil diperbarui');
        } catch (\Exception $e) {
            return response()->failed([
                'Gagal memperbarui payroll: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified payroll
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(string $id)
    {
        try {
            $payroll = $this->payroll->delete($id);

            if (!$payroll) {
                return response()->failed(['Data payroll tidak ditemukan']);
            }

            return response()->success(null, 'Payroll berhasil dihapus');
        } catch (\Exception $e) {
            return response()->failed(['Gagal menghapus payroll: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore the specified payroll
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(string $id)
    {
        try {
            $result = $this->payroll->restore($id);

            if (!$result) {
                return response()->failed(['Data payroll tidak ditemukan']);
            }

            return response()->success(null, 'Payroll berhasil dipulihkan');
        } catch (\Exception $e) {
            return response()->failed(['Gagal memulihkan payroll: ' . $e->getMessage()]);
        }
    }
}
