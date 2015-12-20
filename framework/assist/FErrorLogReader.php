<?php

class FErrorLogReader
{
	public $content;
	public $template;
	public $result;

	/**
	 * 构造函数
	 * 
	 * @access public
	 * @param string $date 日期
	 */
	public function __construct($date)
	{
		$config = F::load_config('config.php');
		$filename = "{$config['log']['error']}log_error_{$date}.log";
		if(file_exists($filename))
		{
			$this->content = file_get_contents($filename);
			$this->result = $this->get_result();
		}
		else
		{
			$this->result = array();
		}
	}

	/**
	 * 获取显示结果
	 * 
	 * @access public
	 * @return Ambigous <multitype:, multitype:unknown Ambigous <> >
	 */
	public function get_result()
	{
		$logs = explode("\n", $this->content);
		$hashes = array();
		foreach($logs as $log)
		{
			$log_array = explode("\t", $log);
			if(count($log_array) < 2) continue;
			$r = array();
			$r['time'] = $log_array[0];
			$r['host'] = $log_array[1];
			$r['path'] = $log_array[2];
			$r['ip'] = $log_array[3];
			$r['level'] = $log_array[4];
			$r['error'] = $log_array[5];
			$r['file'] = $log_array[6];
			$r['line'] = $log_array[7];
			$r['get'] = $log_array[8];
			$r['post'] = $log_array[9];
			$hash = crc32($r['error'] . $r['file'] . $r['line']);
			if(!in_array($hash, $hashes))
			{
				$hashes[] = $hash;
				$this->result[] = $r;
			}
		}
		return $this->result;
	}

	/**
	 * 使用模板显示内容
	 * 
	 * @access public
	 */
	public function show()
	{
		require('FErrorLogReader_Tpl.php');
	}
}
