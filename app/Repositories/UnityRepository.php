<?php

namespace App\Repositories;

use App\Models\Unity;
use App\Support\UnityContext;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class UnityRepository extends BaseRepository
{
    public function __construct(UnityContext $unityContext)
    {
        parent::__construct(Unity::class, $unityContext);
    }

    public function all(array $columns = ['*'], string $orderBy = 'name', string $direction = 'asc'): Collection
    {
        return $this->model::orderBy($orderBy, $direction)->get($columns);
    }

    public function find(int $id): ?Model
    {
        return $this->model::find($id);
    }

    public function findOrFail(int $id): Model
    {
        return $this->model::findOrFail($id);
    }
}
