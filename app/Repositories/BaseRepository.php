<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseRepository
{
    protected string $model;

    //TODO: implement pagination on lists
    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->model::all($columns);
    }

    public function find(int $id): ?Model
    {
        return $this->model::find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model::findOrFail($id);
    }

    public function findBy(string $column, $value): ?Model
    {
        return $this->model::where($column, $value)->first();
    }

    public function findAllBy(string $column, $value): Collection
    {
        return $this->model::where($column, $value)->get();
    }

    public function getWhere(array $conditions): Collection
    {
        $query = $this->model::query();
        foreach ($conditions as $column => $value) {
            $query->where($column, $value);
        }
        return $query->get();
    }

    public function create(array $data): Model
    {
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
