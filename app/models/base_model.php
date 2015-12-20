<?php
/**
 * 
 * @author zhaoguang
 *
 */
class base_model
{
    /**
     * 默认时间格式
     * @var string
     */
    const DEFAULT_TIME_STYLE = 'Y-m-d H:i:s';
    
    /**
     * 一天
     * @var int
     */
    const DEFAULT_ONE_DAY = 86400;
    
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
    
    /**
     * 授权model
     * @var token_model
     */
    protected $tokenModel;
    
    /**
     * 构造函数
     * 
     * @access public
     * @return void
     */
    public function __construct() {
        $this->tokenModel = F::load_model('token');
    }
    
    /**
     * 析构函数
     * 
     * @access public
     * @return void
     */
    public function __destruct() {
        unset($this->tokenModel);
    }
    
    /**
     * 根据授权令牌，获取对应账户信息
     * 
     * @access public
     * @param string $accessToken 授权令牌
     * @return array
     * @throws Exception 106 无效的授权信息
     * @throws Exception 109 授权信息已经过期
     */
    public function getTokenInfo($accessToken) {
        #获取原始授权信息
        $_token = $this->tokenModel->getTokenInfo($accessToken);
        #检验信息有效性
        if (empty($_token)) throw new Exception('无效的授权信息', 106);
        if ($_token['expireTime'] < time()) throw new Exception('授权信息已经过期', 109);
        return $_token;
     }
}

?>