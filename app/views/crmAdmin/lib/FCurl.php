<?php

/**
 * 网络请求CURL封装, 提供相关CURL方式发送请求
 * 
 * @author reckfull <ruckfull@gmail.com>
 * @version 2.0
 */
class FCurl {
	
	/*
	 * opt includes timeout, headers, header, follow
	 */
	
	public static $boundary = '';
	
	/**
	 * 执行请求
	 * 
	 * @access public
	 * @param curl_handle $ch CURL打开句柄
	 * @param array $opt 设置参数, 包括header, timeout, follow, headers
	 * @return mixed
	 */
	public static function fetch($ch, $opt) {
		curl_setopt($ch, CURLOPT_HEADER, isset($opt['header']) ? $opt['header'] : 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, isset($opt['timeout']) ? $opt['timeout'] : 20);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, isset($opt['follow']) ? $opt['follow'] : 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		if(isset($opt['headers'])) curl_setopt($ch, CURLOPT_HTTPHEADER, $opt['headers']);
		$response = curl_exec($ch);

		#解析相关数据
		$response = json_decode($response, true);
		if ($response['code'] == 0 && isset($response['code'])) {
			#获取数据成功
			$response = isset($response['data']) ? $response['data'] : '';
		}else {
			//echo '<script type="text/javascript" charset="utf-8" async defer>alert("获取数据失败")</script>';
			self::header('login.php');
		}

		return $response;
	}

	public static function header($url) {
		header('location:http://'.$_SERVER['HTTP_HOST'].'/test/crmAdmin/'.$url);
		exit();
	}
	
	/**
	 * GET请求处理
	 * 
	 * @access public
	 * @param string $url
	 * @param array $params 请求参数
	 * @param array $opt 设置请求头参数信息
	 * @return mixed
	 */
	public static function get($url, $params = array(), $opt = array()) {
	    $url = 'http://'.$_SERVER['HTTP_HOST'].'/vstone/app/?r='.$url;
		$url = strpos($url, '?') ? $url . '&' . http_build_query($params) : $url . '?' . http_build_query($params);
		$ch = curl_init();
		// echo $url;
		// echo "<br>";
		curl_setopt($ch, CURLOPT_URL, $url);
		
		return self::fetch($ch, $opt);
	}
	
	/**
	 * POST请求处理
	 * 
	 * @access public
	 * @param string $url
	 * @param string $params POST参数
	 * @param string $multi
	 * @param string $content_type
	 * @param unknown $opt
	 */
	public static function post($url, $params = array(), $multi = FALSE, $content_type = NULL, $opt = array()) {
		if(!isset($opt['headers'])) $opt['headers'] = array();
		if(!$multi && (is_array($params))) {
			$params = http_build_query($params);
		} elseif ($multi) {
			$params = self::build_http_query_multi($params, $content_type);
			$opt['headers'][] = "Content-Type: multipart/form-data; boundary=" . self::$boundary;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		
		return self::fetch($ch, $opt);
	}
	
	/**
	 * PUT请求处理
	 * 
	 * @access public
	 * @param string $url
	 * @param string $file 打开文件地址
	 * @param array $opt
	 */
	public static function put($url, $file, $opt = array()) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_PUT, 1);
		curl_setopt($ch, CURLOPT_INFILE, fopen($file, 'r'));
		curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
		
		return self::fetch($ch, $opt);
	}
	
	public static function head($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'HEAD');
		curl_setopt($ch, CURLOPT_NOBODY, 1);
		
		return self::fetch($ch, array('header' => 1));
	}
	
	public static function get_mime_type($filename) {
		$mime_map = array(
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'jpeg'=> 'image/jpeg',
			'gif' => 'image/gif',
			'wav' => 'audio/wav',
			'mp3' => 'audio/mpeg3',
			'mov' => 'video/quicktime',
			'pdf' => 'application/pdf',
			'html' => 'text/html',
			'txt' => 'text/plain',
			'xml' => 'application/xml',
		);
		$file_ext = substr($filename, strrpos($filename, '.') + 1);
		if (!empty($file_ext) && array_key_exists($file_ext, $mime_map)) {
			return $mime_map[$file_ext];
		}
		return 'application/octet-stream';
	}
	
	private static function build_http_query_multi($params, $content_type = NULL) {
		if (!$params)
			return '';

		uksort($params, 'strcmp');

		$pairs = array();

		self::$boundary = $boundary = uniqid('------------------');
		$MPboundary = '--' . $boundary;
		$endMPboundary = $MPboundary . '--';
		$multipartbody = '';

		foreach ($params as $key => $value) {

			if (in_array($key, array('data', 'photo', 'file', 'image')) && $value{0} == '@') {
				$url = ltrim($value, '@');
				$content = file_get_contents($url);
				$array = explode('?', basename($url));
				$filename = $array[0];

				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $key . '"; filename="' . $filename . '"' . "\r\n";
				if($content_type) $multipartbody .= "Content-Type: " . $content_type . "\r\n\r\n";
				else $multipartbody .= "Content-Type: " . self::get_mime_type($filename) . "\r\n\r\n";
				$multipartbody .= $content . "\r\n";
			} else {
				$multipartbody .= $MPboundary . "\r\n";
				$multipartbody .= 'Content-Disposition: form-data; name="' . $key . "\"\r\n\r\n";
				$multipartbody .= $value . "\r\n";
			}

		}

		$multipartbody .= $endMPboundary;
		return $multipartbody;
	}
	
}
