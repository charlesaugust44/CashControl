<?php

namespace App\Services;

use App\Repositories\HeaderRepository;
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
}
