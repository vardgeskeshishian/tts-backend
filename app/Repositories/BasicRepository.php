<?php

namespace App\Repositories;

use App\Factories\ModelsFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

class BasicRepository
{
    protected $modelName = "";
    /**
     * @var Builder|null
     */
    protected $model = null;

    public function __construct($model = null)
    {
        $factory = resolve(ModelsFactory::class);

        if ($this->modelName) {
            $model = $this->modelName;
        }

        $this->model = $factory->getModelFromName($model);
    }

    public function applyRelation($relations)
    {
        $this->model = $this->model->with($relations);

        return $this;
    }

    public function get()
    {
        return $this->model->all();
    }

    public function getBy(string $column, $value)
    {
        return $this->model->where($column, $value)->get();
    }

    public function getWhere(array $values)
    {
        return $this->model->where($values)->get();
    }

    public function getWhereIn(string $column, array $values)
    {
        return $this->model->whereIn($column, $values)->get();
    }

    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param string $column
     * @param $value
     *
     * @return Model
     */
    public function findOneBy(string $column, $value)
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function findOneWhere(array $values)
    {
        return $this->model->where($values)->first();
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function insertOne(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * @param array $findByData
     * @param array $fillInData
     *
     * @return mixed
     */
    public function updateOrCreate(array $findByData, array $fillInData)
    {
        $fullData = array_merge($findByData, $fillInData);

        return $this->model->updateOrCreate($findByData, $fullData);
    }

    /**
     * Return amount of rows by query
     *
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    public function countBy($column, $value)
    {
        return $this->model->where($column, $value)->count();
    }

    /*
     * Skeleton for filtering, sorting, pagination
     */
    protected $filterFields = [];

    private $defaultSortField = 'id';
    private $defaultSortOrder = 'desc';

    protected $perPage = 15;

    /**
     * each filter should have it's own getCamelCaseFilter with specific conditions for it
     *
     * @return array
     */
    protected function parseFilterConditions()
    {
        $filters = request('filter', []);

        $where = [];

        foreach ($filters as $filter => $value) {
            $filterName = sprintf("get%sFilter", Str::studly($filter));

            if (!method_exists($this, $filterName)) {
                $where[] = $this->getSimpleFilter($filter, $value);

                continue;
            }

            $where[] = $this->$filterName($value);
        }

        return $where;
    }

    protected function getSimpleFilter($filter, $value)
    {
        return $this->model->where($filter, $value);
    }

    public function filter($additionalWhere = [])
    {
        $filters = $this->parseFilterConditions();

        if (!empty($filters)) {
            foreach ($filters as $query) {
                $this->model = $query;
            }
        }

        if (!empty($additionalWhere)) {
            $this->model = $this->model->where($additionalWhere);
        }

        return $this;
    }

    public function sort()
    {
        $sorting = request('sort', []);

        $by = $sorting['by'] ?? $this->defaultSortField;
        $order = $sorting['order'] ?? $this->defaultSortOrder;

        if (Str::contains($order, ':')) {
            [, $order] = explode(':', $order);
        }

        $this->model = $this->model->orderBy($by, $order);

        return $this;
    }

    public function paginate($perPage = 15)
    {
        $perPage = request('perpage', $perPage);

        return $this->model->paginate($perPage);
    }

    #[NoReturn] public function dumpSql()
    {
        dd($this->model->toSql());
    }
}
