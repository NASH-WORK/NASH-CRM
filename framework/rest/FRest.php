<?php

class FRest
{
	private $_config;
	
	public function __construct($config)
	{
		$this->_config = $config;
		return $this;
	}
	
	public function show_result($result = NULL, $extra = NULL)
	{
		$array = array($this->_config['keys']['code'] => 0);
		
		if(NULL !== $result || is_array($result))
		{
			$array[$this->_config['keys']['result']] = $result;
		}
		
		if($extra)
		{
			if(is_array($extra))
			{
				$array += $extra;
			}
			else
			{
				$array[$this->_config['keys']['extra']] = $extra;
			}
		}
		
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($array);
		die();
	}
	
	public function show_error($error_code, $extra = NULL)
	{
		$array = array($this->_config['keys']['code'] => $error_code);
		
		if(isset($this->_config['messages'][$error_code]))
		{
			$array[$this->_config['keys']['message']] = $this->_config['messages'][$error_code];
		}
		else
		{
			$array[$this->_config['keys']['message']] = 'unknown error.';
		}
		
		if($extra)
		{
			if(is_array($extra))
			{
				$array += $extra;
			}
			else
			{
				$array[$this->_config['keys']['extra']] = $extra;
			}
		}
		
		header('Content-Type:application/json; charset=utf-8');
		echo json_encode($array);
		die();
	}
}
