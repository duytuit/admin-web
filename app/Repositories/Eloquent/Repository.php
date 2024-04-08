<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use App\Repositories\Eloquent\Exceptions\RepositoryException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Repository
 *
 */
abstract class Repository implements RepositoryInterface {

    /**
     * @var App
     */
    private $app;

    /**
     * @var
     */
    protected $model;

    /**
     * @param App $app
     * @throws \Repositories\Exceptions\RepositoryException
     */
    public function __construct(App $app) {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name
     *
     * @return mixed
     */
    abstract function model();

    /**
     * @param array $columns
     * @return mixed
     */
    public function paginate( $columns = array('*')) {
        return $this->model->orderBy('created_at', 'desc')->paginate(env('PERPAGE'), $columns);
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function all( $columns = array('*')) {
        return $this->model->orderBy('created_at', 'desc')->get($columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data) {
        return $this->model->create($data);
    }

    /**
     * @param array $data
     * @param string $value
     * @param string $attribute
     * @return mixed
     */
    public function update(array $data, $value, $attribute="id") {

        $rs = $this->model->where($attribute, '=', $value)->first();
        if($rs){
           return $rs->update($data);
        }else{
            return false;
        }
    }

     /**
     * @param int $perPage
     * @param array $columns
     * @return mixed
     */
    public function deletePaginate( $columns = array('*')) {
        return $this->model->withTrashed()->paginate(env('PERPAGE'), $columns);
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    public function find($id, $columns = array('*')) {
        return $this->model->find($id, $columns);
    }
    /**
     * @param $id
     * @param array $columns
     * @return mixed
     */
    // public function search($input, $columns = array('*')) {

    //     if (empty($input)) {
    //         return array();
    //     }

    //     return $this->model->where($input)->paginate(env('PERPAGE'), $columns);
    // }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*')) {
        return $this->model->where($attribute, '=', $value)->first($columns);
    }
    public function delete($array){
        return $this->model->where($array )->delete();
    }


    /**
     * @param array $ids
     * @return mixed
     */
    public function restore( $ids)
    {
        return $this->model->withTrashed()->whereIn('id', $ids)->restore();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     * @throws RepositoryException
     */
    public function makeModel() {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model)
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");

        return $this->model = $model;
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

    /**
     * @return Builder|SoftDeletes
     */
    protected function query()
    {
        return $this->getModel()->newQuery();
    }

    /**
     * @return Model
     */
    protected function getModel(): Model
    {
        return $this->model;
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
}
