<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * CodeIgniter Application Controller Class
 *
 * This class object is the super class that every library in
 * CodeIgniter will be assigned to.
 *
 * @author GevilRror
 */

class HEI_WebApplication {

    private $_controller;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Controller
        require(HEI_PATH.'/core/Controller.php');
        hei::trace('Controller Class Loaded', array('core.WebApplication', 'debug'));

        // Model
        require(HEI_PATH.'/core/Model.php');
        hei::trace('Model Class Loaded', array('core.WebApplication', 'debug'));

        hei::set_app($this);

        $this->default_controller = strtolower(hei::config_item('default_controller'));

        hei::trace('WebApplication Class Initialized', 'core.WebApplication', 'debug');
    }


    /**
     * Runs the application.
     * This method loads static application components. Derived classes usually overrides this
     * method to do more application-specific tasks.
     * Remember to call the parent implementation so that static application components are loaded.
     */
    public function run()
    {
        hei::trace('On Begin Request', 'core.WebApplication', 'debug');

        $this->process_request();

        hei::trace('On End Request', 'core.WebApplication', 'debug');
    }


    /**
     * Processes the current request.
     * It first resolves the request into controller and action,
     * and then creates the controller to perform the action.
     */
    public function process_request()
    {
        $route = hei::load('URI')->parse_url();

        $this->run_controller($route);
    }

    /**
     * Creates the controller and performs the specified action.
     * @param string $route the route of the current request. See {@link createController} for more details.
     * @throws CHttpException if the controller could not be created.
     */
    public function run_controller($route)
    {
        if (($ca = $this->create_controller($route)) !== null)
        {
            list($controller, $action_id) = $ca;
            $old_controller = $this->_controller;
            $this->_controller = $controller;
            $controller->init();
            $controller->run($action_id);
            $this->_controller = $old_controller;
        }
        else
        {
            hei::show_404('Unable to resolve the request "'.($route === '' ? $this->default_controller : $route).'"');
        }
    }

    /**
     * Creates a controller instance based on a route.
     * The route should contain the controller ID and the action ID.
     * It may also contain additional GET variables. All these must be concatenated together with slashes.
     *
     * This method will attempt to create a controller in the following order:
     * <ol>
     * <li>If the first segment is found in {@link controllerMap}, the corresponding
     * controller configuration will be used to create the controller;</li>
     * <li>If the first segment is found to be a module ID, the corresponding module
     * will be used to create the controller;</li>
     * <li>Otherwise, it will search under the {@link controllerPath} to create
     * the corresponding controller. For example, if the route is "admin/user/create",
     * then the controller will be created using the class file "protected/controllers/admin/UserController.php".</li>
     * </ol>
     * @param string $route the route of the request.
     * @param CWebModule $owner the module that the new controller will belong to. Defaults to null, meaning the application
     * instance is the owner.
     * @return array the controller instance and the action ID. Null if the controller class does not exist or the route is invalid.
     */
    public function create_controller($route, $owner = null)
    {
        if ($owner===null)
        {
            $owner=$this;
        }

        if ($route === '')
        {
            $route = $owner->default_controller;
        }

        $route .= '/';
        while(($pos = strpos($route, '/')) !== false)
        {
            $id = strtolower(substr($route, 0, $pos));

            if ( ! preg_match('/^\w+$/', $id))
            {
                return null;
            }

            $route = (string)substr($route, $pos+1);

            if ( ! isset($base_path))  // first segment
            {
                $base_path = SYS_PATH.'/controllers';
            }

            $class_name = ucfirst($id).'Controller';
            $class_file = $base_path.'/'.$class_name.'.php';

            if (is_file($class_file))
            {
                if ( ! class_exists($class_name, false))
                {
                    require($class_file);
                }

                if (class_exists($class_name, false) && is_subclass_of($class_name, 'HEI_Controller'))
                {
                    return array( new $class_name(), $this->parse_action_params($route));
                }

                return null;
            }

            $base_path .= '/'.$id;

            if ( ! is_dir($base_path))
            {
                return null;
            }
        }

        return null;
    }

    /**
     * Parses a path info into an action ID and GET variables.
     * @param string $path_info path info
     * @return string action ID
     */
    protected function parse_action_params($path_info)
    {
        if (($pos = strpos($path_info, '/')) !== false)
        {
            $action_id = substr($path_info, 0, $pos);
            return strtolower($action_id);
        }
        else
        {
            return $path_info;
        }
    }
}
// END WebApplication class

/* End of file WebApplication.php */