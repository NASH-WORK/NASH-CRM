<?php

class FController
{
	public function require_params($require_params, $params = array())
	{
		$check_params = FValidator::require_params($require_params, $params);
		if($check_params) {
			F::rest()->show_error(100, array('requires' => $check_params));
		}
		return $params;
	}
}
