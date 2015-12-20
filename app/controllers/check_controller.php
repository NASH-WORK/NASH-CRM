<?php
/**
 * 检查相关信息controller
 * 
 * @author zhaoguang
 * @version 1.0 
 */
final class check_controller extends \base_controller
{
    public function __construct()
    {
        parent::__construct();
    }

    function __destruct()
    {}
    
    /**
     * (non-PHPdoc)
     * @see base_controller::checkAccessToken()
     */
    public function checkAccessToken() {
        parent::checkAccessToken();
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($GLOBALS['userId']);
    }
}

?>