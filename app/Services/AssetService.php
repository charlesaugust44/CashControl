<?php

namespace App\Services;

use App\Models\Asset;
use App\Repositories\AssetRepository;
use App\Repositories\EntryRepository;
use App\Support\UnityContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AssetService
{

    private AssetRepository $assetRepository;
    private EntryRepository $entryRepository;
    private UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->assetRepository = new AssetRepository($unityContext);
        $this->entryRepository = new EntryRepository($unityContext);
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
        $asset = $this->assetRepository->create($data);
        $this->clearAllCache();

        return $asset;
    }

    public function update(string $id, array $data): Asset
    {
        $asset = $this->assetRepository->findOrFail(intval($id));
        $this->assetRepository->update($asset->id, $data);
        $this->clearAllCache();

        return $this->assetRepository->findOrFail($asset->id);
    }

    private function clearAllCache(): void
    {
        Cache::tags(['events', 'forecast'])->flush();
        BalanceService::clearCache();
    }
}
