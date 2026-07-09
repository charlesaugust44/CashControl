<?php

namespace App\Services;

use App\Models\Unity;
use App\Models\User;
use App\Repositories\UnityRepository;
use App\Support\UnityContext;
use Exception;

class UnityService
{
    protected UnityRepository $repository;

    public function __construct(UnityContext $unityContext)
    {
        $this->repository = new UnityRepository($unityContext);
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function getById(int $id): Unity
    {
        return $this->repository->findOrFail($id);
    }

    public function create(array $data): Unity
    {
        return $this->repository->create($data);
    }

    public function update(int $id, array $data): Unity
    {
        $unity = $this->getById($id);
        $this->repository->update($id, $data);
        return $unity->fresh();
    }

    public function delete(int $id): void
    {
        $unity = $this->getById($id);

        if ($unity->users()->count() > 0) {
            throw new Exception('Cannot delete unity with assigned users');
        }

        if ($unity->assets()->count() > 0 || $unity->headers()->count() > 0 || $unity->events()->count() > 0) {
            throw new Exception('Cannot delete unity with existing data');
        }

        $this->repository->delete($id);
    }

    public function assignUser(int $unityId, int $userId): void
    {
        $unity = $this->getById($unityId);
        $user = User::findOrFail($userId);

        if ($user->unities()->count() > 0) {
            $user->unities()->detach();
        }

        $unity->users()->attach($userId);
    }

    public function unassignUser(int $unityId, int $userId): void
    {
        $unity = $this->getById($unityId);
        $unity->users()->detach($userId);
    }

    public function getAvailableUsers(int $unityId)
    {
        $assignedUserIds = Unity::find($unityId)->users()->pluck('users.id')->toArray();
        return User::whereNotIn('id', $assignedUserIds)
            ->whereNotNull('approved_at')
            ->orderBy('name')
            ->get();
    }
}
