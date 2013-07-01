<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * xhei/core/Benchmark.php
 * 基准类
 * 利用 trace 回调 记录所有trace
 *
 * @author GevilRror
 */

class HEI_Benchmark {

    /**
     * List of all benchmark markers and when they were added
     *
     * @var array
     */
    private static $marker = array();


    /**
     * Constructor
     */
    public function __construct()
    {
        hei::add_trace_cb(array('HEI_Benchmark', 'mark'));
        hei::trace("Benchmark Class Initialized", 'core.Benchmark', 'debug');
    }

    // --------------------------------------------------------------------

    /**
     * Set a benchmark marker
     *
     * Multiple calls to this function can be made so that several
     * execution points can be timed
     *
     * @access    public
     * @param    string    $name    name of the marker
     * @return    void
     */
    public static function mark($message)
    {
        self::$marker[strtr($message['msg'], ' ', '_')] = array('microtime' => microtime(), 'memory_usage' => memory_get_usage(), 'memory_usage_peak' => memory_get_peak_usage());
    }

    function get_mark()
    {
        return self::$marker;
    }

}

// END CI_Benchmark class

/* End of file Benchmark.php */