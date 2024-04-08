<?php

namespace Modules\Media\Repositories;

use App\Models\Building;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Modules\Media\Entities\Document;

class DocumentRepository extends BaseRepository implements DocumentInterface {

    public function getModel(): string
    {
        return Document::class;
    }

    public function filterByBuildingId($building_id)
    {
        return $this->model->whereHas('buildings',function (Builder $query) use ($building_id) {
                    $query->where('bdc_building.id', $building_id);
                })
                ->orderBy('id', 'desc')
                ->get();
    }

    public function filterDocument($filter)
    {
        return $this->model->whereHas('apartments',function (Builder $query) use ($filter){
            $query->where('building_id',$filter['building_id']);
            if (!empty($filter['apartment_id'])) {
                $query->where('bdc_apartments.id',$filter['apartment_id']);
            }
            if (!empty($filter['apartment_group_id'])) {
                $query->where('id', "=",-1);
            }
        })
            ->orWhereHas('apartmentGroups',function (Builder $query) use ($filter){
                $query->where('bdc_building_id',$filter['building_id']);
                if (!empty($filter['apartment_id'])) {
                    $query->whereHas('apartments',function (Builder $q) use ($filter) {
                        $q->where('bdc_apartments.id',$filter['apartment_id']);
                    });
                }
                if (!empty($filter['apartment_group_id'])) {
                    $query->where('id', $filter['apartment_group_id']);
                }
        })
            ->with('apartments')
            ->with('apartmentGroups')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function filterDocumentByApartmentId($apartment_id)
    {
        return $this->model->whereHas('apartments',function (Builder $query) use ($apartment_id){
            $query->where('id',$apartment_id);
        })
            ->orWhereHas('apartmentGroups',function (Builder $query) use ($apartment_id){
                $query->whereHas('apartments',function (Builder $q) use ($apartment_id) {
                    $q->where('id',$apartment_id);
                });
            })
            ->with('apartments')
            ->with('apartmentGroups')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function filterDocumentAppByApartmentId($apartment_id)
    {
        return $this->model->whereHas('apartments',function (Builder $query) use ($apartment_id){
            $query->where('id',$apartment_id);
        })
            ->orWhereHas('apartmentGroups',function (Builder $query) use ($apartment_id){
                $query->whereHas('apartments',function (Builder $q) use ($apartment_id) {
                    $q->where('id',$apartment_id);
                });
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    public function filterById($id)
    {
        return $this->model
            ->with("apartments")
            ->with('apartmentGroups')
            ->with('buildings')
            ->find($id);
    }

}
