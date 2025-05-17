<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\LeaveType\LeaveTypeHelper;
use App\Http\Requests\LeaveType\LeaveTypeCreateRequest;
use App\Http\Requests\LeaveType\LeaveTypeUpdateRequest;
use App\Http\Resources\LeaveType\LeaveTypeCollection;
use App\Http\Resources\LeaveType\LeaveTypeResource;

class LeaveTypeController extends Controller
{
    private $leaveType;

    public function __construct()
    {
        $this->leaveType = new LeaveTypeHelper();
    }

    public function index(Request $request)
    {
        $filter = [
            'search' => $request->search ?? '',
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $leaveTypes = $this->leaveType->getAll($filter);

        return response()->success(new LeaveTypeCollection($leaveTypes));
    }

    public function store(LeaveTypeCreateRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'name',
            'description',
            'annual_allowance',
            'requires_approval',
            'minimum_notice_days',
            'maximum_days_per_request',
            'carried_forward',
            'carry_forward_max_days',
            'requires_attachment',
            'half_day_allowed'
        ]);

        try {
            $leaveType = $this->leaveType->store($payload);

            return response()->success(
                new LeaveTypeResource($leaveType),
                'Leave type created successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to create leave type: ' . $e->getMessage()]);
        }
    }

    public function show(string $id)
    {
        try {
            $leaveType = $this->leaveType->getById($id);

            if (!$leaveType) {
                return response()->failed(['Leave type not found'], 404);
            }

            return response()->success(new LeaveTypeResource($leaveType));
        } catch (\Exception $e) {
            return response()->failed(['Failed to retrieve leave type: ' . $e->getMessage()]);
        }
    }

    public function update(LeaveTypeUpdateRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'name',
            'description',
            'annual_allowance',
            'requires_approval',
            'minimum_notice_days',
            'maximum_days_per_request',
            'carried_forward',
            'carry_forward_max_days',
            'requires_attachment',
            'half_day_allowed'
        ]);

        try {
            $leaveType = $this->leaveType->update($id, $payload);

            return response()->success(
                new LeaveTypeResource($leaveType),
                'Leave type updated successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to update leave type: ' . $e->getMessage()]);
        }
    }

    public function destroy(string $id)
    {
        try {
            $result = $this->leaveType->delete($id);

            if (!$result) {
                return response()->failed(['Leave type not found']);
            }

            return response()->success(null, 'Leave type deleted successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to delete leave type: ' . $e->getMessage()]);
        }
    }

    public function restore(string $id)
    {
        try {
            $result = $this->leaveType->restore($id);

            if (!$result) {
                return response()->failed(['Leave type not found']);
            }

            return response()->success(null, 'Leave type restored successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to restore leave type: ' . $e->getMessage()]);
        }
    }
}
