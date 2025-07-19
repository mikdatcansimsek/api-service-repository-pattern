<?php

namespace App\Abstracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\ProductNotFoundException;
use App\Exceptions\CategoryNotFoundException;
use App\Exceptions\DatabaseException;
use App\Exceptions\CustomException;

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
        try {
            return $this->repository->getAll();
        } catch (\Exception $e) {
            throw new DatabaseException('Kayıtları getirme', $e, [
                'operation' => 'getAllRecords',
                'model' => $this->getModelName()
            ]);
        }
    }

    /**
     * ID ile kayıt getir - CustomException ile
     */
    public function getRecordById(int $id): ?Model
    {
        try {
            $record = $this->repository->findById($id);

            if (!$record) {
                // Model türüne göre özel exception at
                $this->throwNotFoundExceptionForModel($id);
            }

            return $record;
        } catch (CustomException $e) {
            // CustomException'ları yeniden at
            throw $e;
        } catch (\Exception $e) {
            throw new DatabaseException('Kayıt getirme', $e, [
                'id' => $id,
                'model' => $this->getModelName()
            ]);
        }
    }

    /**
     * Yeni kayıt oluştur
     */
    public function createRecord(array $data): Model
    {
        try {
            $validatedData = $this->validateCreateData($data);
            return $this->repository->create($validatedData ?? $data);
        } catch (CustomException $e) {
            // CustomException'ları yeniden at
            throw $e;
        } catch (\Exception $e) {
            throw new DatabaseException('Kayıt oluşturma', $e, [
                'input_data' => $data,
                'model' => $this->getModelName()
            ]);
        }
    }

    /**
     * Kayıt güncelle
     */
    public function updateRecord(int $id, array $data): Model
    {
        try {
            // Önce kayıt var mı kontrol et (bu zaten custom exception atacak)
            $this->getRecordById($id);

            $validatedData = $this->validateUpdateData($id, $data);
            return $this->repository->update($id, $validatedData ?? $data);
        } catch (CustomException $e) {
            // CustomException'ları yeniden at
            throw $e;
        } catch (\Exception $e) {
            throw new DatabaseException('Kayıt güncelleme', $e, [
                'id' => $id,
                'input_data' => $data,
                'model' => $this->getModelName()
            ]);
        }
    }

    /**
     * Kayıt sil
     */
    public function deleteRecord(int $id): bool
    {
        try {
            // Önce kayıt var mı kontrol et (bu zaten custom exception atacak)
            $this->getRecordById($id);

            $this->validateDeleteOperation($id);
            return $this->repository->delete($id);
        } catch (CustomException $e) {
            // CustomException'ları yeniden at
            throw $e;
        } catch (\Exception $e) {
            throw new DatabaseException('Kayıt silme', $e, [
                'id' => $id,
                'model' => $this->getModelName()
            ]);
        }
    }

    /**
     * Pagination
     */
    public function getPaginatedRecords(int $perPage = 15)
    {
        try {
            return $this->repository->paginate($perPage);
        } catch (\Exception $e) {
            throw new DatabaseException('Sayfalı kayıtları getirme', $e, [
                'per_page' => $perPage,
                'model' => $this->getModelName()
            ]);
        }
    }

    /**
     * Model türüne göre özel not found exception at
     */
    protected function throwNotFoundExceptionForModel(int $id): void
    {
        $modelClass = $this->getModelClass();

        switch ($modelClass) {
            case 'App\Models\Product':
                throw new ProductNotFoundException($id, [
                    'available_products_count' => $this->repository->count() ?? 0,
                    'suggestion' => 'Mevcut ürünleri listelemek için /api/products endpoint\'ini kullanın.'
                ]);

            case 'App\Models\Category':
                throw new CategoryNotFoundException($id, [
                    'available_categories_count' => $this->repository->count() ?? 0,
                    'suggestion' => 'Mevcut kategorileri listelemek için /api/categories endpoint\'ini kullanın.'
                ]);

            default:
                // Generic model exception
                $modelName = $this->getModelName();
                throw new CustomException(
                    "ID {$id} olan {$modelName} bulunamadı.",
                    404,
                    null,
                    [
                        'model' => $modelName,
                        'id' => $id,
                        'suggestion' => "Geçerli bir {$modelName} ID'si kullanın."
                    ]
                );
        }
    }

    /**
     * Model class adını al
     */
    protected function getModelClass(): string
    {
        if (method_exists($this->repository, 'getModel')) {
            return get_class($this->repository->getModel());
        }

        // Repository class'ından model'i tahmin et
        $repositoryClass = get_class($this->repository);
        return str_replace(['Repository', 'App\\Repositories\\'], ['', 'App\\Models\\'], $repositoryClass);
    }

    /**
     * Model adını al
     */
    protected function getModelName(): string
    {
        return class_basename($this->getModelClass());
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
        // Base validation - child class'larda override edilebilir
    }

    /**
     * Repository helper methods
     */
    protected function getRepository()
    {
        return $this->repository;
    }

    /**
     * Model existence check
     */
    protected function recordExists(int $id): bool
    {
        try {
            return $this->repository->findById($id) !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get total count
     */
    protected function getTotalCount(): int
    {
        try {
            return $this->repository->count() ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
