<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Position\PositionHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Position\PositionRequest;
use App\Http\Resources\Position\PositionCollection;
use App\Http\Resources\Position\PositionResource;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    private $position;

    public function __construct()
    {
        $this->position = new PositionHelper();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $filter = [
            'search' => $request->search ?? '',
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_desc' => $request->sort_desc ?? 'desc',
            'per_page' => $request->per_page ?? 10
        ];

        $positions = $this->position->getAll($filter);

        return response()->success(new PositionCollection($positions));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only(['name', 'description']);

        if ($this->position->isNameExists($payload['name'])) {
            return response()->failed(['Nama position sudah digunakan']);
        }

        $position = $this->position->store($payload);

        if (!$position) {
            return response()->failed(['Gagal menambahkan position']);
        }

        return response()->success(new PositionResource($position), 'Position berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $position = $this->position->getById($id);

        if (!$position) {
            return response()->failed(['Data position tidak ditemukan'], 404);
        }

        return response()->success(new PositionResource($position));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionRequest $request, string $id)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only(['name', 'description']);

        $position = $this->position->update($id, $payload);

        if (!$position) {
            return response()->failed(['Gagal mengubah position']);
        }

        return response()->success(new PositionResource($position), 'Position berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $position = $this->position->delete($id);

        if (!$position) {
            return response()->failed(['Data position tidak ditemukan']);
        }

        return response()->success(null, 'Position berhasil dihapus');
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore(string $id)
    {
        $position = $this->position->restore($id);

        if (!$position) {
            return response()->failed(['Data position tidak ditemukan']);
        }

        return response()->success(null, 'Position berhasil dipulihkan');
    }
}
