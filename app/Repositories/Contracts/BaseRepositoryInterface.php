<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     */
    public function getAll(): Collection;

    /**
     * Find a record by its ID.
     */
    public function findById(int $id): ?Model;

    /**
     * Create a new record.
     */
    public function create(array $data): Model;

    /**
     * Update a record by its ID.
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete a record by its ID.
     */
    public function delete(int $id): bool;

    /**
     * Find records by a set of criteria.
     */
    
    public function paginate(int $perPage = 15);
}
