<?php
namespace App\Repository;

interface CrudInterface
{
    public function drop(string $id);

    public function edit(array $payload, string $id);

    public function getAll(array $filter, int $itemPerPage, string $sort);

    public function getById(string $id);

    public function store(array $payload);
}
