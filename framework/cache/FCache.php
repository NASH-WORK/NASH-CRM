<?php

/**
 * memcache 缓存封装
 * 
 * @author reckfull <ruckfull@gmail.com>
 * @version 2.0
 */
class FCache
{
	public $instance = NULL;
	
	public $hosts = array();
	
	/**
	 * 构造函数, 实例化对象$instance
	 * 
	 * @access public
	 * @param array $hosts 连接信息
	 * @param string $driver 指定连接方式
	 * @throws FCacheException
	 */
	public function __construct($hosts = array(array('host' => '127.0.0.1', 'port' => 11211)), $driver = NULL)
	{
		$this->hosts = $hosts;
		if($hosts)
		{
			if(!$driver)
			{
// 				if (strcmp(strtolower($hosts[0]), 'baidu') == 0) {
// 					require_once dirname(__FILE__).'/CacheSdk/BaeMemcache.class.php';
// 					$this->instance = new BaeMemcache($hosts[1]['cacheID'], $hosts[1]['host']. ': '. $hosts[1]['port'], $hosts[1]['user'], $hosts[1]['password']);
// 				}
				if (class_exists('Memcached'))
				{
					$driver_class = "FMemcached";
					require dirname(__FILE__) . '/drivers/' . $driver_class . '.php';
					$this->instance = new $driver_class($hosts);
				}
				elseif(class_exists('Memcache'))
				{
					$driver_class = "FMemcache";
					require dirname(__FILE__) . '/drivers/' . $driver_class . '.php';
					$this->instance = new $driver_class($hosts);
				}
				else
				{
					throw new FCacheException("No cache driver found.");
				}
			}
		}
	}
	
	/**
	 * 初始化方法
	 * 
	 * @access public
	 * @return NULL
	 */
	public function init()
	{
		if(!$this->instance) return NULL;
		if(method_exists($this->instance, 'init')) $this->instance->init();
	}
	
	/**
	 * 向cache中添加一个新的key/value
	 * 
	 * @access public
	 * @param string $key
	 * @param string $var
	 * @param string $compress
	 * @param number $expire
	 * @return NULL
	 */
	public function add($key, $var, $compress = FALSE, $expire = 0)
	{
		if(!$this->instance) return NULL;
		return $this->instance->add($key, $var, $compress, $expire);
	}
	
	/**
	 * key自减value值
	 * 
	 * @access public
	 * @param string $key
	 * @param number $value
	 * @return NULL|Ambigous <number, boolean>
	 */
	public function decrement($key, $value = 1)
	{
		if(!$this->instance) return NULL;
		return $this->instance->decrement($key, $value);
	}
	
	/**
	 * 删除一个key
	 * 
	 * @access public
	 * @param string $key
	 * @return NULL|boolean
	 */
	public function delete($key)
	{
		if(!$this->instance) return NULL;
		return $this->instance->delete($key);
	}
	
	/**
	 * 获取一个key值
	 * 
	 * @access public
	 * @param string $key
	 * @return NULL|Ambigous <string, multitype:, NULL, boolean, multitype:NULL , unknown, mixed>
	 */
	public function get($key)
	{
		if(!$this->instance) return NULL;
		return $this->instance->get($key);
	}
	
	/**
	 * key自增value值
	 * 
	 * @access public
	 * @param string $key
	 * @param string $value
	 * @return NULL
	 */
	public function increment($key, $value)
	{
		if(!$this->instance) return NULL;
		return $this->instance->increment($key, $value);
	}
	
	/**
	 * 替换一个key的value值
	 * 
	 * @access public
	 * @param string $key
	 * @param string $var
	 * @param string $compress
	 * @param number $expire
	 * @return NULL|boolean
	 */
	public function replace($key, $var, $compress = FALSE, $expire = 0)
	{
		if(!$this->instance) return NULL;
		return $this->instance->replace($key, $var, $compress, $expire);
	}
	
	/**
	 * 设定一个key的value
	 * 
	 * @access public
	 * @param string $key
	 * @param string $var
	 * @param string $compress
	 * @param number $expire
	 * @return NULL|boolean
	 */
	public function set($key, $var, $compress = FALSE, $expire = 0)
	{
		if(!$this->instance) return NULL;
		return $this->instance->set($key, $var, $compress, $expire);
	}
}
