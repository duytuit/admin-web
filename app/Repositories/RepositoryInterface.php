<?php

namespace App\Repositories;

interface RepositoryInterface
{
    /**
     * Filter data by building id
     * @return mixed
     */
    public function filterByBuildingId($buildingId);

    /**
     * Reload data with parameter building id
     * @return mixed
     */
    public function reloadByBuildingId($buildingId);

    /**
     * Filter data by id
     * @return mixed
     */
    public function filterById($id);

    /**
     * Reload data with parameter id
     * @return mixed
     */
    public function reloadById($id);

    /**
     * Delete data and cache
     * @return mixed
     */
    public function deleteRedisCache($model = null);
}
