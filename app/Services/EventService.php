<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\EventRepository;
use App\Support\UnityContext;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\Facades\Cache;

class EventService
{
    private EventRepository $eventRepository;
    private EventGenerationService $eventGenerationService;
    private UnityContext $unityContext;

    public function __construct(UnityContext $unityContext)
    {
        $this->unityContext = $unityContext;
        $this->eventRepository = new EventRepository($unityContext);
        $this->eventGenerationService = new EventGenerationService($unityContext);
    }

    public function consolidate(int $id): Event
    {
        $event = $this->eventRepository->consolidate($id);
        $this->clearRelatedCache($event->date->year, $event->date->month);
        return $event;
    }

    public function listByMonth(int $year, int $month): Enumerable
    {
        $cacheKey = $this->buildCacheKey("events:{$year}-{$month}");
        
        return Cache::tags(['events'])->remember($cacheKey, now()->addHours(24), function () use ($year, $month) {
            return $this->eventGenerationService->getMonthEvents($year, $month);
        });
    }

    public function clearMonthCache(int $year, int $month): void
    {
        $cacheKey = $this->buildCacheKey("events:{$year}-{$month}");
        Cache::tags(['events'])->forget($cacheKey);
    }

    public function clearRelatedCache(int $year, int $month): void
    {
        $this->clearMonthCache($year, $month);
        Cache::tags(['forecast'])->flush();
    }

    private function buildCacheKey(string $key): string
    {
        $unityId = $this->unityContext->has() ? $this->unityContext->id() : 'global';
        return "unity_{$unityId}:{$key}";
    }
}
