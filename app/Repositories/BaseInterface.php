<?php

namespace App\Repositories;

interface BaseInterface
{
    /**
     * Get all
     * @return mixed
     */
    public function getAll($key = null);

    /**
     * Get
     * @return mixed
     */
    public function get($key = null);

    /**
     * Get all
     * @return mixed
     */
    public function select(...$columns);

    /**
     * Paginate
     * @return mixed
     */
    public function paginate($limit, $key = null);

    /**
     * Get one
     * @param $id
     * @return mixed
     */
    public function find($id, $key = null);

    /**
     * Create
     * @param array $attributes
     * @return mixed
     */
    public function create($attributes = [], $key = null);

    /**
     * Update
     * @param $id
     * @param array $attributes
     * @return mixed
     */
    public function update($id, $attributes = [], $key = null);

    /**
     * Save
     * @param array $attributes
     * @return mixed
     */
    public function save($attributes = [], $key = null);

    /**
     * Delete
     * @param $id
     * @return mixed
     */
    public function delete($id, $key = null);

    /**
     * Get data conditional
     * @param array $conditions
     * @return mixed
     */
    public function findColumns(array $conditions, $key = null);
}
