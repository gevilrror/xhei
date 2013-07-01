<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @author GevilRror
 */
class HEI_Controller {

    private $load;

    /**
     * @var string the name of the default action. Defaults to 'index'.
     */
    public $defaultAction = 'index';

    /**
     * Constructor
     */
    public function __construct()
    {
        hei::trace('Controller Class Initialized', array('core.Controller', 'debug'));
    }

    /**
     * Initializes the controller.
     * This method is called by the application before the controller starts to execute.
     * You may override this method to perform the needed initialization for the controller.
     */
    public function init()
    {
    }

    /**
     * Runs the named action.
     * Filters specified via {@link filters()} will be applied.
     * @param string $action_id action ID
     * @throws CHttpException if the action does not exist or the action name is not proper.
     * @see filters
     * @see createAction
     * @see runAction
     */
    public function run($action_id)
    {
        if ($action_id === '')
        {
            $action_id = $this->defaultAction;
        }

        $action = 'action'.$action_id;

        if (method_exists($this, $action))
        {
            hei::trace('BeforeControllerAction', array('core.Controller', 'debug'));

            $this->$action();

            hei::trace('AfterControllerAction', array('core.Controller', 'debug'));
        }
        else
        {
            hei::show_404('The system is unable to find the requested action "'.$action_id.'".');
        }
    }

    public function model($model)
    {
        return hei::load($model.'Model', 'models');
    }

    public function library($library)
    {
        return hei::load($library, 'libraries');
    }

    public function view($view, $data = array())
    {
        return hei::load('view')->load($view, $data);
    }
}
// END Controller class

/* End of file Controller.php */