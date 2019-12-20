<?php

namespace Tablda\DataReceiver;



class DataTableReceiver implements DataTableInterface
{
    protected $model;
    protected $builder;
    protected $case_sens;

    /**
     * DataTableReceiver constructor.
     * @param TabldaTable $model
     * @param null $case_sens
     */
    public function __construct(TabldaTable $model, $case_sens = null)
    {
        $this->case_sens = $case_sens;
        $this->model = $model;
        $this->clearQuery();
    }

    /**
     * Clear Builder Query;
     */
    public function clearQuery() {
        $this->builder = $this->model->newQuery();
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param  string|array|\Closure  $column
     * @param  mixed   $operator
     * @param  mixed   $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $mapped = $this->map_column($column);
        $this->builder->where($mapped, $operator, $value, $boolean);
        return $this;
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $mapped = $this->map_column($column);
        $this->builder->whereIn($mapped, $values, $boolean, $not);
        return $this;
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param  string $column
     * @return $this
     */
    public function groupBy($column)
    {
        $mapped = $this->map_column($column);
        $this->builder->groupBy($mapped);
        return $this;
    }

    /**
     * Add a "offset" to the query.
     *
     * @param  int $value
     * @return $this
     */
    public function offset($value)
    {
        $this->builder->offset($value);
        return $this;
    }

    /**
     * Add a "limit" to the query.
     *
     * @param  int $value
     * @return $this
     */
    public function limit($value)
    {
        $this->builder->limit($value);
        return $this;
    }

    /**
     * Set Distinct to the query.
     *
     * @return $this
     */
    public function distinct()
    {
        $this->builder->distinct();
        return $this;
    }

    /**
     * Set the columns to be selected.
     *
     * @param string $column
     * @return $this
     */
    public function select(string $column)
    {
        $mapped = $this->map_column($column);
        $this->builder->select($mapped);
        return $this;
    }

    /**
     * Get Data.
     *
     * @return array
     */
    public function get()
    {
        $models = $this->builder->get();
        foreach ($models as $m) {
            $m->setMaps( $this->model->getMaps() );
        }
        return $models->toArray();
    }

    /**
     * Insert Row.
     *
     * @param array $data
     * @return int
     */
    public function insert(array $data)
    {
        $this->clearQuery();
        return $this->builder->insertGetId( $this->map_data($data) );
    }

    /**
     * Update Data.
     *
     * @param array $data
     * @return bool
     */
    public function update(array $data)
    {
        return $this->builder->update( $this->map_data($data) );
    }

    /**
     * Delete Data.
     *
     * @return bool
     */
    public function delete()
    {
        return $this->builder->delete();
    }

    /**
     * Map input data for insert/update.
     * Clear all not present in $maps of Model.
     *
     * @param array $input
     * @return array
     */
    private function map_data(array $input) {
        $maps = $this->model->getMaps();
        $mapped_data = [];
        foreach ($input as $key => $val) {
            $mapper = $maps[ $this->t_case($key) ] ?? null;
            if ($mapper) {
                $mapped_data[ $this->t_case($mapper) ] = $val;
            }
        }
        return $mapped_data;
    }

    /**
     * Map column for wheres.
     *
     * @param string $column
     * @return null
     * @throws \Exception
     */
    private function map_column(string $column) {
        $maps = $this->model->getMaps();
        $mapped = $maps[ $this->t_case($column) ] ?? null;
        if (!$mapped) {
            throw new \Exception('Column "'.$column.'" not present in CorrespondenceFields');
        }
        return $this->t_case($mapped);
    }

    /**
     * Case sensitive or not.
     *
     * @param string $val
     * @return string
     */
    private function t_case(string $val)
    {
        if ($this->case_sens) {
            $val = strtolower($val);
        }
        return $val;
    }


}