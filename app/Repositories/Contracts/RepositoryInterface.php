<?php 
namespace App\Repositories\Contracts;

interface RepositoryInterface {

    public function all($columns = array('*'));

    public function paginate($columns = array('*'));

    public function create(array $data);

    public function update(array $data, $id);

    public function deletePaginate($columns = array('*'));

    public function find($id, $columns = array('*'));

    public function findBy($attribute, $value, $columns = array('*'));

    public function restore( $ids);
}