<?php

/**
 * 框架主体
 * 
 * @author reckfull <ruckfull@gmail.com>
 * @version 2.0
 */
class F
{
	private static $_config_dir = '../app/config/';
	private static $_config = array();
	private static $_loaded_config = array();
	private static $_loaded_component = array();
	private static $_loaded_controller = array();
	private static $_loaded_base_controller = FALSE;
	private static $_loaded_model = array();
	private static $_loadedClass = array();
	private static $_loaded_base_model = FALSE;
	private static $_instance = array();
	
	public static $global = array();
	
	public static function run($config_dir)
	{
		self::$_config_dir = $config_dir;
		self::load_component('core');
		self::load_component('util');
		self::$_config = self::load_config('config.php');
		self::init();
		self::route();
	}
	
	/**
	 * 初始化过程, 设定错误级别, 注册register_shutdown_function
	 * 
	 * @access public
	 */
	public static function init()
	{
		if(self::$_config['debug'])
		{
			error_reporting(E_ALL);
		}
		else
		{
			error_reporting(0);
		}
		if(isset(self::$_config['log']['level']))
		{
			set_error_handler(array(__CLASS__, 'error_handler'), self::$_config['log']['level']);
			register_shutdown_function(array(__CLASS__, 'shutdown_handler'));
		}
	}
	
	public static function route()
	{
		try
		{
			$route = explode('/', F::request('r'));
			if(count($route) == 2)
			{
				try
				{
					$module = F::load_controller($route[0]);
				}
				catch(Exception $e)
				{
					F::log()->log_debug($e);
					if ($e->getCode()) $rest = F::rest()->show_error($e->getCode(), $e->getMessage());
					else $rest = F::rest()->show_error(108);
				}
				
				if(method_exists($module, $route[1]))
				{
					try
					{
						$module->$route[1]();
					}
					catch(Exception $e)
					{
						F::log()->log_debug($e);
						if ($e->getCode()) $rest = F::rest()->show_error($e->getCode(), $e->getMessage());
						else $rest = F::rest()->show_error(200, $e->getMessage());
					}
				}
				else
				{
					throw new Exception('Function Not Found');
				}
			}
			else
			{
				throw new Exception('Route error.');
			}
		}
		catch(Exception $e)
		{
			F::log()->log_debug($e);
			$rest = F::rest()->show_error(108);
		}
	}
	
	public static function get($name, $default_value = '')
	{
		return self::request($name, $default_value, $_GET);
	}
	
	public static function post($name, $default_value = '')
	{
		return self::request($name, $default_value, $_POST);
	}
	
	public static function cookie($name, $default_value = '')
	{
		return self::request($name, $default_value, $_COOKIE);
	}
	
	public static function files($name)
	{
		return self::request($name, NULL, $_FILES);
	}
	
	public static function request($name, $default_value = '', $params = NULL)
	{
		if(!$params) $params = $_REQUEST;
		if(isset($params[$name]))
		{
			return $params[$name];
		}
		else
		{
			return $default_value;
		}
	}
	
	public static function all_gets()
	{
		return $_GET;
	}
	
	public static function all_posts()
	{
		return $_POST;
	}
	
	public static function all_requests()
	{
		return $_REQUEST;
	}
	
	/**
	 * 加载controller
	 * 
	 * @access public
	 * @param string $controller
	 * @throws Exception
	 * @return multitype:
	 */
	public static function load_controller($controller)
	{
		$controller_name = "{$controller}_controller";
		if(!self::$_loaded_base_controller)
		{
			$file_path = APPPATH . 'controllers/base_controller.php';
			if(file_exists($file_path)) require($file_path);
			self::$_loaded_base_controller = TRUE;
		}
		if(!isset(self::$_loaded_controller[$controller]) && $controller != 'base')
		{
			$file_path = APPPATH . 'controllers/' . $controller . '_controller.php';
			if(file_exists($file_path))
			{
				require($file_path);
				self::$_loaded_controller[$controller] = new $controller_name;
				return self::$_loaded_controller[$controller];
			}
			else
			{
				throw new Exception('Controller not found.');
			}
		}
		else
		{
			return self::$_loaded_controller[$controller];
		}
	}
	
	/**
	 * 加载一个model
	 * 
	 * @access public
	 * @param string $model
	 * @throws Exception
	 * @return multitype:
	 */
	public static function load_model($model, array $param)
	{
		$model_name = "{$model}_model";
		if(!self::$_loaded_base_model)
		{
			$file_path = APPPATH . 'models/base_model.php';
			if(file_exists($file_path)) require($file_path);
			self::$_loaded_base_model = TRUE;
		}
		if(!isset(self::$_loaded_model[$model]))
		{
			$file_path = APPPATH . 'models/' . $model . '_model.php';
			if(file_exists($file_path))
			{
				if ($model != 'base') require($file_path);
				self::$_loaded_model[$model] = new $model_name($param);
				return self::$_loaded_model[$model];
			}
			else
			{
				throw new Exception('Model not found.', 200);
			}
		}
		else
		{
			return self::$_loaded_model[$model];
		}
	}
	
	/**
	 * 加载一个view层
	 * 
	 * @access public
	 * @param string $view
	 * @param array $var_array 传递到加载view层的数据数组
	 */
	public static function load_view($view, $var_array = array())
	{
		extract($var_array);
		require APPPATH . "views/$view";
	}
	
