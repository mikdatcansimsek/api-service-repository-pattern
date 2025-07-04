<?php

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    protected $repository; // Type hint kaldırdım - child class'larda belirlenecek

    public function __construct($repository)
    {
        $this->repository = $repository;
    }

    /**
     * Tüm kayıtları getir
     */
    public function getAllRecords(): Collection
    {
        return $this->repository->getAll();
    }

    /**
     * ID ile kayıt getir
     */
    public function getRecordById(int $id): ?Model
    {
        $record = $this->repository->findById($id);

        if (!$record) {
            throw new \Exception("Record with ID {$id} not found");
        }

        return $record;
    }

    /**
     * Yeni kayıt oluştur
     */
    public function createRecord(array $data): Model
    {
        $validatedData = $this->validateCreateData($data);

        return $this->repository->create($validatedData ?? $data);
    }

    /**
     * Kayıt güncelle
     */
    public function updateRecord(int $id, array $data): Model
    {
        $validatedData = $this->validateUpdateData($id, $data);

        return $this->repository->update($id, $validatedData ?? $data);
    }

    /**
     * Kayıt sil
     */
    public function deleteRecord(int $id): bool
    {
        $this->validateDeleteOperation($id);

        return $this->repository->delete($id);
    }

    /**
     * Pagination
     */
    public function getPaginatedRecords(int $perPage = 15)
    {
        return $this->repository->paginate($perPage);
    }

    /**
     * Validation method'ları
     */
    protected function validateCreateData(array $data): ?array
    {
        return null;
    }

    protected function validateUpdateData(int $id, array $data): ?array
    {
        return null;
    }

    protected function validateDeleteOperation(int $id): void
    {
        // Base validation
    }
}
