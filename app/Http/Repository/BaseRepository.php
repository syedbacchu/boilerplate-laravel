<?php

namespace App\Http\Repository;


class BaseRepository
{
    public $model;

    function __construct($model)
    {
        $this->model = $model;
    }

    public function create($data)
    {
        return $this->model->create($data);
    }

    public function update($where, $data)
    {
        return $this->model->where($where)->update($data);
    }

    public function delete($id)
    {
        return $this->model->where('id', $id)->update(['status' => STATUS_DELETED]);
    }

    public function deleteByColumn($column, $value)
    {
        return $this->model->where($column, $value)->delete();
    }

    public function fullDelete($id)
    {
        return $this->model->where('id', $id)->delete();
    }

    public function getById($id)
    {
        return $this->model->where('id', $id)->first();
    }

    public function getAll($relation = [])
    {
        return $this->model->with($relation)->get();
    }

    public function exists($where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->exists();
    }

    public function getWhere($where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->get();
    }

    public function countWhere($where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->count();
    }

    public function randomWhere($quantity, $where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->inRandomOrder($quantity)->get();
    }

    public function whereFirst($where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->first();
    }

    public function selectWhere($select, $where, $relation = [], $paginate = 0)
    {
        if ($paginate === 0) {
            return $this->model->select($select)->where($where)->with($relation)->get();
        }

        return $this->model->select($select)->where($where)->with($relation)->paginate($paginate);
    }

    public function limitWhere($quantity, $where = [], $relation = [])
    {
        return $this->model->where($where)->with($relation)->limit($quantity)->get();
    }

    public function getDocs($params=[],$select=null,$orderBy=[],$with=[]){
        if($select == null){
            $select = ['*'];
        }
        $query = $this->model::select($select);
        foreach($with as $wt) {
            $query = $query->with($wt);
        }
        foreach($params as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        foreach($orderBy as $key => $value) {
            $query->orderBy($key,$value);
        }

        return $query->get();
    }

    public function updateWhere($where=[], $update=[])
    {
        $query = $this->model::query();
        foreach($where as $key => $value) {
            if(is_array($value)){
                $query->where($key,$value[0],$value[1]);
            }else{
                $query->where($key,'=',$value);
            }
        }
        return $query->update($update);
    }
}
