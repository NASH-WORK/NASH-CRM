<?php

/**
 * 日志类, 书写对应日志信息
 * 
 * @author reckfull <ruckfull@gmail.com>
 * @version 2.0
 */
class FLog
{
	private $_config;
	
	/**
	 * 加载LOG配置文件
	 * 
	 * @access public
	 * @param array $config
	 * @return FLog
	 */
	public function __construct($config)
	{
		$this->_config = $config;
		return $this;
	}
	
	/**
	 * 生产errorLog显示内容
	 * 
	 * @access public
	 * @param int $errno 错误号 
	 * @param string $errstr 错误信息
	 * @param string $errfile 错误文件
	 * @param int $errline 错误行数
	 * @param string $errcontext
	 */
	public function log_error($errno, $errstr, $errfile, $errline, $errcontext)
	{
		$log_content = date('Y-m-d H:i:s') . "\t{$_SERVER['HTTP_HOST']}\t{$_SERVER['REQUEST_URI']}\t{$_SERVER['REMOTE_ADDR']}\t$errno\t$errstr\t$errfile\t$errline\tGET" .
			$this->export_array($_GET) . "\tPOST" . $this->export_array($_POST) . "\n";
		$this->write_log($this->_config['error'] . '/log_error_' . date('Y-m-d') . '.log', $log_content);
	}
	
	/**
	 * 生成debugLog显示内容
	 * 
	 * @access public
	 * @param string $content
	 */
	public function log_debug($content)
	{
		$log_content = "--------------------LOG BEGIN--------------------\n";
		$log_content .= date('Y-m-d H:i:s') . "\t{$_SERVER['HTTP_HOST']}\t{$_SERVER['REQUEST_URI']}\t{$_SERVER['REMOTE_ADDR']}\tGET";
		$log_content .= $this->export_array($_GET) . "\tPOST" . $this->export_array($_POST) . "\n" . $content . "\n";
		$log_content .= "---------------------LOG END---------------------\n";
		$this->write_log($this->_config['debug'] . '/log_debug_' . date('Y-m-d') . '.log', $log_content);
	}
	
	/**
	 * 书写Log日志
	 * 当项目部署在BAE上时使用扩展服务LOG
	 * 
	 * @access private
	 * @param string $file
	 * @param string $content
	 */
	private function write_log($file, $content)
	{
		if(in_array('baidu', $this->_config)) {
			require_once dirname(__FILE__).'/LogSdk/BaeLog.class.php';
			$secret = array('user' => $this->_config['user'], 'passwd' => $this->_config['pass']);
			$logger = BaeLog::getInstance($secret);
			$logger->setLogLevel(16);
			$logger->Debug($content);
		}
		else
			file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
	}
	
	public function export_array($array){
		foreach($array as $k => &$v) {
			if(is_array($v))
				$v = "$k=>" . $this->export_array($v);
			else
				$v = "$k=>$v";
		}
		return '(' . implode(', ', $array) . ')';
	}
}
