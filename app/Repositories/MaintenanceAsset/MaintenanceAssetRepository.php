<?php

namespace App\Repositories\MaintenanceAsset;

use App\Repositories\Eloquent\Repository;
use App\Models\MaintenanceAsset\MaintenanceAsset;
use Illuminate\Database\Eloquent\Builder;

const DEFAULT_PAGE = 10;

class MaintenanceAssetRepository extends Repository {


    /**
     * Specify Model class name
     *
     * @return mixed
     */
    function model()
    {
        return MaintenanceAsset::class;
    }

    public function findMaintenance($asset_id, $date)
    {
        return $this->model->where('asset_id', $asset_id)->where('maintenance_time', $date)->first();
    }

    public function myPaginate($input, $building_id, $per_page)
    {
        return $this->model->with('asset')
                    ->filter($input)
                    ->whereHas('asset', function (Builder $query) use($building_id) {
                        $query->where('bdc_building_id', $building_id);
                    })
                    ->orderBy('maintenance_time', 'desc')
                    ->paginate($per_page, ['*'], 'page_m');
    }

    public function checkDone($id, $user_id)
    {
        $this->model->find($id)->update([
            'status' => $this->model::FINISH,
            'user_id' => $user_id,
        ]);
    }

    public function cancelMaintain($id, $user_id)
    {
        $this->model->find($id)->update([
            'status' => $this->model::CANCEL,
            'user_id' => $user_id,
        ]);
    }

    public function findByActiveBuilding($building_id)
    {
        return $this->model->whereHas('asset', function($q) use ($building_id){
            $q->where('bdc_building_id', $building_id);
        })->where('status', $this->model::UNFINISH)->get();
    }

    public function getMenuMaintenance($building_id)
    {
        return $this->model->with('asset')
            ->whereHas('asset', function (Builder $query) use($building_id) {
                $query->where('bdc_building_id', $building_id);
            })
            ->orderBy('maintenance_time', 'desc')
            ->limit(5)
            ->get();
    }
}
