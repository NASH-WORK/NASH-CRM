<?php
#配置文件
#包含数据库配置信息, debug开关, cache配置信息
#日志配置信息, 运行环境信息
return array(
	'debug' => TRUE,
	#数据库配置文件
	'db' => array(
		'host'    => '127.0.0.1',
		'port'    => '3306',
		'user'    => 'database user name',
		'pass'    => 'database user password',
		'db'      => 'database name',
		'charset' => 'utf8',
	),

	#缓存服务器配置文件
	'cache_on' => false,
	'cache' => array(
		array(
			'host'    => '127.0.0.1',
			'port'    => 11211,
			'weight'  => 100,
		)
	),

	#redis客户端
	'redis' => array(
	   'isOn' => TRUE,
	   'host' => '127.0.0.1',
	   'port' => 6379
	),

	#运行日志配置文件
	'log' => array(
		'level'   => E_ALL,
		'error'   => 'error_log path',
		'debug'   => 'debug_log path',
	),

	#运行环境配置
	'isTest' => TRUE,
);
