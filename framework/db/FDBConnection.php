<?php

class FDBConnection
{
	public  $instance = NULL;
	
	public  $host    = '';
	public  $port    = '';
	public  $user    = '';
	public  $pass    = '';
	public  $db      = '';
	public  $charset = '';
	
	private $transaction = FALSE;
	
	public function __construct($host = '127.0.0.1', $user = 'root', $pass = 'root', $db = '', $port = 3306, $charset = 'utf8')
	{
		$this->host    = $host;
		$this->user    = $user;
		$this->pass    = $pass;
		$this->db      = $db;
		$this->port    = $port;
		$this->charset = $charset;
	}
	
	public function init()
	{
		for($i = 0; $i < 3; $i++)
		{
			$this->instance = new mysqli($this->host, $this->user, $this->pass, $this->db, $this->port);
			if($this->instance->connect_errno == 0)
			{
				$this->instance->query("SET NAMES {$this->charset}");
				return;
			}
		}
		throw new FDBException("Failed to connect database. {$this->instance->connect_errno}: {$this->instance->connect_error}");
	}
	
	public function close()
	{
		$this->instance->close();
	}
	
	public function query($sql = NULL, $params = NULL)
	{
		return new FDBQuery($this, $sql, $params);
	}
	
	public function execute($sql = NULL, $params = NULL)
	{
		return $this->query($sql, $params)->execute();
	}
	
	public function fetch($sql = NULL, $params = NULL)
	{
		return $this->query($sql, $params)->fetch();
	}
	
	public function fetch_all($sql = NULL, $params = NULL)
	{
		return $this->query($sql, $params)->fetch_all();
	}
	
	public function begin()
	{
		$this->instance->autocommit(0);
		$this->transaction = TRUE;
		register_shutdown_function(array($this, 'rollback'));
	}
	
	public function commit()
	{
		$this->instance->autocommit(1);
		$this->transaction = FALSE;
	}
	
	public function rollback()
	{
		if($this->transaction)
		{
			$this->instance->rollback();
			$this->transaction = FALSE;
		}
	}
	
	public function errno()
	{
		return $this->instance->errno;
	}
	
	public function error()
	{
		return $this->instance->error;
	}
	
	public function affected_rows()
	{
		return $this->instance->affected_rows;
	}
	
	public function insert_id()
	{
		return $this->instance->insert_id;
	}
	
}
