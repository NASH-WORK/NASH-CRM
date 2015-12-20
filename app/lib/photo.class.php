<?php

/** 
 * 图片操作库
 * 
 * 该类库依赖于GB库扩展
 * 该类库封装了图片方面的常用操作, 现阶段提供如下方法:
 * copyImageWithSize:根据源图片为基础，修改其宽与高，然后生成一个新的图片至目的地址
 * getPhotoInfo : 获取图片信息
 * 
 * @author reckfull
 * @email ruckfull@gmail.com
 * @version 1.0.0
 */

final class photo {
	
	/**
	 * 支持图片格式
	 * @var ArrayIterator
	 */
	private $supportImageType;
	
	/**
	 * 构造函数,若环境不支持gb库则无法使用该类
	 * @access public
	 * @return boolean
	 */
	public function __construct(){
		if (!extension_loaded('gd')) F::rest()->show_error(200, 'can\'t load gd extension.');
		$this->supportImageType = array('image/jpeg', 'image/png');
	}
	
	public function __destruct(){
		unset($this->supportImageType);
	}
	
	/**
	 * 根据源图片为基础，修改其宽与高，然后生成一个新的图片至目的地址
	 * @access public
	 * @param resource $sourceImage 源图片操作资源符
	 * @param resource $dstImage 生成图片资源符
	 * @param number $len 生成图片宽度
	 * @param number $height 生成图片高度
	 * @return boolean
	 */
	public function copyImageWithSize($sourceImage, $dstImage, $len = 160, $height = 160){
		#获得图像的信息数组
		$dims = getimagesize($sourceImage);
		$imageType = $dims['mime'];
		#判断图片格式的合法性
		if (!in_array($imageType, $this->supportImageType)) return false;
		
		#缩略图的最大宽度和最大高度
		$max_width = $len;
		$max_height = $len;
		$start_x = 0;
		$start_y = 0;
		
		$original = $imageType == 'image/jpeg' ? imagecreatefromjpeg($sourceImage) : imagecreatefrompng($sourceImage);
		#创建一个供复制的真彩色图像
		$thumb = imagecreatetruecolor($len, $height);
		#复制图像
		imagecopyresampled($thumb, $original, 0, 0, 0, 0, $len, $height, $dims[0], $dims[1]);
		#输出图像
		$imageType == 'image/jpeg' ? imagejpeg($thumb, $dstImage) : imagepng($thumb, $dstImage);
		return true;
	}
	
	/**
	 * 获取图片信息
	 * 
	 * @access public
	 * @param string $photo 上传图片
	 * @return array include width, height, md5, type
	 */
	public function getPhotoInfo($photo) {
		$photoInfo = getimagesize($photo);
		return array(
			'width' => $photoInfo[0],
			'height' => $photoInfo[1],
			'md5' => md5($photo),
			'type' => str_replace('image/', '', $photoInfo['mime'])
		);
	}
}

