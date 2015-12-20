<?php

/**
 * 验证类, 负责提供相关验证方法
 * 
 * @author ruckfull	<ruckfull@gmail.com>
 * @version 2.0
 */

define('UINT64_MAX', 1.844674407371E+19);
define('UINT32_MAX', 4294967296);
define('INT64_MAX',  UINT64_MAX / 2);
define('INT32_MAX',  UINT32_MAX / 2);
define('INT64_MIN',  -UINT64_MAX / 2);
define('INT32_MIN',  -UINT32_MAX / 2);

class FValidator
{
	/**
	 * 筛选数组,获取必备key对应value
	 * 
	 * @access public
	 * @param array $require_params key array
	 * @param array $params 待筛选数组
	 * @return multitype:
	 */
	public static function require_params($require_params = array(), &$params = array())
	{
		if(!$params) $params = F::all_requests();
		return array_values(array_diff($require_params, array_keys($params)));
	}
	
	/**
	 * 判断参数是否是int类型
	 * 
	 * @access public
	 * @param int|string $var
	 * @return boolean
	 */
	public static function int($var)
	{
		if(is_int($var)) return TRUE;
		return FALSE;
	}
	
	/**
	 * 判断参数是否是一个数字
	 * 
	 * @access public
	 * @param int|string $var
	 * @return boolean
	 */
	public static function number($var)
	{
		if(is_numeric($var)) return TRUE;
		return FALSE;
	}
	
	/**
	 * 判断参数长度是否合法
	 * 
	 * @access public
	 * @param string $var
	 * @param int $min 最短长度
	 * @param int $max 最大长度
	 * @return boolean
	 */
	public static function length($var, $min, $max)
	{
		if(function_exists('mb_strlen')) $len = mb_strlen($var);
		else $len = strlen($var);
		if($len < $min || $len > $max) return FALSE;
		return TRUE;
	}
	
	/**
	 * 给定元素是否存在于集合中
	 * 不建议使用, 使用系统函数替换
	 * 
	 * @access public
	 * @param string $var 待查找元素
	 * @param array $enum 待查找集合
	 * @return boolean
	 */
	public static function enum($var, $enum)
	{
		return in_array($var, $enum);
	}
	
	/**
	 * 判断email格式否是合法
	 * 
	 * @access public
	 * @param string $var
	 * @return mixed
	 */
	public static function email($var)
	{
		return filter_var($var, FILTER_VALIDATE_EMAIL);
	}
	
	/**
	 * 判断URL格式是否合法
	 * 
	 * @access public
	 * @param string $var
	 * @return mixed
	 */
	public static function url($var)
	{
		return filter_var($var, FILTER_VALIDATE_URL);
	}
	
	/**
	 * 给定IP是否合法
	 * 
	 * @access public
	 * @param string $var
	 * @return mixed
	 */
	public static function ip($var)
	{
		return filter_var($ip, FILTER_VALIDATE_IP);
	}
	
	/**
	 * 验证手机号码合法性
	 * 
	 * @access public
	 * @param number $var phone num
	 * @return boolean
	 */
	public static function phone($var) {
		if( ! is_numeric($var)) return false ;
		
		$var = trim($var);
		if(strlen($var) != 11) return false;
		
		$asms = array (
			'133','153','180','189', //电信
			'177',
			'130','131','132','145', //联通
			'155','156','185','186',
			'134','135','136','137', //移动
			'138','139','147','150',
			'151','152','157','158',
			'159','182','187','188',
		    '183'
		);
		if(in_array(substr($var, 0, 3), $asms)) return true;
		return false;
	}
	
	/**
	 * 检测电话号码是否合法
	 * 包括手机号码和电话号码检测
	 * 
	 * @access public
	 * @static
	 * @param string $str
	 * @return boolean
	 */
	public static function telNum($str) {
	    if (preg_match("/^(0[0-9]{2,3}-)?([2-9][0-9]{6,7})+(-[0-9]{1,4})?$/",$str)) return TRUE;
	    else return FALSE;
	}
}
