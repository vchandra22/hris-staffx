<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Attendance\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\AttendanceCreateRequest;
use App\Http\Requests\Attendance\AttendanceUpdateRequest;
use App\Http\Resources\Attendance\AttendanceCollection;
use App\Http\Resources\Attendance\AttendanceResource;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    private $attendance;

    public function __construct()
    {
        $this->attendance = new AttendanceHelper();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'search' => $request->search ?? '',
            'employee_id' => $request->employee_id ?? '',
            'date' => $request->date ?? '',
            'start_date' => $request->start_date ?? '',
            'end_date' => $request->end_date ?? '',
            'status' => $request->status ?? '',
            'sort_by' => $request->sort_by ?? 'date',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $attendances = $this->attendance->getAll($filter);

        return response()->success(new AttendanceCollection($attendances));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AttendanceCreateRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'date',
            'check_in',
            'check_out',
            'status',
            'late_minutes',
            'early_leave_minutes',
            'overtime_minutes',
            'lat',
            'lng',
            'notes'
        ]);

        try {
            $attendance = $this->attendance->store($payload);

            return response()->success(
                new AttendanceResource($attendance),
                'Attendance record created successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to create attendance record: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attendance = $this->attendance->getById($id);

            if (!$attendance) {
                return response()->failed(['Attendance record not found'], 404);
            }

            return response()->success(new AttendanceResource($attendance));
        } catch (\Exception $e) {
            return response()->failed(['Failed to retrieve attendance record: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AttendanceUpdateRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'date',
            'check_in',
            'check_out',
            'status',
            'late_minutes',
            'early_leave_minutes',
            'overtime_minutes',
            'lat',
            'lng',
            'notes'
        ]);

        try {
            $attendance = $this->attendance->update($id, $payload);

            return response()->success(
                new AttendanceResource($attendance),
                'Attendance record updated successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to update attendance record: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $result = $this->attendance->delete($id);

            if (!$result) {
                return response()->failed(['Attendance record not found']);
            }

            return response()->success(null, 'Attendance record deleted successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to delete attendance record: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $result = $this->attendance->restore($id);

            if (!$result) {
                return response()->failed(['Attendance record not found']);
            }

            return response()->success(null, 'Attendance record restored successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to restore attendance record: ' . $e->getMessage()]);
        }
    }
}
