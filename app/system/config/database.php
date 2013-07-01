<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
*/

$db['default'] = array(
    'dbname' => 'default',
    'server' => array(
        'read' => array(
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => 'test',
            'port' => ''
        ),
        'write' => array(
            'hostname' => 'localhost',
            'username' => 'test',
            'password' => 'XhJ5bQ29BYY7NxF3',
            'database' => 'test11',
            'port' => ''
        ),
    ),
    'dbmethod' => 'base', // base / normal / active_rec : Active Record
    'dbdriver' => 'mysql',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => TRUE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'autoinit' => TRUE,
    'stricton' => FALSE
);


/* End of file database.php */