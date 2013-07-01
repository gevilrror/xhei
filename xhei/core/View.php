<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * View Class
 *
 * xhei/core/View.php
 *
 * @author GevilRror
 */
class HEI_View {

    /**
     * List of paths to load views from
     *
     * @var array
     * @access protected
     */
    protected $_hei_view_paths        = array();

    /**
     * List of cached variables
     *
     * @var array
     * @access protected
     */
    protected $_hei_cached_vars        = array();


    /**
     * Constructor
     *
     * Sets the path to the view files and gets the initial output buffering level
     */
    public function __construct()
    {
        //$this->output =& hei::load('Output');

        $this->_hei_view_paths = array(SYS_PATH.'/views/');

        hei::trace('View Class Initialized', 'core.View', 'debug');

    }

    /**
     * Loader
     *
     * This function is used to load views and files.
     * Variables are prefixed with _hei_ to avoid symbol collision with
     * variables made available to view files
     *
     * @param    array
     * @return    void
     */
    public function load($_hei_view, $_hei_vars = array(), $_hei_return = FALSE)
    {
        // FE : file exists
        $_hei_FE = FALSE;

        $_hei_ext = pathinfo($_hei_view, PATHINFO_EXTENSION);

        $_hei_file = ($_hei_ext == '') ? $_hei_view.'.php' : $_hei_view;

        // VP : view path
        foreach ($this->_hei_view_paths as $_p)
        {
            if (is_file($_p.$_hei_file))
            {
                $_hei_path = $_p.$_hei_file;
                $_hei_FE = TRUE;
                break;
            }
        }


        if ( ! $_hei_FE)
        {
            hei::show_error('Unable to load the requested file: '.$_hei_file);
        }

        /*
         * Extract and cache variables
         *
         * You can either set variables using the dedicated $this->load_vars()
         * function or via the second parameter of this function. We'll merge
         * the two types and cache them so that views that are embedded within
         * other views can have access to these variables.
         */
        if (is_array($_hei_vars))
        {
            $this->_hei_cached_vars = array_merge($this->_hei_cached_vars, $_hei_vars);
        }

        extract($this->_hei_cached_vars, EXTR_SKIP);

        /*
         * Buffer the output
         *
         * We buffer the output for two reasons:
         * 1. Speed. You get a significant speed boost.
         * 2. So that the final rendered template can be
         * post-processed by the output class.  Why do we
         * need post processing?  For one thing, in order to
         * show the elapsed page load time.  Unless we
         * can intercept the content right before it's sent to
         * the browser and then stop the timer it won't be accurate.
         */
        if ($_hei_return)
        {
            ob_start();
        }
        //

        // If the PHP installation does not support short tags we'll
        // do a little string replacement, changing the short tags
        // to standard PHP echo statements.

        include($_hei_path); // include() vs include_once() allows for multiple views with the same name

        hei::trace('File loaded: '.$_hei_path, 'core.View', 'debug');

        // Return the file data if requested
        if ($_hei_return)
        {
            $buffer = ob_get_contents();
            @ob_end_clean();
            return $buffer;
        }
    }
}

/* End of file View.php */