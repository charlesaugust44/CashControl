<?php

namespace App\Repositories;

use App\Models\Asset;
use App\Support\UnityContext;
use Illuminate\Database\Eloquent\Model;

class AssetRepository extends BaseRepository
{

    public function __construct(UnityContext $unityContext)
    {
        parent::__construct(Asset::class, $unityContext);
    }

    public function create(array $data): Model
    {
        $asset = new Asset();

        $asset->name = $data['name'];
        $asset->balance  = $data['balance'];

        if ($this->unityContext->has()) {
            $asset->unity_id = $this->unityContext->id();
        }

        $asset->save();

        return $asset;
    }


}
