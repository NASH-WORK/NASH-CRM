<?php

class FDebugLogReader
{
	public $content;
	public $template;
	public $result;

	/**
	 * 构造方法
	 * 
	 * @access public
	 * @param string $date 日期
	 */
	public function __construct($date) {
		$config = F::load_config('config.php');
		$filename = "{$config['log']['debug']}log_debug_{$date}.log";
		if(file_exists($filename)) {
			$this->content = file_get_contents($filename);
			$this->result = $this->get_result();
		}
		else $this->result = array();
	}

	/**
	 * 获取日志内容
	 * 
	 * @access public
	 * @return Ambigous <multitype:, multitype:unknown Ambigous <> >
	 */
	public function get_result() {
		$lines = explode("\n", $this->content);
		$r = array();
		$hashes = array();
		$reading = 0;
		
		foreach($lines as $line) {
			if($line == "--------------------LOG BEGIN--------------------")
				$reading = 1;
			elseif($reading == 1) {
				$log_array = explode("\t", $line);
				$r['time'] = $log_array[0];
				$r['host'] = $log_array[1];
				$r['path'] = $log_array[2];
				$r['ip'] = $log_array[3];
				$r['get'] = $log_array[4];
				$r['post'] = $log_array[5];
				$reading = 2;
			}
			elseif($reading == 2) {
				$r['error'] = $line;
				$reading = 3;
			}
			elseif($reading == 3) {
				if($line == "---------------------LOG END---------------------") {
					$reading = 0;
					$hash = crc32($r['error']);
					if(!in_array($hash, $hashes)) $this->result[] = $r;
					$hashes[] = $hash;
					$r = array();
				}
				else
					$r['stack'] .= "{$line}\n";
			}
		}
		return $this->result;
	}

	/**
	 * 加载模板显示内容
	 * 
	 * @access public
	 */
	public function show() {
		require('FDebugLogReader_Tpl.php');
	}
}
