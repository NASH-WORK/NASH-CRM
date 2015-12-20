<?php
/**
 * 面试信息model
 * 
 * @author zhaoguang
 * @version 1.0
 */
final class audition_model
{
    /**
     * 面试人员账户信息类型
     * @var int
     */
    const AUDITION_ACCOUNT = 10;
    
    /**
     * 候选人tagId
     * @var int
     */
    const AUDITION_TAG_ID = 59;
    
    /**
     * 用户model
     * @var user_model
     */
    private $userModel;
    
    public function __construct() {
        $this->userModel = F::load_model('user');
    }
    
    public function __destruct() {
        unset($this->userModel);
    }
    
    /**
     * 根据手机号，获取面试人员的详细信息
     * 
     * @access public
     * @param string $phoneNum
     * @return array
     */
    public function seach($phoneNum) {
        $userId = $this->getUserIdByPhoneNum($phoneNum);
        if ($userId) {
            #获取相关人员的手机号, 姓名, 性别, 应聘职位信息
            $userInfo = $this->userModel->get($userId);
            $returnData = array(
                'phoneNum' => $phoneNum, 
                'sex' => $userInfo[0]['userProfile']['sex']['value'],
                'occupation' => $userInfo[0]['userProfile']['occupation']['value'],
                'nickname' => $userInfo[0]['nickname']
            );
        }else $returnData = array();
        
        return $returnData;
    }
    
    /**
     * 根据手机号码, 获取面试人员id
     * 
     * @access private
     * @param string $phoneNum
     * @return int | boolean
     */
    private function getUserIdByPhoneNum($phoneNum) {
        $userId = F::db()->query('select account.user_id as user_id from account left join user_tag on account.user_id = user_tag.user_id 
            where phone_num = ? and status = 1 and type != 1 and tag_id = ?', 
            array($phoneNum, self::AUDITION_TAG_ID))->fetch_column('user_id');
        return $userId ? $userId : FALSE;
    }
}

?>