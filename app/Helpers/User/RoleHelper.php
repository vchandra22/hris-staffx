<?php

namespace App\Helpers\User;

use App\Helpers\Venturo;
use App\Models\RoleModel;
use Illuminate\Support\Facades\Hash;
use Throwable;

class RoleHelper extends Venturo
{
    private $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function getAll(array $filter, int $page = 1 , int $itemPerPage = 0, string $sort = '')
    {
        $role = $this->roleModel->getAll($filter, $page, $itemPerPage, $sort);

        return [
            'status' => true,
            'data' => $role
        ];
    }

    public function getById(string $id): array
    {
        $role = $this->roleModel->getById($id);
        if (empty($role)) {
            return [
                'status' => false,
                'data' => null
            ];
        }

        return [
            'status' => true,
            'data' => $role
        ];
    }

    public function create(array $payload): array
    {
        try {

            $role = $this->roleModel->store($payload);

            return [
                'status' => true,
                'data' => $role
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function update(array $payload, string $id): array
    {
        try {

            $this->roleModel->edit($payload, $id);

            $role = $this->getById($id);

            return [
                'status' => true,
                'data' => $role['data']
            ];
        } catch (Throwable $th) {
            return [
                'status' => false,
                'error' => $th->getMessage()
            ];
        }
    }

    public function delete(string $id): bool
    {
        try {
            $this->roleModel->drop($id);

            return true;
        } catch (Throwable $th) {
            return false;
        }
    }
}
