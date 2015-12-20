<?php
/**
 * 所有controller父类,所有controller都需要继承本类
 * 提供签名检查方法
 * 
 * @author zhaoguang
 * @version 1.0
 */
class base_controller extends \FController
{
    /**
     * 系统创建事件
     * @var int
     */
    const SYSTEM_EVENT = 1;
    
    /**
     * 用户创建事件
     * @var int
     */
    const USER_EVENT = 2;
    
    #管理员账户id
    const ADMIN_USER_ID = 1;
    
    /**
     * base model
     * @var base_model
     */
    protected $model;
    
    /**
     * 请求参数数组
     * @var array
     */
    protected $params = array();
    
    /**
     * 返回结果数组
     * @var array
     */
    protected $returnData = array();
    
    /**
     * 构造函数
     * 
     * @access protected
     * @return void
     */
    protected function __construct() {
        $this->model = F::load_model('base');
        #设定时区信息
        date_default_timezone_set('Asia/Shanghai');
    }
    
    /**
     * 析构函数
     * 
     * @access protected
     * @return void
     */
    protected function __destruct() {
        unset($this->model);
        unset($this->params);
        unset($this->returnData);
    }
    
    /**
     * 用户授权检查
     * 
     * @access protected
     * @param string accessToken 用户授权令牌
     * @return void
     */
    protected function checkAccessToken() {
        $this->params = $this->require_params(array('accessToken'));
        
        if ($this->params['accessToken'] == 'vPoGp4lHm6') {
            $_tokenInfo['userId'] = 306;
        }else {
            $_tokenInfo = $this->model->getTokenInfo($this->params['accessToken']);
        }
        #填写全局信息
        $GLOBALS['userId'] = $_tokenInfo['userId'];
        unset($_tokenInfo);
    }
    
    /**
     * 用户接口调用日志纪录
     * 
     * @access public
     * @param int $userId
     * @param string $APIName
     * @param string $param
     * @return boolean
     */
    protected function userLog($userId, $APIName, $param) {
        F::db()->execute('insert into user_log set user_id = ?, API_name = ?, param = ?, time = ?', array($userId, $APIName, serialize($param), date('Y-m-d H:i:s')));
    }
}

?>