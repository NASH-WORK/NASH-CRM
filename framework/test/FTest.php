<?php

/**
 * 基于PHPUnit测试脚本基类, 所有测试脚本需要继承该类
 * 
 * @author reckfull <ruckfull@gmail.com>
 * @version 2.0
 */

class FTest extends PHPUnit_Framework_TestCase
{
	/**
	 * PHPUnit load方法
	 * 
	 * @param string $module 加载测试类名称
	 * @return $module object
	 */
	public static function load($module)
	{
		require_once APPPATH . 'tests/' . $module . '_test.php';
		$class = $module . '_test';
		return new $class;
	}
	
	/**
	 * 发出POST请求
	 * 
	 * @access public
	 * @param string $url
	 * @param string $params POST请求参数
	 */
	public function post($url, $params = NULL)
	{
		$result = FCurl::post(BASEURL . $url, $params);
		echo "<pre>$result</pre>";
		return $this->parseJson($result);
	}
	
	/**
	 * 解析JSON格式数据
	 * 
	 * @access public
	 * @param string $json
	 * @return mixed
	 */
	public function parseJson($json)
	{
		return json_decode($json, TRUE);
	}
}
