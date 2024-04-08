<?php

namespace App\Repositories\V3\BaseRepository;

use Exception;
use Throwable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ActionByUser;

abstract class BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    use ActionByUser;
    protected $table;

    /**
     * @var null|Model
     */
    protected $instanceWithTrash = null;

    /**
     * @var null|Model
     */
    protected $instanceWithoutTrash = null;

    /**
     * @return Model
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * @return Builder|SoftDeletes
     */
    public function query()
    {
        return $this->getModel()->newQuery();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        if (! $this->table) {
            $this->table = $this->getModel()->getTable();
        }

        return $this->table;
    }

    /**
     * @param $attributes
     * @return Model
     */
    public function create(array $attributes)
    {
        return $this->query()->create($attributes);
    }

    /**
     * @param array $attributes
     * @param array $value
     * @return Builder|Model
     */
    public function updateOrCreate(array $attributes, array $value)
    {
        return $this->query()->withTrashed()->updateOrCreate($attributes, $value);
    }

    public function update($id, array $attributes)
    {
        $result = $this->findById($id);
        if ($result) {
            $result->update($attributes);
            return $result;
        }
        return false;
    }

    /**
     * @param $attributes
     * @param $value
     * @return Builder|Model
     */
    public function firstOrCreate($attributes, $value = [])
    {
        return $this->query()->withTrashed()->firstOrCreate($attributes, $value);
    }

    /**
     * @return string
     */
    public function getKeyName()
    {
        return $this->getModel()->getKeyName();
    }

    public function find($id)
    {
        return $this->query()->find($id);
    }

    /**
     * @param $id
     * @return Builder|Model
     */
    public function findById($id)
    {
        if (! $this->instanceWithTrash) {
            $this->instanceWithTrash = $this->query()
                ->withTrashed()
                ->where($this->getKeyName(), $id)
                ->firstOrFail();
        }

        return $this->instanceWithTrash;
    }

    /**
     * @param $id
     * @return bool|void
     * @throws Exception
     */
    public function delete($id)
    {
        /** @var Model $model */
        $model = $this->findById($id);

        $model->delete();
    }

    /**
     * @param $id
     * @return bool|void
     * @throws Exception
     */
    public function forceDelete($id)
    {

        if (is_array($id)) {
            $this->query()->whereIn('id', $id)->forceDelete();
        }
        else {
            /** @var Model $model */
            $model = $this->findById($id);
            $model->forceDelete();
        }
    }

    /**
     * @param $id
     */
    public function restore($id)
    {
        /** @var SoftDeletes $model */
        $model = $this->findById($id);

        try {
            $model->restore();
        } catch (Throwable $exception) {

        }
    }

//    public function fetch()
//    {
//        return $this->query()
//            ->get()
//            ->toArray();
//    }

    public function findColumns(array $conditions, $key = null): Builder
    {
        return $this->query()->where($conditions);
    }

}
