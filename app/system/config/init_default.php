<?php  defined('HEI_PATH') || exit('No direct script access allowed');

return array(
    // application components
    'components' => array(
        'core' => array(
            // 简单模式下 仅进行安全过滤
            array('Input', array(
                'filtering' => array(
                    'data' => array(&$_GET, &$_POST, &$_REQUEST), // 放置引用
                    'method' => array('htmlspecialchars', 'trim')
                )
            )),
            // 设置默认 uft-8 字符
            array('Output', array(
                'charset' => 'utf-8'
            ))
        )
    )
);

/* End of file config_default.php */