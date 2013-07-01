<?php
/**
 * xhei/hei.php
 * HEI
 *
 * @author GevilRror
 */

/*
 *---------------------------------------------------------------
 * BEGIN TIME
 *---------------------------------------------------------------
 */
defined('HEI_BEGIN_TIME') or define('HEI_BEGIN_TIME', microtime(true));

/*
 *---------------------------------------------------------------
 * PATHS
 *---------------------------------------------------------------
 */
defined('HEI_PATH') or define('HEI_PATH', dirname(__FILE__));
defined('SYS_PATH') or define('SYS_PATH', defined('APP_PATH') ? (APP_PATH.DIRECTORY_SEPARATOR.'system') : HEI_PATH);

/*
 *---------------------------------------------------------------
 * LOAD THE BOOTSTRAP FILE
 *---------------------------------------------------------------
 *
 * And away we go...
 *
 */
require(HEI_PATH.'/HEIBase.php');


class hei extends HEIBase
{
}

/* End of file index.php */