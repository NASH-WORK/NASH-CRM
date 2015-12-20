<?php
/**
 * 面试人信息controller
 * 
 * @author zhaoguang
 * @version 1.0
 */
final class audition_controller extends \base_controller
{
    /**
     * 面试人员信息model
     * @var audition_model
     */
    private $auditionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->auditionModel = F::load_model('audition');
    }

    function __destruct()
    {
        unset($this->auditionModel);
        parent::__destruct();
    }
    
    /**
     * 搜索面试人员信息
     * 
     * @access public
     * @param string phoneNum
     * @return array
     * @example
     * 
     */
    public function seach() {
        $this->params = $this->require_params(array('phoneNum'));
        if (!FValidator::phone($this->params['phoneNum'])) throw new Exception('无效的手机号码', 100);
        $this->returnData = $this->auditionModel->seach($this->params['phoneNum']);
        if (empty($this->returnData)) throw new Exception('相关信息未找到', 204);
        F::rest()->show_result($this->returnData);
    }
}

?>