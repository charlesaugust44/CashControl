<?php

namespace App\Repositories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Model;

class AssetRepository extends BaseRepository
{

    public function __construct()
    {
        parent::__construct(Asset::class);
    }

    public function create(array $data): Model
    {
        $asset = new Asset();

        $asset->name = $data['name'];
        $asset->balance  = $data['balance'];
        $asset->save();

        return $asset;
    }


}
