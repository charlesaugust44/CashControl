<?php

namespace App\Repositories;

use App\Support\UnityContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected string $model;
    protected UnityContext $unityContext;

    public function __construct(string $model, UnityContext $unityContext)
    {
        $this->model = $model;
        $this->unityContext = $unityContext;
    }

    protected function scopeToUnity(Builder $query, ?string $table = null): Builder
    {
        if ($this->unityContext->has()) {
            $column = $table ? "{$table}.unity_id" : 'unity_id';
            $query->where($column, $this->unityContext->id());
        }

        return $query;
    }

    public function all(array $columns = ['*'], string $orderBy = 'id', string $direction = 'asc'): Collection
    {
        return $this->scopeToUnity($this->model::query())
            ->orderBy($orderBy, $direction)
            ->get($columns);
    }

    public function find(int $id): ?Model
    {
        return $this->scopeToUnity($this->model::query())->find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->scopeToUnity($this->model::query())->findOrFail($id);
    }

    public function findBy(string $column, $value): ?Model
    {
        return $this->scopeToUnity($this->model::query())->where($column, $value)->first();
    }

    public function findAllBy(string $column, $value): Collection
    {
        return $this->scopeToUnity($this->model::query())->where($column, $value)->get();
    }

    public function getWhere(array $conditions): Collection
    {
        $query = $this->scopeToUnity($this->model::query());
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        return $query->get();
    }

    public function create(array $data): Model
    {
        if ($this->unityContext->has() && ! isset($data['unity_id'])) {
            $data['unity_id'] = $this->unityContext->id();
        }

        return $this->model::create($data);
    }

    public function update(int $id, array $data): bool
    {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        return $record->update($data);
    }

    public function delete(int $id): bool
    {
        $record = $this->find($id);
        if (!$record) {
            return false;
        }
        return $record->delete();
    }
}
