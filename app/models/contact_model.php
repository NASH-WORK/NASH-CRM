<?php
/**
 * 联系人model
 * 
 * @author zhaoguang
 * @version 1.0
 */
final class contact_model
{
    /**
     * 用户model
     * @var user_model
     */
    private $userModel;
    
    /**
     * 标签model
     * @var tag_model
     */
    private $tagModel;
    
    function __construct()
    {
        $this->userModel = F::load_model('user');
        $this->tagModel = F::load_model('tag');
    }

    function __destruct()
    {
        unset($this->userModel);
        unset($this->tagModel);
    }
    
    /**
     * 获取联系人的信息
     * 
     * @access public
     * @param int $userId
     * @return array
     */
    public function getContactProfile($userId) {
        $userProfile = $this->userModel->get($userId);
        $userTagList = $this->userModel->getUserTagList($userId);
        foreach ($userTagList as &$value) $value = '<span>#'.$value['tagName'].'</span>';
        $acctionList = $this->userModel->getAcctionList($userId);
        foreach ($acctionList as &$acctionIndex) $acctionIndex = '<a href="">'.$acctionIndex.'</a>';
        
        return array(
            'id' => $userId,
            'username' => $userProfile[0]['nickname'],
            'phoneNum' => $userProfile[0]['phone_num'],
            'userProfile' => $userProfile[0]['userProfile'],
            'tagList' => implode(' ', $userTagList),
            'acctionList' => implode(',', $acctionList)
        );
    }
    
    /**
     * 更新用户信息
     * 
     * @access public
     * @param int $userId
     * @param array $updateInfo
     */
    public function update($userId, $updateInfo) {
        unset($updateInfo['add']);
        unset($updateInfo['update']);
        unset($updateInfo['addParam']);
        unset($updateInfo['updateParam']);
        $nickname = $updateInfo['username'];
        $phoneNum = $updateInfo['phoneNum'];
        unset($updateInfo['username']);
        unset($updateInfo['phoneNum']);
        
        $updateInfoTmp = array();
        foreach ($updateInfo as $key => $value) {
            $updateInfoTmp[$key] = array(
                $key => $this->tagModel->getUserProfileName($key),
                'value' => $value
            );
        }
        
        F::db()->execute('update account set phone_num = ? where user_id = ?', array($phoneNum, $userId));
        F::db()->execute('update user_profile set nickname = ?, user_profile = ?, update_time = ? where user_id = ?', 
            array($nickname, serialize($updateInfoTmp), date('Y-m-d H:i:s'), $userId));
    }
    
    /**
     * 通过完整的联系电话号码，检测相关联系人是否已经被创建
     * 
     * @access public
     * @param string $telNum 联系电话号码
     * @param int|boolean
     */
    public function getContactIdByPhoneNum($telNum) {
        $contactId = F::db()->query('select user_id from account where phone_num like '."'%$telNum%'".' and status = 1 and type = 2')->fetch_column('user_id');
        return $contactId ? $contactId : FALSE;
    }
    
    #根据联系人姓名，返回联系人相关信息
    public function seach($name) {
        if (!trim($name)) return array();
        $list = F::db()->fetch_all('SELECT user_profile.user_id as userId, nickname, phone_num FROM `user_profile` left join account on user_profile.user_id = account.user_id 
            where nickname like ? and type = 2 and status = 1 limit 20 ', array($name.'%'));
        return is_array($list) ? $list : array();
    }
    
    #根据账户姓名，返回联系人相关信息
    public function seachAll($name) {
        if (!trim($name)) return array();
        $list = F::db()->fetch_all('SELECT user_profile.user_id as userId, nickname, phone_num FROM `user_profile` left join account on user_profile.user_id = account.user_id
            where nickname like ? and status = 1 limit 20 ', array($name.'%'));
        return is_array($list) ? $list : array();
    }
}

?>