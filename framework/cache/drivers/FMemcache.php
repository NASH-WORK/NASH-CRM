<?php

class FMemcache
{
	public $instance;
	
	public $hosts;
	
	public function __construct($hosts = array(array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100)))
	{
		$this->hosts = $hosts;
	}
	
	public function init()
	{
		$this->instance = new Memcache;
		$connected = FALSE;
		$hosts = $this->hosts;
		if(is_array($hosts))
		{
			if(is_array($hosts[0]))
			{
				foreach($hosts as $host)
				{
					for($i = 0; $i < 3; $i++)
					{
						if($this->instance->addServer($host['host'], $host['port'], TRUE, $host['weight']))
						{
							$connected = TRUE;
							break;
						}
					}
				}
			}
			else
			{
				foreach($hosts as $host)
				{
					for($i = 0; $i < 3; $i++)
					{
						if($this->instance->addServer($hosts['host'], $hosts['port'], TRUE, $hosts['weight']))
						{
							$connected = TRUE;
							break;
						}
					}
				}
			}
		}
		if(!$connected)
		{
			throw new FCacheException('Failed to connect to Memcached Server');
		}
	}
	
	public function add($key, $var, $compress = FALSE, $expire = 0)
	{
		if($compress) $compress = MEMCACHE_COMPRESSED;
		return $this->instance->add($key, $var, $compress, $expire);
	}
	
	public function decrement($key, $value = 1)
	{
		return $this->instance->decrement($key, $value);
	}
	
	public function delete($key)
	{
		return $this->instance->delete($key);
	}
	
	public function get($key)
	{
		return $this->instance->get($key);
	}
	
	public function increment($key, $value)
	{
		return $this->instance->increment($key, $value);
	}
	
	public function replace($key, $var, $compress = FALSE, $expire = 0)
	{
		if($compress) $compress = MEMCACHE_COMPRESSED;
		return $this->instance->replace($key, $var, $compress, $expire);
	}
	
	public function set($key, $var, $compress = FALSE, $expire = 0)
	{
		if($compress) $compress = MEMCACHE_COMPRESSED;
		return $this->instance->set($key, $var, $compress, $expire);
	}
	
}