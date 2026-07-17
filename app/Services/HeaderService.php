<?php

namespace App\Services;

use App\Models\Header;
use App\Repositories\HeaderRepository;
use App\Support\UnityContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class HeaderService
{
    private HeaderRepository $headerRepository;
    private UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->headerRepository = new HeaderRepository($unityContext);
    }

    public function active(): Collection
    {
        return $this->headerRepository->active();
    }

    public function list(): Collection
    {
        return $this->headerRepository->all(['*'], 'name');
    }

    public function get(int $id): Header
    {
        return $this->headerRepository->findOrFail($id);
    }

    public function create(array $data): Header
    {
        if (empty($data['end_date'])) {
            $data['end_date'] = null;
        }

        $header = $this->headerRepository->create($data);
        $this->clearAllCache();

        return $header;
    }

    public function update(int $id, array $data): Header
    {
        if (empty($data['end_date'])) {
            $data['end_date'] = null;
        }

        $header = $this->headerRepository->findOrFail($id);
        $header->update($data);
        $this->clearAllCache();

        return $header->fresh();
    }

    public function delete(int $id): void
    {
        $header = $this->headerRepository->findOrFail($id);
        $header->delete();
        $this->clearAllCache();
    }

    public function futurePersistedEvents(int $headerId): Collection
    {
        $currentMonth = Carbon::now()->startOfMonth();

        $query = \App\Models\Event::with(['entries.asset'])
            ->where('header_id', $headerId)
            ->where('date', '>=', $currentMonth)
            ->orderBy('date');

        if ($this->unityContext->has()) {
            $query->where('unity_id', $this->unityContext->id());
        }

        return $query->get();
    }

    private function clearAllCache(): void
    {
        Cache::tags(['events', 'forecast'])->flush();
        BalanceService::clearCache();
    }
}
