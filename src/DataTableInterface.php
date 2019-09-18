<?php

namespace Tablda\DataReceiver;


interface DataTableInterface
{
    /**
     * DataTableInterface constructor.
     * @param TabldaTable $model
     */
    public function __construct(TabldaTable $model);

    /**
     * @return void
     */
    public function clearQuery();

    /**
     * @param $column
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and');

    /**
     * @param $column
     * @param $values
     * @param string $boolean
     * @param bool $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false);

    /**
     * @return $this
     */
    public function distinct();

    /**
     * @return array
     */
    public function get();

    /**
     * @param array $data
     * @return int
     */
    public function insert(array $data);

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data);

    /**
     * @return bool
     */
    public function delete();
}