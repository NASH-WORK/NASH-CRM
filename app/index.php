<?php
/**
 * 入口文件
 * 
 * @var unknown_type
 */

//设定全局变量APPPATH
define('APPPATH', dirname(__FILE__) . '/');

//设定框架目录SYSPATH，本机测试环境使用第二个定义，正式环境适应第一个
define('SYSPATH', dirname(__FILE__) . '/../framework/');

#规定时间格式
define('TIMESTYLE', 'Y-m-d H:i:s');
define('DAYSTYLE', 'Y-m-d');

#注册自动加载方法
function __autoload($class) {
    if (strpos($class, 'controller') && file_exists(APPPATH.'controllers/'.$class.'.php')) require APPPATH.'controllers/'.$class.'.php';
    if (strpos($class, 'model') && file_exists(APPPATH.'models/'.$class.'.php')) require APPPATH.'models/'.$class.'.php';
}

require SYSPATH . 'core/F.php';

//加载config文件,正式环境配置在config下，开发环境配置在config-dev下
#F::run(dirname(__FILE__) . '/config');
F::run(dirname(__FILE__) . '/config-dev');

