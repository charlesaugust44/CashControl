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

    public function get(string $id): Asset
    {
        return $this->assetRepository->findOrFail(intval($id));
    }

    public function entries(string $id): Collection
    {
        /** @var Asset $asset */
        $asset = $this->assetRepository->findOrFail(intval($id));
        return $this->entryRepository->history($asset);
    }

    public function entriesByMonth(string $id, int $year, int $month): Collection
    {
        /** @var Asset $asset */
        $asset = $this->assetRepository->findOrFail(intval($id));
        return $this->entryRepository->historyByMonth($asset, $year, $month);
    }

    public function create(array $data): Asset
    {
        return $this->assetRepository->create($data);
    }

    public function update(string $id, array $data): Asset
    {
        $asset = $this->assetRepository->findOrFail(intval($id));
        $this->assetRepository->update($asset->id, $data);

        return $this->assetRepository->findOrFail($asset->id);
    }
}
