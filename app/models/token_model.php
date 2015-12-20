<?php
/**
 * 授权model, 负责授权信息的相关查询等
 * 
 * @author zhaoguang
 * @version 1.0
 * @method F::db()
 */
final class token_model
{
    /**
     * 默认授权过期时间
     * @var int
     */
    const DEFAULT_TOKEN_TIME = 2592000;
    
    /**
     * 默认时间格式
     * @var string
     */
    const DEFAULT_TIME_STYLE = 'Y-m-d H:i:s';
    
    private $userId;
    private $accessToken;
    private $refreshToken;
    private $expireTime;
    
    /**
     * 获取授权对应用户信息
     * 
     * @access public
     * @param string $accessToken 授权信息
     * @return array
     */
    public function getTokenInfo($accessToken) {
        #针对空处理
        if (!$accessToken) return array();
        
        #读取缓存数据
        $returnData = F::cache()->get('vstone_tokenModel_getTokenInfo_accessToken:'.$accessToken);
        if ($returnData) {
            $returnData = unserialize($returnData);
        }else {
            #读取数据库数据
            $returnData = F::db()->fetch('select user_id as userId, refresh_token as refreshToken, expire_time as expireTime from token where token = ? limit 1', array($accessToken));
            if ($returnData['expireTime']) $returnData['expireTime'] = strtotime($returnData['expireTime']);
            #添加缓存纪录，时间24h
            F::cache()->add('vstone_tokenModel_getTokenInfo_accessToken:'.$accessToken, serialize($returnData), FALSE, 86400);
        }
        return is_array($returnData) ? $returnData : array();
    }
    
    /**
     * 生成一份新的授权信息
     * 
     * @access public
     * @param int $userId 用户id
     * @return array
     */
    public function create($userId) {
        #生成相关信息
        $this->accessToken = $this->_createToken($userId);
        $this->refreshToken = $this->_createToken($userId);
        $this->expireTime = time() + self::DEFAULT_TOKEN_TIME;
        
        #写入数据库
        F::db()->execute('replace into token set user_id = ?, token = ?, refresh_token = ?, create_time = ?, expire_time = ?', 
            array($userId, $this->accessToken, $this->refreshToken, date(self::DEFAULT_TIME_STYLE), date(self::DEFAULT_TIME_STYLE, $this->expireTime)));
        return array(
            'accessToken' => $this->accessToken,
            'refreshToken' => $this->refreshToken,
            'expireTime' => $this->expireTime
        );
    }
    
    /**
     * 生成授权码信息
     * 
     * @access private
     * @param int $userId 用户id
     * @return string
     */
    private function _createToken($userId) {
        return sha1($userId.microtime().uniqid().rand(0, time()));
    }
    
}

?>