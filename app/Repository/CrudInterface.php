<?php
namespace App\Repository;

interface CrudInterface
{
    public function drop(int $id);

    public function edit(array $payload, int $id);

    public function getAll(array $filter, int $itemPerPage, string $sort);

    public function getById(int $id);

    public function store(array $payload);
}
