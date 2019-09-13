# tablda_data_receiver
Component for receiving data in TablDA Apps.

For Laravel 5+ only.



Installation:

1) composer require andreykurmel/tablda_data_receiver

2) fill .env file like this:

/* required */

TABLDA_APP_NAME={app name in 'correspondence apps'}

TABLDA_SYS_CONN=tablda_sys

TABLDA_DATA_CONN=tablda_data

/* not required */

TABLDA_APPS_TB=correspondence_apps

TABLDA_TABLES_TB=correspondence_tables

TABLDA_FIELDS_TB=correspondence_fields

3) create connection "tablda_sys" with access to 'correspondence tables db' and empty connection "tablda_data"

4) add "Tablda\DataReceiver\TabldaDataServiceProvider::class" to config/app.php ['providers']


Example of using:

//get data:

$table = app(TabldaDataInterface::class)->tableReceiver('members');
$table->where('ID', 1)->get();
            
//update data:

$table = app(TabldaDataInterface::class)->tableReceiver('members');
$table->where('ID', 1)->update(['field' => 12]);