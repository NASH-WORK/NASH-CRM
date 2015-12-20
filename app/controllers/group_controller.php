<?php
/**
 * 群组controller
 * 
 * @author zhaoguang
 * @version 1.1
 */
final class group_controller extends \base_controller
{
    /**
     * 群组model
     * @var group_model
     */
    private $groupModel;
    
    /**
     * 用户model
     * @var user_model
     */
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->groupModel = F::load_model('group', array());
        $this->userModel = F::load_model('user', array());
    }

    public function __destruct()
    {
        unset($this->groupModel);
        unset($this->userModel);
    }
    
    #根据群组名称搜索
    public function seachByName() {
        $this->checkAccessToken();
        $this->params['name'] = F::request('name', '');
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 20);
        
        $this->returnData = $this->params['name'] ?
                            $this->groupModel->seachGroupListByName($this->params['name'], $this->params['page'], $this->params['num']) :
                            $this->groupModel->getAcctionGroupList($GLOBALS['userId'], $this->params['page'], $this->params['num']);
        
        F::rest()->show_result($this->returnData);
    }
    
    #获取群组基本信息
    public function get() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id'));
        
        #检查当前用户是否具有查看该群组权限
        if ($this->userModel->isEnableOpen($GLOBALS['userId'], $this->params['id'], 3)) {
            $this->returnData = $this->groupModel->setGroupId($this->params['id'])->get();
        }else {
            $this->returnData = array();
        }
        
        F::rest()->show_result($this->returnData);
    }
    
    #获取群组事件列表
    public function getEventList() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id'));
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 20);
        
        $this->returnData = $this->groupModel->setGroupId($this->params['id'])->getEventList($this->params['page'], $this->params['num']);
        #补充当前用户id
        foreach ($this->returnData as &$value) {
            $value['currentUserId'] = $GLOBALS['userId'];
        }
        
        F::rest()->show_result($this->returnData);
    }
    
    #追加群组事件
    public function createEvent() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id', 'event'));
        $this->params['photo'] = F::request('photo', '');
        str_replace('＃', '#', str_replace('＠', '@', $this->params['event']));
        
        $this->groupModel->setGroupId($this->params['id'])->createEvent($GLOBALS['userId'], $this->params['event'], $this->params['photo']);
        F::rest()->show_result();
    }
    
    #检查群组名称是否可用
    public function checkGroupName() {
        $this->params = $this->require_params(array('name'));
        
        $this->returnData = $this->groupModel->getGroupIdByName($this->params['name']);
        F::rest()->show_result($this->returnData ? FALSE : TRUE );
    }
    
    #创建群组
    public function create() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('name'));
        $this->params['groupType'] = F::request('groupType', 0);
        $this->params['groupProject'] = F::request('groupProject', 0);
        
        $this->returnData = $this->groupModel->create($GLOBALS['userId'], $this->params['name'], $this->params['groupType'], $this->params['groupProject']);
        F::rest()->show_result($this->returnData);
    }
    
    #删除群组内关系人员
    public function deleteRelation() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('userId', 'groupId'));
        
        $this->groupModel->setGroupId($this->params['groupId'])->deleteRelation($this->params['userId'], $GLOBALS['userId']);
        F::rest()->show_result();
    }
    
    #添加群组内人员
    public function addRelation() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('groupId', 'userId', 'type'));
        $this->groupModel->setGroupId($this->params['groupId'])->addRelation($GLOBALS['userId'], $this->params['userId'], $this->params['type']);
        
        #补充对应联系人的信息
        $this->userModel->createEvent(
            $GLOBALS['userId'], $this->params['userId'], 
            $this->getEventContentByRelationType($this->params['type'], $this->params['groupId'])
        ); 
        F::rest()->show_result();
    }
    
    #根据关系类型，生成相关追加到联系人的事件内容
    private function getEventContentByRelationType($relationType, $groupId) {
        #获取群组类型
        $groupInfo = $this->groupModel->setGroupId($groupId)->get();
        return $groupInfo['groupType'] == '' 
            ? $this->getEventContentByRelationTypeForGroup($relationType, $groupInfo['name']) 
            : $this->getEventContentByRelationTypeForRoom($relationType, $groupInfo);
    }
    
    #返回群组追加事件内容
    private function getEventContentByRelationTypeForGroup($relation, $groupName) {
        return '绑定群组'.$groupName.', 关系#'.$relation;
    }
    
    #返回房间追加事件内容
    private function getEventContentByRelationTypeForRoom($relation, array $groupInfo) {
        $projectName = $groupInfo['groupProject']['name'] == '未知' ? '' : $groupInfo['groupProject']['name'];
        $roomNum = '';
        foreach ($groupInfo['tagList'] as $groupInfoTagListIndex) {
//             F::showLog($groupInfoTagListIndex['name']);
            if (preg_match('/^R\w{1,}/', $groupInfoTagListIndex['name'])) {
                $roomNum = $groupInfoTagListIndex['name'];
//                 F::showLog($roomNum);
                break;
            }
        }
        return '绑定房间'.'#'.$projectName.' #'.$roomNum.' ,关系#'.$relation;
    }
    
    #获取群组内关系列表类型
    public function getGroupRelationTypeList() {
        $this->returnData = array(
            array('name' => '业主', 'color' => 'white-text amber lighten-2'),
            array('name' => '租户', 'color' => 'white-text light-blue lighten-2'),
            array('name' => '看房者', 'color' => 'white-text purple lighten-2'),
            array('name' => '成员', 'color' => 'white-text green darken-2')
        );
        F::rest()->show_result($this->returnData);
    }
    
    #删除事件
    public function callbackEvent() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id'));
        $this->groupModel->deleteEvent($this->params['id'], $GLOBALS['userId']);
        F::rest()->show_result();
    }
    
    #更新群组信息
    public function update() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id', 'name'));
        $this->groupModel->setGroupId($this->params['id'])->update($this->params, $GLOBALS['userId']);
        F::rest()->show_result();
    }
    
    #房间信息搜索
    public function seachRoomByName() {
        $this->checkAccessToken();
        $this->params['name'] = F::request('name', '');
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 20);
        
        $this->returnData = $this->groupModel->seachGroupRoomListByName($this->params['name'], $this->params['page'], $this->params['num'], $GLOBALS['userId']);
        F::rest()->show_result($this->returnData);
        
    }
}

?>