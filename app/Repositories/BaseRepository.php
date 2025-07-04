<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{


    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;  // Model'i dependency injection ile alır

    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): Model
    {
        $record = $this->findById($id);

        if (!$record) {
            throw new \Exception("Record with ID {$id} not found");
        }

        $record->update($data);
        return $record->fresh();  // Güncel veriyi döndürür fresh() dbden güncel veriyi alır
    }

    public function delete(int $id): bool
    {
        $record = $this->findById($id);

        if (!$record) {
            throw new \Exception("Record with ID {$id} not found");
        }

        return $record->delete();
    }

    public function findWhere(array $criteria): Collection
    {
        $query = $this->model->newQuery();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        return $query->get();
    }

    public function paginate(int $perPage = 15)
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Model'i değiştir (dependency injection için)
     */
    public function setModel(Model $model): void
    {
        $this->model = $model;
    }

    /**
     * Yeni query builder instance'ı al
     */
    protected function newQuery()
    {
        return $this->model->newQuery();
    }
}
