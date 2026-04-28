<?php

namespace App\Services;

use App\Models\Asset;
use App\Repositories\AssetRepository;
use App\Repositories\EntryRepository;
use Illuminate\Support\Collection;

class AssetService
{

    private AssetRepository $assetRepository;
    private EntryRepository $entryRepository;

    public function __construct()
    {
        $this->assetRepository = new AssetRepository();
        $this->entryRepository = new EntryRepository();
    }

    /**
     * @return Collection<Asset>
     */
    public function list(string $orderBy = 'created_at', string $direction = 'desc'): Collection
    {
        return $this->assetRepository->all(['*'], $orderBy, $direction);
    }

    public function entries(int $id): Collection
    {
        /** @var Asset $asset */
        $asset = $this->assetRepository->findOrFail($id);
        return $this->entryRepository->history($asset);
    }

    public function create(array $data): Asset
    {
        return $this->assetRepository->create($data);
    }
}
