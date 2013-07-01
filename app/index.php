<?php
/**
 * app/index.php
 * 示例
 *
 * @author GevilRror
 */

var_dump('START: mu['.memory_get_usage().']mup['.memory_get_peak_usage().']');

/**
 *---------------------------------------------------------------
 * ERROR REPORTING
 *---------------------------------------------------------------
 */
error_reporting(E_ALL);
//error_reporting(0);

/**
 *---------------------------------------------------------------
 * Set the main path constants
 *---------------------------------------------------------------
 */

//app path
define('APP_PATH',dirname(__FILE__));

/**
 * ---------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 * ---------------------------------------------------------------
 * 加载xhei
 */

require_once(dirname(__FILE__).'/../xhei/hei.php');

// config
$config = array(
    'default_controller' => 'post', // 进入MVC模式

    // application components
    'components' => array(
        'core' => array(
            'Benchmark',
            array('URI', array(
                //'urlFormat'=>'post',
                'urlFormat'=>'fget', // first GET
                'rules'=>array(
                    '/^post\/(?P<id>\d+)$/u' => 'post/view/sss',
                    '/^posts\/(?P<tag>.*?)\/$/u' => 'post/index',
                    '/^(?P<controller>\w+)\/(?P<action>\w+)\/$/u' => '<controller>/<action>',
                ),
            )),
            array('Input', array(
                'filtering'=>array(
                    'data' => array(&$_GET, &$_POST, &$_REQUEST),
                    'method' => array('htmlspecialchars', 'trim')
                )
            ))
            /*
            'URI',
            'Utf8',
            'Config',
            'Exceptions',
            'Router',
             array('Output', array('display_cache' => TRUE, 'a' => 2)),
            'Security',
            'Input',
            'Lang',
            'Loader',*/

        ),
        'lib' => array(
            //'Log' => ''
        ),
    ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    //'params'=>require(dirname(__FILE__).'/params.php'),
);

header("Content-type: text/html; charset=utf-8");

var_dump('preINIT: mu['.memory_get_usage().']mup['.memory_get_peak_usage().']');

hei::init();
// hei::init()->run();
// hei::init('mvc')->run();
// hei::init($config)->run();


var_dump('INIT: mu['.memory_get_usage().']mup['.memory_get_peak_usage().']');

//hei::show_404('This is 404');
//hei::init($config)->run();

//var_dump(hei::db()->query('SELECT * FROM test'));
//var_dump(hei::db()->query('INSERT INTO  `test`.`test11` (`name` ,`title` ,`email`) VALUES (\'23\',  \'23\',  \'23\');'));

//hei::load('output')->last_modified('20130505 1850');

//hei::load('view')->load('welcome_message', array('aa'=>'1','bb'=>'2'));

var_dump('INIT: mu['.memory_get_usage().']mup['.memory_get_peak_usage().']');

//var_dump(hei::load('Benchmark')->get_mark());














