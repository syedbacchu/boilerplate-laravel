<?php

namespace App\Http\Services;


class BaseService
{
    public $repository;

    function __construct($repository)
    {
        $this->repository = $repository;
    }

    public function create($data)
    {
        return $this->repository->create($data);
    }

    public function update($where, $data)
    {
        return $this->repository->update($where, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function fullDelete($id)
    {
        return $this->repository->fullDelete($id);
    }

    public function getById($id)
    {
        return $this->repository->getById($id);
    }

    public function getAll($relation = [])
    {
        return $this->repository->getAll($relation);
    }

    public function getWhere($where, $relation = [])
    {
        return $this->repository->getWhere($where, $relation);
    }

    public function whereFirst($where, $relation = [])
    {
        return $this->repository->whereFirst($where, $relation);
    }

    public function selectWhere($select, $where, $relation = [], $paginate = 0)
    {
        return $this->repository->selectWhere($select, $where, $relation, $paginate);
    }

    public function getDocs($params)
    {
        return $this->repository->getDocs($params);
    }
}
