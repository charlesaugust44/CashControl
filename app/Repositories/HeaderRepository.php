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

    public function active(): Collection
    {
        //TODO: enforce start and end date are always firstOfMonth
        return Header::where('start_date', '<>', 'end_date')
            ->where('start_date', '<=', Carbon::now()->firstOfMonth())
            ->where(function (Builder $query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::now()->firstOfMonth());
            })
            ->get();
    }
}
