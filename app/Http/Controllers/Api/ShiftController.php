<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Shift\ShiftHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shift\ShiftCreateRequest;
use App\Http\Requests\Shift\ShiftUpdateRequest;
use App\Http\Resources\Shift\ShiftCollection;
use App\Http\Resources\Shift\ShiftResource;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    private $shift;

    public function __construct()
    {
        $this->shift = new ShiftHelper();
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
            'sort_by' => $request->sort_by ?? 'date',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $shifts = $this->shift->getAll($filter);

        return response()->success(new ShiftCollection($shifts));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ShiftCreateRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'date',
            'start_time',
            'end_time',
        ]);

        try {
            $shift = $this->shift->store($payload);

            return response()->success(
                new ShiftResource($shift),
                'Shift record created successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to create shift record: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $shift = $this->shift->getById($id);

            if (!$shift) {
                return response()->failed(['Shift record not found'], 404);
            }

            return response()->success(new ShiftResource($shift));
        } catch (\Exception $e) {
            return response()->failed(['Failed to retrieve shift record: ' . $e->getMessage()]);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ShiftUpdateRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only([
            'employee_id',
            'date',
            'start_time',
            'end_time',
        ]);

        try {
            $shift = $this->shift->update($id, $payload);

            return response()->success(
                new ShiftResource($shift),
                'Shift record updated successfully'
            );
        } catch (\Exception $e) {
            return response()->failed(['Failed to update shift record: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $result = $this->shift->delete($id);

            if (!$result) {
                return response()->failed(['Shift record not found']);
            }

            return response()->success(null, 'Shift record deleted successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to delete shift record: ' . $e->getMessage()]);
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        try {
            $result = $this->shift->restore($id);

            if (!$result) {
                return response()->failed(['Shift record not found']);
            }

            return response()->success(null, 'Shift record restored successfully');
        } catch (\Exception $e) {
            return response()->failed(['Failed to restore shift record: ' . $e->getMessage()]);
        }
    }
}
