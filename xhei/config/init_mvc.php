<?php  defined('HEI_PATH') || exit('No direct script access allowed');

return array(
    'default_controller' => 'site', // 进入MVC模式

    // application components
    'components' => array(
        'core' => array(
            // uri 处理
            array('URI', array(
                'url_format'=>'get', // 可选: post, fget(first GET), get, post
                // post模式 dome
                // 'url_format'=>'post',
                // 'rules'=>array(
                // '/^(?P<controller>\w+)\/(?P<action>\w+)\/$/u' => '<controller>/<action>',
                // ),
            )),

            // MVC模式下 安全过滤应放在核心(core)组件最末尾
            array('Input', array(
                'filtering' => array(
                    'data' => array(&$_GET, &$_POST, &$_REQUEST), // 放置引用
                    'method' => array('htmlspecialchars', 'trim')
                )
            ))
        )
    )
);

/* End of file config_mvc.php */