<?php

class FMemcached
{
	public $instance;
	
	public $hosts;
	
	public function __construct($hosts = array(array('host' => '127.0.0.1', 'port' => 11211, 'weight' => 100)))
	{
		$this->hosts = $hosts;
	}
	
	public function init()
	{
		$this->instance = new Memcached;
		$connected = FALSE;
		$hosts = $this->hosts;
		if(is_array($hosts))
		{
			if(is_array($hosts[0]))
			{
				for($i = 0; $i < 3; $i++)
				{
					if($this->instance->addServers($hosts))
					{
						$connected = TRUE;
						break;
					}
				}
			}
			else
			{
				for($i = 0; $i < 3; $i++)
				{
					if($this->instance->addServer($hosts['host'], $hosts['port'], $hosts['weight']))
					{
						$connected = TRUE;
						break;
					}
				}
			}
		}
		if(!$connected)
		{
			throw new FCacheException('Failed to connect to Memcached Server');
		}
	}
}