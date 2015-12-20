<?php
/**
 * 联系人controller
 * 
 * @author zhaoguang
 * @version 1.0
 */
final class contact_controller extends \base_controller
{
    /**
     * 联系人model
     * @var contact_model
     */
    private $contactModel;
    
    /**
     * 标签model
     * @var tag_model
     */
    private $tagModel;
    
    /**
     * 用户model
     * @var user_model
     */
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->contactModel = F::load_model('contact');
        $this->userModel = F::load_model('user');
        $this->tagModel = F::load_model('tag');
    }

    function __destruct()
    {
        parent::__destruct();
        unset($this->contactModel);
        unset($this->userModel);
        unset($this->tagModel);
    }
    
    /**
     * 获取联系人的相关信息
     * 
     * @access public
     * @return array
     */
    public function getContactProfile() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('phoneNum'));
        $this->params['id'] = F::request('id', 0);
        
        if ($this->params['id']) {
            $userId = $this->params['id'];
        }else {
            $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
            if (!$userId) throw new Exception('用户不存在', 101);
        }
        
        #判定查看权限
        $openEnable = $this->userModel->isEnableOpen($GLOBALS['userId'], $userId, 1);
        if ($openEnable) {
            $this->returnData = $this->contactModel->getContactProfile($userId);
        }else {
            $this->returnData = array();
        }
        
        F::rest()->show_result($this->returnData);
    }
    
    /**
     * 更新联系人信息
     * 
     * @access public
     * @return void
     */
    public function update() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id'));
        $userId = $this->params['id'];
        unset($this->params['id']);
        unset($this->params['accessToken']);
        unset($this->params['r']);
        
        $this->returnData = $this->contactModel->getContactProfile($userId);
        unset($this->returnData['id']);
        unset($this->returnData['tagList']);
        unset($this->returnData['acctionList']);
        
        $requestTagArray = array();
        foreach ($this->params as $key => $value) {
            if ($key == 'username' && $value != $this->returnData['username']) {
                if ($this->returnData['username']) {
                    $this->params['update'] = 1;
                    $this->params['updateParam'][] = array('姓名', $value);
                }
                else {
                    $this->params['add'] = 1;
                    $this->params['addParam'][] = array('姓名', $value);
                }
            }
            
            if ($key == 'phoneNum' && $value != $this->returnData['phoneNum']) {
                if ($this->returnData['username']) {
                    $this->params['update'] = 1;
                    $this->params['updateParam'][] = array('手机号', $value);
                }
                else {
                    $this->params['add'] = 1;
                    $this->params['addParam'][] = array('手机号', $value);
                }
            }
            
            if ($key != 'username' && $key != 'phoneNum') $requestTagArray[] = $this->tagModel->getUserProfileName($key);
            if (!isset($this->returnData['userProfile'][$key]) && $key != 'username' && $key != 'phoneNum') {
                $this->params['add'] = 1;
                $this->params['addParam'][] = array($this->tagModel->getUserProfileName($key), $value);
            }
            if (isset($this->returnData['userProfile'][$key]['value']) && $value != $this->returnData['userProfile'][$key]['value']) {
                $this->params['update'] = 1;
                $this->params['updateParam'][] = array($this->returnData['userProfile'][$key][$key], $value);
            }
        }
        
        $this->params['add'] = $this->params['add'] ? $this->params['add'] : 0;
        $this->params['update'] = $this->params['update'] ? $this->params['update'] : 0;
        
        $this->contactModel->update($userId, $this->params);
        if ($this->params['add']) {
            $addEventContent = '新增信息';
            foreach ($this->params['addParam'] as $addIndex) {
                $addEventContent .= ' '.'#'.$addIndex[0].' '.$addIndex[1];
            }
        }
        if ($this->params['update']) {
            $updateEventContent = '更新信息';
            foreach ($this->params['updateParam'] as $updateIndex) {
                $updateEventContent .= ' '.'#'.$updateIndex[0].' '.$updateIndex[1];
            }
        }
        $this->userModel->createEvent($GLOBALS['userId'], $userId, $addEventContent.' '.$updateEventContent, '', self::SYSTEM_EVENT);
        
        foreach ($requestTagArray as $value) $this->userModel->bindTag($userId, $value);
        
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();        
    }
    
    /**
     * 根据完整的手机号，检测其对于联系人是否已经被创建
     *
     * @access public
     * @param string phoneNum 待检测电话号码
     * @return string
     */
    public function checkContactExistByPhoneNum() {
        $this->checkAccessToken();
        $this->params['phoneNum'] = $this->params['phoneNum'];
    
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        if (FValidator::telNum($this->params['phoneNum']) || FValidator::phone($this->params['phoneNum'])) {
            $contactId = $this->contactModel->getContactIdByPhoneNum($this->params['phoneNum']);
            F::rest()->show_result($contactId);
        }else throw new Exception('待检测电话不是一个有效的电话号码', 103);
    }
    
    #根据联系人姓名，返回联系人相关信息
    public function seach() {
        $this->params['name'] = F::request('name', '');
        $this->params['seachType'] = F::request('type', 0);
//         $this->returnData = $this->params['seachType'] == 0 ? $this->contactModel->seach($this->params['name']) : $this->contactModel->seachAll($this->params['name']); 
        $this->returnData = $this->contactModel->seach($this->params['name']);
        F::rest()->show_result($this->returnData);
    }
}

?>