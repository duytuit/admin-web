<?php

namespace App\Repositories\V3\BaseRepository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Database\Eloquent\SoftDeletes;

interface BaseRepositoryInterface {

    /**
     * @return Model
     */
    public function getModel();

    /**
     * @return Builder|SoftDeletes
     */
    public function query();

    /**
     * @return string
     */
    public function getTable();

    /**
     * Create
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * @param array $attributes
     * @param array $value
     * @return Builder|Model
     */
    public function updateOrCreate(array $attributes, array $value);

    /**
     * @param $attributes
     * @param $value
     * @return Builder|Model
     */
    public function firstOrCreate($attributes, $value = []);

    /**
     * @return string
     */
    public function getKeyName();

    /**
     * @param $id
     * @return Builder|Model
     */
    public function findById($id);

    /**
     * @param $id
     * @return bool|void
     * @throws Exception
     */
    public function delete($id);

    /**
     * @param $id
     * @return bool|void
     * @throws Exception
     */
    public function forceDelete($id);

    /**
     * @param $id
     */
    public function restore($id);


}