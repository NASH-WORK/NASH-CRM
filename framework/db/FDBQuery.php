<?php

class FDBQuery
{
	private $_connection;
	private $_sql;
	private $_statement;
	private $_result;
	
	public function __construct(FDBConnection $connection, $sql = NULL, $params = NULL)
	{
		$this->init($connection, $sql, $params);
	}
	
	public function init(FDBConnection $connection, $sql = NULL, $params = NULL)
	{
		$this->_connection = $connection;
		$this->_sql = $sql;
		if($params)
		{
			$this->auto_bind($params);
		}
	}
	
	public function prepare()
	{
		if($this->_statement == NULL)
		{
			$this->_statement = $this->_connection->instance->prepare($this->_sql);
			if(!$this->_statement)
			{
				throw new FDBException("SQL init error. {$this->_connection->errno()}: {$this->_connection->error()}");
			}
		}
		return $this;
	}
	
	public function bind_param($type = 's', $args)
	{
		$this->prepare();
		if(PHP_VERSION < 5.3)
		{
			$args = func_get_args();
			call_user_func_array(array($this->_statement, 'bind_param'), $args);
		}
		else
		{
			call_user_func_array(array($this->_statement, 'bind_param'), func_get_args());
		}
		return $this;
	}
	
	public function auto_bind($params)
	{
		$params_types = array('');
		if(!is_array($params))
		{
			$params = array($params);
		}
		foreach($params as $p)
		{
			if(is_int($p))
			{
				$params_types[0] .= 'i';
			}
			#针对带前导0或者空格的字符串被误判为double类型而造成的存储错误BUG修正
			#is_numeric检查是否是数字或者数字字符串
			elseif(is_numeric($p) && !is_string($p))
			{
				$params_types[0] .= 'd';
			}
			else
			{
				$params_types[0] .= 's';
			}
		}
		$params = array_merge($params_types, array_values($params));
		call_user_func_array(array($this, 'bind_param'), $params);
		return $this;
	}
	
	/*
	 * execute an insert, update, delete or replace statement
	 */
	public function execute()
	{
		$this->prepare();
		if($this->_statement)
		{
			$this->_statement->execute();
			if(method_exists($this->_statement, 'get_result'))
			{
				// mysqlnd
				$this->_result = $this->_statement->get_result();
			}
			else
			{
				$this->_statement->store_result();
				$this->_result = array();
				$meta = $this->_statement->result_metadata();
				if(method_exists($meta, 'fetch_field'))
				{
					while($columnName = $meta->fetch_field())
					{
	            		$params[] = &$row[$columnName->name];
					}
					call_user_func_array(array($this->_statement, 'bind_result'), $params);
	
					while($this->_statement->fetch())
					{
						foreach($row as $k => $v)
						{
							$c[$k] = $v;
						}
						$this->_result[] = $c;
					}
				}
			}
		}
		else
		{
			$this->_result = $this->_connection->instance->query($this->_sql);
		}
		if($this->_connection->errno())
		{
			throw new FDBException("SQL execute error. {$this->_connection->errno()}: {$this->_connection->error()}");
		}
		return $this;
	}
	
	public function fetch()
	{
		$this->execute();
		if(is_array($this->_result))
		{
			if(count($this->_result) > 0)
				$result = $this->_result[0];
			else
				$result = FALSE;
		}
		else
		{
			$result = $this->_result->fetch_array(MYSQLI_ASSOC);
			$this->_result->free_result();
		}
		return $result;
	}
	
	public function fetch_all()
	{
		$this->execute();
		if(method_exists($this->_result, 'fetch_all'))
		{
			$result = $this->_result->fetch_all(MYSQLI_ASSOC);
			$this->_result->free_result();
		}
		else
		{
			if(is_array($this->_result))
			{
				$result = $this->_result;
			}
			else
			{
				$result = array();
				while($row = $this->_result->fetch_array(MYSQLI_ASSOC))
				{
					$result[] = $row;
				}
				$this->_result->free_result();
			}
		}
		return $result;
	}
	
	public function fetch_column($column)
	{
		$result = $this->fetch();
		if(isset($result[$column]))
		{
			return $result[$column];
		}
		else
		{
			return FALSE;
		}
	}
	
	public function fetch_column_all($column)
	{
		$result = $this->fetch_all();
		$result_array = array();
		foreach($result as $r)
		{
			if(isset($r[$column]))
			{
				if(!in_array($r[$column], $result_array))
				{
					$result_array[] = $r[$column];
				}
			}
		}
		return $result_array;
	}
	
	public function affected_rows()
	{
		return $this->_connection->affected_rows();
	}
	
	public function insert_id()
	{
		return $this->_connection->insert_id();
	}
	
}
