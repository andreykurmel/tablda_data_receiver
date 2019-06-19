<?php

namespace Tablda\DataReceiver;


interface TabldaDataInterface
{
    /**
     * Get Mapable-Eloquent Query Builder.
     *
     * @param string $table
     * @return mixed
     */
    public function getQuery(string $table);

    /**
     * Get data for current app in 'correspondence tables'.
     *
     * @return array
     */
    public function appDatas();
}