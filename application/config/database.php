<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|				 (and in table creation queries made with DB Forge).
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'default';
$active_record = TRUE;

$db['default']['hostname'] = 'localhost';
$db['default']['username'] = 'innterface_lab';
$db['default']['password'] = 'fACe7vHLB3AvU8Gt';
$db['default']['database'] = 'innterface';
$db['default']['dbdriver'] = 'mysqli';
$db['default']['dbprefix'] = '';
$db['default']['pconnect'] = TRUE;
$db['default']['db_debug'] = TRUE;
$db['default']['cache_on'] = FALSE;
$db['default']['cachedir'] = '';
$db['default']['char_set'] = 'utf8';
$db['default']['dbcollat'] = 'utf8_general_ci';
$db['default']['swap_pre'] = '';
$db['default']['autoinit'] = TRUE;
$db['default']['stricton'] = FALSE;

$db['lab']['hostname'] = 'innterface-lab-db.chsmrhfotli9.us-east-1.rds.amazonaws.com';
$db['lab']['username'] = 'innterface_lab';
$db['lab']['password'] = 'fACe7vHLB3AvU8Gt';
$db['lab']['database'] = 'innterface_lab';
$db['lab']['dbdriver'] = 'mysqli';
$db['lab']['dbprefix'] = '';
$db['lab']['pconnect'] = TRUE;
$db['lab']['db_debug'] = TRUE;
$db['lab']['cache_on'] = FALSE;
$db['lab']['cachedir'] = '';
$db['lab']['char_set'] = 'utf8';
$db['lab']['dbcollat'] = 'utf8_unicode_ci';
$db['lab']['swap_pre'] = '';
$db['lab']['autoinit'] = TRUE;
$db['lab']['stricton'] = FALSE;

$db['dev']['hostname'] = 'innterface-dev-db.chsmrhfotli9.us-east-1.rds.amazonaws.com';
$db['dev']['username'] = 'innterface_lab';
$db['dev']['password'] = 'KrXBzMhUQFX64DMC';
$db['dev']['database'] = 'innterface';
$db['dev']['dbdriver'] = 'mysqli';
$db['dev']['dbprefix'] = '';
$db['dev']['pconnect'] = FALSE; // 第二個以後的DB要設FALSE，不然會有問題
$db['dev']['db_debug'] = TRUE;
$db['dev']['cache_on'] = FALSE;
$db['dev']['cachedir'] = '';
$db['dev']['char_set'] = 'utf8';
$db['dev']['dbcollat'] = 'utf8_general_ci';
$db['dev']['swap_pre'] = '';
$db['dev']['autoinit'] = TRUE;
$db['dev']['stricton'] = FALSE;

$db['ios']['hostname'] = 'interface-crawler-db.chsmrhfotli9.us-east-1.rds.amazonaws.com';
$db['ios']['username'] = 'innterface_lab';
$db['ios']['password'] = 'KTJC75TKDGpeZuCP';
$db['ios']['database'] = 'innterface_crawler';
$db['ios']['dbdriver'] = 'mysqli';
$db['ios']['dbprefix'] = '';
$db['ios']['pconnect'] = FALSE; // 第二個以後的DB要設FALSE，不然會有問題
$db['ios']['db_debug'] = TRUE;
$db['ios']['cache_on'] = FALSE;
$db['ios']['cachedir'] = '';
$db['ios']['char_set'] = 'utf8';
$db['ios']['dbcollat'] = 'utf8_general_ci';
$db['ios']['swap_pre'] = '';
$db['ios']['autoinit'] = TRUE;
$db['ios']['stricton'] = FALSE;

$db['android']['hostname'] = 'interface-crawler-android-db.chsmrhfotli9.us-east-1.rds.amazonaws.com';
$db['android']['username'] = 'innterface_lab';
$db['android']['password'] = 'VVytfpCM8GApQGz8';
$db['android']['database'] = 'mydb';
$db['android']['dbdriver'] = 'mysqli';
$db['android']['dbprefix'] = '';
$db['android']['pconnect'] = FALSE; // 第二個以後的DB要設FALSE，不然會有問題
$db['android']['db_debug'] = TRUE;
$db['android']['cache_on'] = FALSE;
$db['android']['cachedir'] = '';
$db['android']['char_set'] = 'utf8';
$db['android']['dbcollat'] = 'utf8_unicode_ci';
$db['android']['swap_pre'] = '';
$db['android']['autoinit'] = TRUE;
$db['android']['stricton'] = FALSE;

/* End of file database.php */
/* Location: ./application/config/database.php */