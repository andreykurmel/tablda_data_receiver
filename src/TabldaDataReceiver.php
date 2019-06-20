<?php

namespace Tablda\DataReceiver;


use Illuminate\Support\Facades\DB;

class TabldaDataReceiver implements TabldaDataInterface
{
    protected $connection_sys;
    protected $connection_data;
    protected $apps_tb;
    protected $tables_tb;
    protected $fields_tb;

    protected $app;
    protected $tables_cache = [];

    public function __construct(array $settings = null)
    {
        $this->setSettings();
        $this->setAppRecord();
        $this->configDataConnection();
    }

    /**
     * Set Settings for DataReceiver (step 1).
     *
     * @throws \Exception
     */
    private function setSettings() {
        $this->connection_sys = env('TABLDA_SYS_CONN');
        $this->connection_data = env('TABLDA_DATA_CONN');

        if (!$this->connection_sys || !$this->connection_data) {
            throw new \Exception('Settings for Tablda connections not found.');
        }

        $this->apps_tb = env('TABLDA_APPS_TB', 'correspondence_apps');
        $this->tables_tb = env('TABLDA_TABLES_TB', 'correspondence_tables');
        $this->fields_tb = env('TABLDA_FIELDS_TB', 'correspondence_fields');
    }

    /**
     * Get App Config Record for DataReceiver (step 2).
     *
     * @throws \Exception
     */
    private function setAppRecord() {
        $this->app = DB::connection($this->connection_sys)
            ->table($this->apps_tb)
            ->where('name', env('TABLDA_APP_NAME'))
            ->first();

        if (!$this->app) {
            throw new \Exception('Record for Tablda application not found.');
        }
    }

    /**
     * Config Data Connection for DataReceiver (step 3).
     *
     * @throws \Exception
     */
    private function configDataConnection() {
        $data = $this->connection_data;
        config([
            "database.connections.$data.host" => ($this->app->host ?: env('DB_HOST', '127.0.0.1')),
            "database.connections.$data.database" => ($this->app->db ?: ''),
            "database.connections.$data.username" => ($this->app->login ?: env('DB_USERNAME', 'root')),
            "database.connections.$data.password" => ($this->app->pass ?: env('DB_PASSWORD', '')),
        ]);
    }

    /**
     * Get Query with field mapping.
     *
     * @param string $table
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery(string $table)
    {
        $tb = $this->getTableWithMaps($table);

        return (new TabldaTable())
            ->setConnection($this->connection_data)
            ->setTable($tb['data_table'])
            ->setMaps($tb['field_maps'])
            ->newQuery();
    }

    /**
     * Get data for current app in 'correspondence tables'.
     *
     * @return array
     */
    public function appDatas()
    {
        return (array)$this->app;
    }

    /**
     * Get mappings from cache or build them.
     *
     * @param string $table
     * @return mixed
     */
    private function getTableWithMaps(string $table)
    {
        if (empty($this->tables_cache[$table])) {
            $this->tables_cache[$table] = $this->buildMaps($table);
        }

        return $this->tables_cache[$table];
    }

    /**
     * Build mapping for selected Table.
     *
     * @param string $table
     * @return array
     */
    private function buildMaps(string $table)
    {
        $app_table = DB::connection($this->connection_sys)
            ->table($this->tables_tb)
            ->where('correspondence_app_id', $this->app->id)
            ->where('app_table', $table)
            ->first();

        $app_fields = DB::connection($this->connection_sys)
            ->table($this->fields_tb)
            ->where('correspondence_app_id', $this->app->id)
            ->where('correspondence_table_id', $app_table->id)
            ->whereNotNull('data_field')
            ->get();

        $maps = [];
        foreach ($app_fields as $app_field) {
            $maps[$app_field->app_field] = $app_field->data_field;
        }

        return [
            'data_table' => $app_table->data_table,
            'field_maps' => $maps,
        ];
    }
}