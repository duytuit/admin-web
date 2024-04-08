<?php

namespace App\Repositories;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Facades\Redis;

abstract class BaseRepository implements BaseInterface
{
    //model muốn tương tác
    protected $model;

   //khởi tạo
    public function __construct()
    {
        $this->setModel();
    }

    //lấy model tương ứng
    abstract public function getModel();

    /**
     * Set model
     */
    public function setModel()
    {
        $this->model = app()->make(
            $this->getModel()
        );
    }

    public function getAll($key = null)
    {
        if($key != null && Redis::exists($key)) {
            return json_decode(Redis::get($key));
        }
        return $this->model->all();
    }

    public function get($key = null)
    {
        return $this->model->get();
    }

    public function select(...$columns)
    {
        return $this->model->select($columns);
    }

    public function paginate($limit, $key = null)
    {
        return $this->model->paginate($limit);
    }

    public function find($id, $key = null)
    {
        if($key != null && Redis::exists($key)) {
            return json_decode(Redis::get($key));
        }
        return $this->model->find($id);
    }

    public function create($attributes = [], $key = null)
    {
        return $this->model->create($attributes);
    }

    public function update($id, $attributes = [], $key = null)
    {
        $result = $this->find($id);
        if ($result) {
            $result->update($attributes);
            return $result;
        }
        return false;
    }

    public function updateObject($conditions = [], $attributes = [])
    {
        return $this->model->where($conditions)->update($attributes);
    }

    public function save($attributes = [], $key = null)
    {
        return $this->model->save($attributes);
    }

    public function delete($id, $key = null)
    {
        $result = $key != null && Redis::exists($key) ? json_decode(Redis::get($key)) : $this->find($id);

        if ($result) {
            $result->delete();
            if($key != null) {
                Redis::del($key);
            }
            return true;
        }
        return false;
    }

    public function findColumns(array $conditions, $key = null)
    {
        return $this->model->where($conditions);
    }
}
