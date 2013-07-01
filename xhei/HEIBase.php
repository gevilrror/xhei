<?php  defined('HEI_PATH') || exit('No direct script access allowed');
/**
 * xhei/HEIBase.php
 * 框架基础类
 * 实现 类基础加载、配置加载、动作监听回调、整体App配置、错误显示(依赖 Exceptions 核心类)
 *
 * @author GevilRror
 */

//版本
define('HEI_VERSION', '1.0.0');

//基础类
class HEIBase
{
    //类
    private static $_class_map      = array();
    //已加载的类
    private static $_class_loaded   = array();

    //基础配置
    private static $_config         = NULL;

    //trace's call back
    private static $_trace_CB       = array();

    //数据库
    public static $_DB              = NULL;

    //app
    private static $_app            = NULL;


    //回调 app
    public static function &app()
    {
        return self::$_app;
    }

    //设置 app
    public static function set_app(& $app)
    {
        if (self::$_app !== NULL)
        {
            self::trace("Hei Application Can Only Be Created Once", 'HEIBase', 'error');
            return FALSE;
        }

        self::$_app =& $app;

        self::trace("Hei Application Created", 'HEIBase', 'debug');
        return TRUE;
    }

    //初始化
    public static function &init($config = 'default')
    {
        //设置配置
        self::config($config);

        //加载核心类
        self::load_core_components();

        self::trace("loaded Core Components", 'HEIBase', 'debug');

        //setApplication
        if (isset(self::$_config['default_controller']))
        {
            self::load('WebApplication');
            self::trace("loaded Controller", 'HEIBase', 'debug');
        }

        //加载预置类
        self::preload_components();

        self::trace("loaded Preload Components", 'HEIBase', 'debug');

        //用于MVC，返回后运行
        return self::$_app;
    }

    //加载核心类
    protected static function load_core_components()
    {
        if ( ! isset(self::$_config['components']['core']))
        {
            return FALSE;
        }

        return self::load_components(self::$_config['components']['core'], 'core');
    }

    //加载预置类
    protected static function preload_components()
    {
        if ( ! isset(self::$_config['components']))
        {
            return FALSE;
        }

        $cpns = self::$_config['components'];
        unset($cpns['core']);

        foreach ($cpns as $type => &$components)
        {
            self::load_components($components, $type);
        }

        return TRUE;
    }

    //模块加载
    public static function load_components($components, $type)
    {
        if ( ! is_array($components))
        {
            return FALSE;
        }

        foreach ($components as $args)
        {
            if ( ! is_array($args))
            {
                $args = array($args);
            }

            $id = array_shift($args);

            //待扩展：前缀？
            self::load($id, $type, array_shift($args));
        }

        return TRUE;
    }

    //类加载
    public static function &load($class, $directory = 'core', $args = array(), $prefix = '', $instantiate = TRUE)
    {
        // directory
        if (($pos = strrpos($class, '/')) !== false)
        {
            $directory .= '/'.strtr(substr($class, 0, $pos), array("./"=>""));
            $class = substr($class, $pos+1);
        }

        $class = ucfirst($class);

        // Does the class exist?  If so, we're done...
        if (isset(self::$_class_map[$class]))
        {
            return self::$_class_map[$class];
        }

        $name = FALSE;

        if ((isset(self::$_class_loaded[$class]) && self::$_class_loaded[$class] === TRUE) || class_exists($prefix.$class))
        {
            $name = $prefix.$class;
        }
        else
        {
            self::$_class_loaded[$class] = FALSE;

            // Look for the class first in the local application/libraries folder
            // then in the native system/libraries folder
            foreach (array(SYS_PATH, HEI_PATH) as $path)
            {
                if (file_exists($path.'/'.$directory.'/'.$class.'.php'))
                {
                    //待扩展：继承？
                    require($path.'/'.$directory.'/'.$class.'.php');
                    $name = $path == HEI_PATH ? 'HEI_'.$class : $prefix.$class;
                    self::$_class_loaded[$class] = TRUE;

                    break;
                }
            }

        }

        // Did we find the class?
        if ($name === FALSE)
        {
            // Note: We use exit() rather then show_error() in order to avoid a
            // self-referencing loop with the Excptions class
            exit('Unable to locate the specified class: '.$class.'.php');
        }

        //实例化
        if ($instantiate === TRUE)
        {
            if ( ! empty($args) && is_array($args))
            {
                self::$_class_map[$class] =  new $name($args);
            }
            else
            {
                self::$_class_map[$class] =  new $name();
            }

            return self::$_class_map[$class];
        }

        return self::$_class_loaded[$class];
    }

    //配置处理
    public static function &config($config)
    {

        //配置已经加载，直接返回所有配置
        if (isset(self::$_config))
        {
            return self::$_config;
        }

        // 配置是路径？
        if ( is_string($config) )
        {
            // Fetch the config file
            if (file_exists($path = SYS_PATH.'/config/init_'.$config.'.php') // 在应用配置文件内?
                || file_exists($path = HEI_PATH.'/config/init_'.$config.'.php') // HEI 默认配置
                || file_exists($path = $config)) //绝对路径
            {
                $config = require($path);
            }
            else
            {
                exit('The configuration file does not exist.');
            }
        }

        // Does the $config array exist in the file?
        if ( ! is_array($config))
        {
            exit('Your config file does not appear to be formatted correctly.');
        }

        self::$_config = $config;

        return self::$_config;
    }

    //获取单项配置
    public static function config_item($item)
    {

        if ( ! isset(self::$_config[$item]))
        {
              return FALSE;
        }

        return self::$_config[$item];
    }

    //数据库
    public static function &db($params = '', $method = '')
    {
        //未加载 Database 处理核心类
        if ( ! isset(self::$_DB[$params]))
        {
            self::load('Database')->init($params, $method);
        }

        if ($params == '')
        {
            reset(self::$_DB);
            $params = key(self::$_DB);
        }

        return self::$_DB[$params];
    }

    //显示错误
    public static function show_error($message, $status_code = 500, $heading = 'An Error Was Encountered')
    {
        self::trace($message, array($heading.$status_code, 'error'));
        self::load('Exceptions')->show_error($heading, $message, 'error_general', $status_code);
        exit;
    }

    //返回404
    public static function show_404($page = '', $log_error = TRUE)
    {
        self::trace($log_error, array($page, '404'));
        self::load('Exceptions')->show_404($page, $log_error);
        exit;
    }

    //trace
    public static function trace($msg, $file = '', $type = '', $extra = '')
    {
        $message = array(
            'msg'   => $msg,
            'file'  => $file,
            'type'  => $type,
            'extra' => $extra);

        //调用回调函数
        foreach(self::$_trace_CB as $callback)
        {
            call_user_func_array($callback, array($message));
        }

        return TRUE;
    }

    //为记录添加回调函数
    //记录为空时，添加整体回调函数
    public static function add_trace_cb($func)
    {
        return self::$_trace_CB[] = $func;
    }
}

/* End of file HEIBase.php */