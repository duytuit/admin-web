<?php

namespace App\Repositories\Assets;

use App\Repositories\Eloquent\Repository;
use App\Models\Asset\Asset;

const DEFAULT_PAGE = 10;

class AssetRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return Asset::class;
    }

    public function myPaginate($keyword, $active_building, $per_page)
    {
        return $this->model
            ->with('maintenances', 'type', 'period')
            ->where('bdc_building_id', $active_building)
            ->filter($keyword)
            ->orderBy('updated_at', 'DESC')
            ->paginate($per_page);
    }

    public function findAsset($id)
    {
        return $this->model->with('maintenances')
                    ->with('maintenances.workdiary')->findOrFail($id);
    }

    public function deleteMulti($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    public function getAssetByLimit($limit, $offset)
    {
        $assets = $this->model->with('maintenances', 'type', 'period')
                                ->orderBy('created_at', 'asc')
                                ->limit($limit)
                                ->offset($offset)
                                ->get();
        foreach ($assets as $key => $asset) {
            if (!$asset->checkIfExpired()) {
                $assets->forget($key);
                $asset->update(['check_maintenance' => true]);
            }
        }
        return $assets;
    }

    public function updateToMaintenance()
    {
        $this->model->where('check_maintenance', true)->update(['check_maintenance' => false]);
    }

    public function count()
    {
        return $this->model->count();
    }
}
