<?php

namespace Tablda\DataReceiver;


interface TabldaDataInterface
{
    /**
     * TabldaDataInterface constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings = []);

    /**
     * Get Mapable-Eloquent Query Builder.
     *
     * @param string $table
     * @return DataTableReceiver
     */
    public function tableReceiver(string $table);

    /**
     * Get data for current app in 'correspondence tables'.
     *
     * @return array
     */
    public function appDatas();
}