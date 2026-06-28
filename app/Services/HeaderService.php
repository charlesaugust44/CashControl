<?php

namespace App\Services;

use App\Models\Header;
use App\Repositories\HeaderRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class HeaderService
{
    private HeaderRepository $headerRepository;

    public function __construct()
    {
        $this->headerRepository = new HeaderRepository();
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

        return $this->headerRepository->create($data);
    }

    public function update(int $id, array $data): Header
    {
        if (empty($data['end_date'])) {
            $data['end_date'] = null;
        }

        $header = $this->headerRepository->findOrFail($id);
        $header->update($data);

        return $header->fresh();
    }

    public function delete(int $id): void
    {
        $header = $this->headerRepository->findOrFail($id);
        $header->delete();
    }

    public function futurePersistedEvents(int $headerId): Collection
    {
        $currentMonth = Carbon::now()->startOfMonth();

        return \App\Models\Event::with(['entries.asset'])
            ->where('header_id', $headerId)
            ->where('date', '>=', $currentMonth)
            ->orderBy('date')
            ->get();
    }
}
