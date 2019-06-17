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
}