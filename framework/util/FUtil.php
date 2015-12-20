<?php

class FUtil
{
	public static function base_convert($number, $from_base = 10, $to_base = 62)
	{
		if($to_base > 62 || $to_base < 2) {
			trigger_error("Invalid base (".he($to_base)."). Max base can be 62. Min base can be 2.", E_USER_ERROR);
		}
		//OPTIMIZATION: no need to convert 0
		if("{$number}" === '0') {
			return 0;
		}
 
		//OPTIMIZATION: if to and from base are same.
		if($from_base == $to_base){
			return $number;
		}
 
		//OPTIMIZATION: if base is lower than 36, use PHP internal function
		if($from_base <= 36 && $to_base <= 36) {
			// for lower base, use the default PHP function for faster results
			return base_convert($number, $from_base, $to_base);
		}
 
		// char list starts from 0-9 and then small alphabets and then capital alphabets
		// to make it compatible with eixisting base_convert function
		$charlist = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($from_base < $to_base) {
			// if converstion is from lower base to higher base
			// first get the number into decimal and then convert it to higher base from decimal;
 
			if($from_base != 10){
				$decimal = self::convert($number, $from_base, 10);
			} else {
				$decimal = intval($number);
			}
 
			//get the list of valid characters
			$charlist = substr($charlist, 0, $to_base);
 
			if($number == 0) {
				return 0;
			}
			$converted = '';
			while($number > 0) {
				$converted = $charlist{($number % $to_base)} . $converted;
				$number = floor($number / $to_base);
			}
			return $converted;
		} else {
			// if conversion is from higher base to lower base;
			// first convert it into decimal and the convert it to lower base with help of same function.
			$number = "{$number}";
			$length = strlen($number);
			$decimal = 0;
			$i = 0;
			while($length > 0) {
				$char = $number{$length-1};
				$pos = strpos($charlist, $char);
				if($pos === false){
					trigger_error("Invalid character in the input number: ".($char), E_USER_ERROR);
				}
				$decimal += $pos * pow($from_base, $i);
				$length --;
				$i++;
			}
			return self::base_convert($decimal, 10, $to_base);
		}
	}
}
