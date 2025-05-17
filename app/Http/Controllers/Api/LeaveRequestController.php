<?php

namespace App\Http\Controllers\Api;

use App\Helpers\LeaveRequest\LeaveRequestHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeaveRequest\LeaveRequestCreateRequest;
use App\Http\Requests\LeaveRequest\LeaveRequestUpdateRequest;
use App\Http\Resources\LeaveRequest\LeaveRequestCollection;
use App\Http\Resources\LeaveRequest\LeaveRequestResource;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    private $leaveRequest;

    public function __construct()
    {
        $this->leaveRequest = new LeaveRequestHelper();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'search' => $request->search ?? '',
            'employee_id' => $request->employee_id ?? '',
            'status' => $request->status ?? '',
            'start_date' => $request->start_date ?? '',
            'end_date' => $request->end_date ?? '',
            'date_range' => $request->date_range ?? '',
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $leaveRequests = $this->leaveRequest->getAll($filter);

        return response()->success(new LeaveRequestCollection($leaveRequests));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LeaveRequestCreateRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'start_date',
            'end_date',
            'reason',
            'leave_type_id',
            'total_days',
            'half_day',
            'half_day_time',
            'attachment_path',
            'attachment_type'
        ]);

        try {
            $leaveRequest = $this->leaveRequest->store($payload);

            return response()->success(
                new LeaveRequestResource($leaveRequest),
                'Leave request created successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to create leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $leaveRequest = $this->leaveRequest->getById($id);

            if (!$leaveRequest) {
                return response()->failed(['Leave request not found'], 404);
            }

            return response()->success(new LeaveRequestResource($leaveRequest));
        } catch (\Exception $e) {
            return response()->failed(['Failed to retrieve leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LeaveRequestUpdateRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'start_date',
            'end_date',
            'reason',
            'leave_type_id',
            'total_days',
            'half_day',
            'half_day_time',
            'status',
            'attachment_path',
            'attachment_type'
        ]);

        try {
            $leaveRequest = $this->leaveRequest->update($id, $payload);

            return response()->success(
                new LeaveRequestResource($leaveRequest),
                'Leave request updated successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to update leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $result = $this->leaveRequest->delete($id);

            if (!$result) {
                return response()->failed(['Leave request not found']);
            }

            return response()->success(null, 'Leave request deleted successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to delete leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $result = $this->leaveRequest->restore($id);

            if (!$result) {
                return response()->failed(['Leave request not found']);
            }

            return response()->success(null, 'Leave request restored successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to restore leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Approve a leave request
     */
    public function approve(Request $request, string $id)
    {
        try {
            if (empty($request->approved_by)) {
                return response()->failed(['Approved by is required']);
            }

            $leaveRequest = $this->leaveRequest->approve($id, $request->approved_by);

            return response()->success(
                new LeaveRequestResource($leaveRequest),
                'Leave request approved successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to approve leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject a leave request
     */
    public function reject(Request $request, string $id)
    {
        try {
            if (empty($request->rejection_reason)) {
                return response()->failed(['Rejection reason is required']);
            }

            $leaveRequest = $this->leaveRequest->reject($id, $request->rejection_reason);

            return response()->success(
                new LeaveRequestResource($leaveRequest),
                'Leave request rejected successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to reject leave request: ' . $e->getMessage()]);
        }
    }
}
