<?php
namespace App\Http\Controllers\Api;

use App\Helpers\Role\RoleHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\RoleRequest;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private $role;

    public function __construct()
    {
        $this->role = new RoleHelper();
    }

    /**
     * Delete data role
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     * @param mixed $id
     */
    public function destroy($id)
    {
        $role = $this->role->delete($id);

        if (!$role) {
            return response()->failed(['Mohon maaf role tidak ditemukan']);
        }

        return response()->success($role, 'Role berhasil dihapus');
    }

    /**
     * Mengambil data role dilengkapi dengan pagination
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     */
    public function index(Request $request)
    {
        $filter = [
            'name' => $request->name ?? '',
        ];
        $roles = $this->role->getAll($filter, $request->per_page ?? 25, $request->sort ?? '');

        return response()->success(new RoleCollection($roles['data']));
    }

    /**
     * Menampilkan role secara spesifik dari tabel user_role
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     * @param mixed $id
     */
    public function show($id)
    {
        $role = $this->role->getById($id);

        if (!($role['status'])) {
            return response()->failed(['Data role tidak ditemukan'], 404);
        }

        return response()->success(new RoleResource($role['data']));
    }

    /**
     * Membuat data role baru & disimpan ke tabel user_role
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     */
    public function store(RoleRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only(['name', 'access']);
        $role = $this->role->create($payload);

        if (!$role['status']) {
            return response()->failed($role['error']);
        }

        return response()->success(new RoleResource($role['data']), 'Role berhasil ditambahkan');
    }

    /**
     * Mengubah data role di tabel user_role
     *
     * @author Wahyu Agung <wahyuagung26@email.com>
     */
    public function update(RoleRequest $request)
    {
        if (isset($request->validator) && $request->validator->fails()) {
            return response()->failed($request->validator->errors());
        }

        $payload = $request->only(['name', 'access', 'id']);
        $role = $this->role->update($payload, $payload['id'] ?? 0);

        if (!$role['status']) {
            return response()->failed($role['error']);
        }

        return response()->success(new RoleResource($role['data']), 'Role berhasil diubah');
    }
}