	public static function load_config($config_file)
	{
		if(!isset(self::$_loaded_config[$config_file]))
		{
			$file_path = self::$_config_dir . '/' . $config_file;
			if(file_exists($file_path))
			{
				$config = require($file_path);
				self::$_loaded_config[$config_file] = $config;
				return $config;
			}
			else
			{
				throw new Exception('Config not found.');
			}
		}
		else
		{
			return self::$_loaded_config[$config_file];
		}
	}
	
	public static function load_component($component)
	{
		if(!isset(self::$_loaded_component[$component]))
		{
			if(isset(self::$_component[$component]))
			{
				foreach(self::$_component[$component] as $file)
				{
					$file_path = dirname(__FILE__) . '/../' . $file;
					if(file_exists($file_path))
					{
						require($file_path);
						self::$_loaded_component[$component] = TRUE;
					}
					else
					{
						throw new FException("Failed to load component [$component].");
					}
				}
			}
			else
			{
				throw new FException("Component [$component] not exists.");
			}
		}
	}

	/**
	 * 获取db操作类
	 * 
	 * @access public
	 * @static
	 * @return FDBConnection
	 */
	public static function db()
	{
		self::load_component('db');
		$dbconfig = self::$_config['db'];
		if(!isset(self::$_instance['db']))
		{
			self::$_instance['db'] = new FDBConnection($dbconfig['host'], $dbconfig['user'], $dbconfig['pass'], $dbconfig['db'], $dbconfig['port'], $dbconfig['charset']);
			self::$_instance['db']->init();
		}
		return self::$_instance['db'];
	}
	
	/**
	 * 加载redis客户端
	 * 
	 * @return FRedis
	 */
	public static function redis() {
	    self::load_component('redis');
	    $redisConfig = self::$_config['redis'];
	    if (!isset(self::$_instance['redis']) && $redisConfig['isOn']) {
	        self::$_instance['redis'] = new FRedis($redisConfig['host'], $redisConfig['port']);
	    }
	    return self::$_instance['redis'];
	}
	
	public static function log()
	{
		self::load_component('log');
		$logconfig = self::$_config['log'];
		if(!isset(self::$_instance['log']))
		{
			self::$_instance['log'] = new FLog($logconfig);
		}
		return self::$_instance['log'];
	}
	
	/**
	 * 获取返回请求结果操作类
	 * 
	 * @access public
	 * @static
	 * @return FRest
	 */
	public static function rest()
	{
		self::load_component('rest');
		$restconfig = self::load_config('rest.php');
		if(!isset(self::$_instance['rest']))
		{
			self::$_instance['rest'] = new FRest($restconfig);
		}
		return self::$_instance['rest'];
	}
	
	/**
	 * 获取cache操作类
	 * 
	 * @access public
	 * @static
	 * @return FCache
	 */
	public static function cache()
	{
		self::load_component('cache');
		$cache_on = self::$_config['cache_on'];
		$cacheconfig = self::$_config['cache'];
		if(!$cache_on)
		{
			$cacheconfig = NULL;
		}
		if(!isset(self::$_instance['cache']))
		{
			self::$_instance['cache'] = new FCache($cacheconfig);
			self::$_instance['cache']->init();
		}
		return self::$_instance['cache'];
	}
	
	/* quick reach components end */
	
	public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
      self::log()->log_error($errno, $errstr, $errfile, $errline, $errcontext);
	}
	
	public static function shutdown_handler()
	{
      if(self::$_config['debug']) return;
      $error = error_get_last();
      if ($error['type'] === E_ERROR)
      {
      	header('location: ' . self::$_config['shutdown']);
      }
	}
	
	/**
	 * 根据系统配置显示调试日志
	 * @access public static
	 * @param string $logContent 调试显示信息
	 * @return boolean
	 */
	public static function showLog($logContent){
		if (self::$_config['isTest']) {
			if (is_array($logContent)) print_r($logContent);
			elseif (is_resource($logContent)) var_dump($logContent);
			elseif (is_string($logContent)) echo $logContent;
			elseif (is_object($logContent)) var_dump($logContent);
			else echo $logContent;
			echo "<hr>";
		}
		return true;
	}

	private static $_component = array(
		'assist'    => array('assist/FErrorLogReader.php', 'assist/FDebugLogReader.php'),
		'core'      => array('core/FController.php', 'core/FException.php', 'core/FModel.php'),
		'db'        => array('db/FDBConnection.php', 'db/FDBException.php', 'db/FDBQuery.php'),
		'cache'     => array('cache/FCache.php', 'cache/FCacheException.php'),
		'log'       => array('log/FLog.php'),
		'rest'      => array('rest/FRest.php'),
		'util'      => array('util/FCurl.php', 'util/FUtil.php', 'util/FValidator.php'),
	    'redis'     => array('redis/FRedis.php'),
	);
	
	/**
	 * 加载lib中的操作类
	 * 如果尝试加载虚基类, 则对应self::$_loadedClass[$className]为虚基类的名字
	 * 
	 * @access public
	 * @param string $className 预加载类名
	 * @param array $params 初始化参数数组
	 * @param boolean $isAbstract 是否是虚基类, 0-否  1-是
	 * @throws Exception
	 */
	public static function loadClass($className, $params = array(), $isAbstract = 0){
		if (!isset(self::$_loadedClass[$className])) {
			$filePath = APPPATH.'lib/'.$className.'.class.php';
			if (file_exists($filePath)) {
				require($filePath);
				if ($isAbstract) self::$_loadedClass[$className] = $className;
				else self::$_loadedClass[$className] = new $className($params);
			}else throw new Exception('class not found');
		}
		return self::$_loadedClass[$className];
	}
	
}