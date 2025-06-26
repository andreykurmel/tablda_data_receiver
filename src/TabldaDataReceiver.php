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

    protected $def_host;
    protected $def_db;
    protected $def_login;
    protected $def_pass;

    protected $app;
    protected $tables_cache = [];
    protected $settings = [];

    /**
     * TabldaDataReceiver constructor.
     *
     * @param array $settings : [
     *      '{SETTINGS NAME}' => string // usually have default values
     *      'case_sens' => bool // case sensitive field names or not
     * ]
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
        $this->setSettings();
        $this->setAppRecord();
        $this->configDataConnection();
    }

    /**
     * Set Settings for DataReceiver (step 1).
     *
     * @throws \Exception
     */
    protected function setSettings()
    {
        $this->connection_sys = $this->settings['TABLDA_SYS_CONN'] ?? 'app_sys';
        $this->connection_data = $this->settings['TABLDA_DATA_CONN'] ?? 'app_data';

        if (!$this->connection_sys || !$this->connection_data) {
            throw new \Exception('Settings for Tablda connections not found.');
        }

        $this->apps_tb = $this->settings['TABLDA_APPS_TB'] ?? 'correspondence_apps';
        $this->tables_tb = $this->settings['TABLDA_TABLES_TB'] ?? 'correspondence_tables';
        $this->fields_tb = $this->settings['TABLDA_FIELDS_TB'] ?? 'correspondence_fields';

        $this->def_host = $this->settings['DEF_HOST'] ?? '127.0.0.1';
        $this->def_db = $this->settings['DEF_DB'] ?? 'app_correspondence';
        $this->def_login = $this->settings['DEF_LOGIN'] ?? 'root';
        $this->def_pass = $this->settings['DEF_PASS'] ?? '';
    }

    /**
     * Get App Config Record for DataReceiver (step 2).
     *
     * @throws \Exception
     */
    protected function setAppRecord()
    {
        $app_name = $this->settings['TABLDA_APP_NAME'];
        $this->app = DB::connection($this->connection_sys)
            ->table($this->apps_tb)
            ->where('code', $app_name)
            ->first();

        if (!$this->app) {
            throw new \Exception('Record for Tablda application not found.');
        }

        $this->app->_tables = DB::connection($this->connection_sys)
            ->table($this->tables_tb)
            ->where('correspondence_app_id', $this->app->id)
            ->where('active', 1)
            ->get()
            ->toArray();
    }

    /**
     * Config Data Connection for DataReceiver (step 3).
     *
     * @throws \Exception
     */
    protected function configDataConnection()
    {
        $data = $this->connection_data;
        config([
            "database.connections.$data.host" => ($this->app->host ?: $this->def_host),
            "database.connections.$data.database" => ($this->app->db ?: $this->def_db),
            "database.connections.$data.username" => ($this->app->login ?: $this->def_login),
            "database.connections.$data.password" => ($this->app->pass ?: $this->def_pass),
        ]);
    }

    /**
     * Get Query with field mapping.
     *
     * @param string $app_table
     * @return DataTableReceiver
     */
    public function tableReceiver(string $app_table)
    {
        $tb = $this->getTableWithMaps($app_table);

        $model = (new TabldaTable())
            ->setConnection($this->connection_data)
            ->setTable($tb['data_table'])
            ->setMaps($tb['_app_maps']);

        return app()->make(DataTableInterface::class, [
            'model' => $model,
            'case_sens' => !empty($this->settings['case_sens'])
        ]);
    }

    /**
     * Get mappings from cache or build them.
     *
     * @param string $app_table
     * @param bool $no_cache
     * @return array
     */
    public function getTableWithMaps(string $app_table, bool $no_cache = false)
    {
        if ($no_cache || empty($this->tables_cache[$app_table])) {
            $this->tables_cache[$app_table] = $this->tableAndMaps($app_table);
        }

        return $this->tables_cache[$app_table];
    }

    /**
     * Build mapping for selected Table.
     *
     * @param string $table
     * @return array
     * @throws \Exception
     */
    protected function tableAndMaps(string $table)
    {
        $app_table = DB::connection($this->connection_sys)
            ->table($this->tables_tb)
            ->where('correspondence_app_id', $this->app->id)
            ->where('app_table', $table)
            ->whereNotNull('data_table')
            ->where(function ($q) {
                $q->whereNotIn('row_hash', ['cf_temp']);
                $q->orWhereNull('row_hash');
            })
            ->where('active', 1)
            ->first();

        if (!$app_table) {
            throw new \Exception('Table "'.$table.'" not found in "CorrespondenceTables"');
        }

        $app_fields = DB::connection($this->connection_sys)
            ->table($this->fields_tb)
            ->where('correspondence_app_id', $this->app->id)
            ->where('correspondence_table_id', $app_table->id)
            ->whereNotNull('app_field')
            ->whereNotNull('data_field')
            ->where(function ($q) {
                $q->whereNotIn('row_hash', ['cf_temp']);
                $q->orWhereNull('row_hash');
            })
            ->get();

        $maps = [
            '_id' => 'id',
            '_row_hash' => 'row_hash'
        ];
        foreach ($app_fields as $app_field) {
            $maps[ $this->t_case($app_field->app_field) ] = $this->t_case($app_field->data_field);
        }

        $app_table->_app_fields = $app_fields;
        $app_table->_app_maps = $maps;
        return (array)$app_table;
    }

    /**
     * Case sensitive or not.
     *
     * @param string $val
     * @return string
     */
    protected function t_case(string $val)
    {
        if (!empty($this->settings['case_sens'])) {
            $val = strtolower($val);
        }
        return $val;
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
}
