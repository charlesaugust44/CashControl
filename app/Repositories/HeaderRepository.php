<?php

namespace App\Repositories;

use App\Models\Header;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class HeaderRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Header::class);
    }

    public function active(?int $year = null, ?int $month = null): Collection
    {
        $referenceDate = Carbon::create(
            $year ?? Carbon::now()->year,
            $month ?? Carbon::now()->month,
            1
        );

        return Header::with(['asset', 'destinationAsset'])
            ->where('start_date', '<=', $referenceDate)
            ->where(function (Builder $query) use ($referenceDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $referenceDate);
            })
            ->get();
    }
}
