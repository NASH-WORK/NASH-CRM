<?php
/**
 * 群组model
 * 
 * @author zhaoguang
 * @version 1.1
 */
final class group_model
{
    const GROUP_EVENT_TYPE = 3;
    
    /**
     * 群组id
     * @var int
     */
    private $id;
    
    /**
     * 群组信息
     * @var array
     */
    private $groupInfo;
    
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
    
    public function __construct(array $config)
    {
        $this->id = $config['groupId'] ? $config['groupId'] : 0;
    }

    public function __destruct()
    {
        unset($this->userModel);
        unset($this->groupInfo);
        unset($this->tagModel);
    }
    
    /**
     * 设置群组id
     * 
     * @param int $id 群组id
     * @return group_model
     */
    public function setGroupId($id) {
        $this->id = $id;
        return $this;
    }
    
    #获取群组基本信息
    public function get() {
        #获取群组名称
        $this->groupInfo = F::db()->fetch('select id, name, list, profile, group_type as groupType, group_project as groupProject, create_user_id as createUserId from `group` where id = ? limit 1', array($this->id));
        #获取群组标签列表
        $groupInfo['name'] = $this->groupInfo['name'];
        #获取群组类型
        $groupInfo['groupType'] = $this->groupInfo['groupType'];
        #获取群组所属项目
        $groupInfo['groupProject'] = $this->getProjectInfoById($this->groupInfo['groupProject']);
        #群组创建者信息
        $groupInfo['createUserId'] = $this->groupInfo['createUserId'];
        
        #获取群组操作列表
        $groupInfo['tagList'] = $this->getGroupTagList($this->id);
        #获取群组操作列表
        $groupInfo['acctionList'] = $this->getGroupAcctionUserList($this->id);
        #获取群组内关系列表
        $groupInfo['relation'] = $this->getGrouoRelation();
        #获取群组信息
        $groupProfile = $this->groupInfo['profile'] ? json_decode($this->groupInfo['profile'], TRUE) : array(); 
        $groupInfo['profile'] = array();
        foreach ($groupProfile as $key => $value) {
            $groupInfo['profile'][$key] = array(
                'showName' => $this->getTagNameByFormKey($key),
                'showValue' => $value,
                'formKey' => $key 
            );
        }
        
        return $groupInfo;
    }
    
    #根据tagId获取项目信息
    private function getProjectInfoById($id) {
        $this->tagModel = F::load_model('tag', array());
        $projectInfo = $this->tagModel->getTagInfo($id);
        return $projectInfo['type'] == 2 ? $projectInfo : array('id' => $id, 'name' => '未知');
    }
    
    #获取群组列表信息
    public function getEventList($page, $num) {
        $list = $this->getGroupEventList($this->id, $page, $num);
        
        $this->userModel = F::load_model('user', array());
        foreach ($list as &$value) {
            $userProfile = $this->userModel->get($value['createUserId']);
            $value['createUserInfo'] = array('userId' => $value['createUserId'], 'nickname' => $userProfile[0]['nickname']);
            unset($userProfile);
            unset($value['createUserId']);
        }
        
        return is_array($list) ? $list : array();
    }
    
    #获取群组关系信息
    private function getGrouoRelation() {
        $groupRelation = F::db()->fetch_all('select user_id as userId, remark from group_list where group_id = ?', array($this->id));
        
        $this->userModel = F::load_model('user', array());
        $relation = array();
        foreach ($groupRelation as $value) {
            $userProfile = $this->userModel->get($value['userId']);
            $relation[$value['remark']][] = array(
                'userId' => $value['userId'],
                'nickname' => $userProfile[0]['nickname'],
                'mobile' => $userProfile[0]['phone_num']
            );
        }
        
        return $relation;
    }
    
    #根据群组名称搜索群组列表
    public function seachGroupListByName($name, $page, $num) {
        #获取群组列表
        $list = F::db()->fetch_all('select id, name from `group` where name like ? and status = 1 order by id desc limit ?, ?', 
            array('%'.$name.'%', ($page-1)*$num, $num));
        foreach ($list as &$value) {
            #获取群组标签列表
            $value['tagList'] = $this->getGroupTagList($value['id']);
            #获取群组操作列表
            $value['acctionList'] = $this->getGroupAcctionUserList($value['id']);
            #获取群组最后一则事件信息
            $value['lastEvent'] = $this->getGroupEventList($value['id'], 1, 1);
            $value['lastEvent'] = $value['lastEvent'][0];
        }
        
        return is_array($list) ? $list : array();
    }
    
    #获取用户操作过的所有群组列表
    public function getAcctionGroupList($userId, $page, $num) {
        #获取群组列表
        $list = F::db()->fetch_all('select id, name from `group` where create_user_id = ? and status = 1 order by id desc limit ?, ?',
            array($userId, ($page-1)*$num, $num));
        
        foreach ($list as &$value) {
            #获取群组标签列表
            $value['tagList'] = $this->getGroupTagList($value['id']);
            #获取群组操作列表
            $value['acctionList'] = $this->getGroupAcctionUserList($value['id']);
            #获取群组最后一则事件信息
            $value['lastEvent'] = $this->getGroupEventList($value['id'], 1, 1);
            $value['lastEvent'] = $value['lastEvent'][0];
        }
        
        return is_array($list) ? $list : array();
    }
    
    #获取群组标签列表
    private function getGroupTagList($groupId) {
        $list = F::db()->fetch_all('select tag.id as id, name, color as tagClass, tag.type as type from tag left join group_tag on tag.id = group_tag.tag_id where group_tag.group_id = ? group by group_tag.tag_id', array($groupId));
        return is_array($list) ? $list : array();
    }
    
    #获取群组操作成员列表
    private function getGroupAcctionUserList($groupId) {
        $list = F::db()->fetch_all('select create_user_id as id from event where user_id = ? and type = ? and status = 1 group by create_user_id', 
            array($groupId, self::GROUP_EVENT_TYPE));
        
        $this->userModel = F::load_model('user', array());
        foreach ($list as &$value) {
            $userProfile = $this->userModel->get($value['id']);
            $value['nickname'] = $userProfile[0]['nickname'];
            unset($userProfile);
        }
        
        return is_array($list) ? $list : array();
    }
    
    #获取群组事件列表
    private function getGroupEventList($groupId, $page, $num) {
        $list = F::db()->fetch_all('select id, create_user_id as createUserId, content as contact, photo, create_time as createTime from event where user_id = ? and type = ? and status = 1 order by id desc limit ?, ?', 
            array($groupId, self::GROUP_EVENT_TYPE, ($page-1)*$num, $num));
        return is_array($list) ? $list : array();
    }
    
    #生成群组事件
    public function createEvent($createUserId, $event, $photo = '') {
        if ($photo) $event .= ' #图片';
        
        #生成事件信息
        $this->userModel = F::load_model('user', array());
        $eventId = $this->userModel->createEvent($createUserId, $this->id, $event, $photo, self::GROUP_EVENT_TYPE);
        
        #绑定tag信息
        $this->bindTagByString($event, $eventId);
        
        return $eventId;
    }
    
    #根据事件内容,给群组绑定相关tag信息
    private function bindTagByString($event, $eventId) {
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $event, $result);
        
        $this->tagModel = F::load_model('tag', array());
        foreach ($result[1] as $value) {
            $tagId = $this->tagModel->getTagIdByName($value);
            $this->bindTag($tagId, $eventId);
        }
    }
    
    #绑定群组标签
    private function bindTag($tagId, $eventId) {
        F::db()->execute('insert into group_tag set group_id = ?, tag_id = ?, bind_time = ?, event_id = ?', array($this->id, $tagId, date(TIMESTYLE), $eventId));
    }
    
    #判断群组是否具有某标签
    private function hadBindTag($tagId) {
        $bindTime = F::db()->query('select bind_time as bindTime from group_tag where group_id = ? and tag_id = ? limit 1', array($this->id, $tagId))->fetch_column('bindTime');
        return $bindTime ? TRUE : FALSE;
    }
    
    #根据群组名称获取群组id
    public function getGroupIdByName($name) {
        $this->id = F::db()->query('select id from `group` where name = ? and status = 1 limit 1', array($name))->fetch_column('id');
        return $this->id ? $this->id : FALSE;
    }
    
    #创建群组
    public function create($createUserId, $name, $groupType = 1, $groupProject = 0) {
        $this->id = F::db()->execute('insert into `group` set name = ?, create_user_id = ?, create_time = ?, status = 1, group_type = ?, group_project = ?', 
            array($name, $createUserId, date(TIMESTYLE), $groupType, $groupProject ))->insert_id();
        return $this->id;
    }
    
    #删除群组成员
    public function deleteRelation($deleteUserId, $createUserId = 0) {
        $relationDetail = $this->getRelationDetail($deleteUserId);
        F::db()->execute('delete from group_list where group_id = ? and user_id = ?', array($this->id, $deleteUserId));
        
        #补充对应事件
        $this->userModel = F::load_model('user', array());
        $addUserInfo = $this->userModel->get($deleteUserId);
        $eventId = $this->createEvent($createUserId, '删除#'.$relationDetail['remark'].' '.$addUserInfo[0]['nickname']);
        
        #删除因事件致使群组拥有的标签
        $this->unbindTagByDeleteEvent($relationDetail['eventId']);
        $this->unbindTagByDeleteEvent($eventId);
    }
    
    #获取群组关系详情
    private function getRelationDetail($userId) {
        $relationDetail = F::db()->fetch('select remark, event_id as eventId from group_list where group_id = ? and user_id = ? limit 1', array($this->id, $userId));
        return is_array($relationDetail) ? $relationDetail : array();
    }
    
    #添加群组成员
    public function addRelation($createUserId, $addUserId, $remark) {
        #补充对应事件
        $this->userModel = F::load_model('user', array());
        $addUserInfo = $this->userModel->get($addUserId);
        $eventId = $this->createEvent($createUserId, '添加#'.$remark.' '.$addUserInfo[0]['nickname']);
        
        F::db()->execute('replace into group_list set group_id = ?, user_id = ?, add_user_id = ?, remark = ?, add_time = ?, event_id = ?', 
            array($this->id, $addUserId, $createUserId, $remark, date(TIMESTYLE), $eventId));
    }
    
    #删除群组事件
    public function deleteEvent($id, $currentUserId) {
        #判断删除权限
        if (!$this->checkDeleteEventLevel($currentUserId, $id)) throw new Exception('删除权限不足', 205);
        
        F::db()->begin();
        #删除事件
        F::db()->execute('update event set status = 0 where id = ? and type = 3', array($id));
        #回收标签
        $this->unbindTagByDeleteEvent($id);
        F::db()->commit();
    }
    
    #因为删除事件造成的标签解绑
    private function unbindTagByDeleteEvent($id) {
        F::db()->execute('delete from group_tag where event_id = ?', array($id));
    }
    
    #判断删除劝阻事件权限
    private function checkDeleteEventLevel($userId, $id) {
        $eventInfo = $this->getGroupEventDetail($id);
        return $eventInfo['create_user_id'] == $userId ? TRUE : FALSE;
    }
    
    #获取群组事件的详细
    private function getGroupEventDetail($id) {
        $eventInfo = F::db()->fetch('select create_user_id from event where id = ? and status = 1 and type = 3 limit 1', array($id));
        return is_array($eventInfo) ? $eventInfo : array();
    }
    
    #更新群组信息
    public function update($updateInfo, $createUserId) {
        $groupInfo = $this->get();
        
        #释放无用的请求信息
        $groupName = $updateInfo['name'];
        $groupType = $updateInfo['group_type'];
        $groupProject = $updateInfo['group_project'];
        unset($updateInfo['r']);unset($updateInfo['accessToken']);unset($updateInfo['id']);
        unset($updateInfo['name']);unset($updateInfo['group_type']);unset($updateInfo['group_project']);
        
        #检查是否存在跟新信息
        if ($groupName != $groupInfo['name']) {
            $updateTmp['hadUpdate'] = TRUE;
            $updateTmp['updateParam']['群组名称'] = $groupName;
        }
        if ($groupType != $groupInfo['groupType']) {
            $updateTmp['hadUpdate'] = TRUE;
            $updateTmp['updateParam']['群组类型'] = $groupType == 1 ? '房间' : '普通群组';
        }
        if ($groupProject != $groupInfo['groupProject']['id']) {
            $updateTmp['hadUpdate'] = TRUE;
            $projectInfo = $this->getProjectInfoById($groupProject);
            $updateTmp['updateParam']['群组所属项目'] = $projectInfo['name'];
        }
        foreach ($updateInfo as $key => $value) {
            if ($value != $groupInfo['profile'][$key]['showValue']) {
                $updateTmp['hadUpdate'] = TRUE;
                $updateTmp['updateParam'][$key] = $value;
            }
        }
        
        #更新群组信息
        if ($updateTmp['hadUpdate']) {
            F::db()->execute('update `group` set name = ?, profile = ?, group_type = ?, group_project = ? where id = ?', 
                array($groupName, json_encode($updateInfo), $groupType, $groupProject, $this->id));
            #追加更新事件
            $event = '更新';
            foreach ($updateTmp['updateParam'] as $key => $value) {
                if ($key == '群组名称' || $key == '群组类型' || $key == '未知') {
                    $event = $event.' '.$this->getTagNameByFormKey($key).' '.$value.' ';
                }elseif ($key == '群组所属项目') {
                    $event = $event.' '.$this->getTagNameByFormKey($key).' #'.$value.' ';
                }else {
                    $event = $event.' #'.$this->getTagNameByFormKey($key).' '.$value.' ';
                }
            }
            $this->createEvent($createUserId, $event);
        }
        return TRUE;
    }
    
    #根据表单提交key获取对应显示name
    private function getTagNameByFormKey($key) {
        if ($key == '群组名称') return '群组名称';
        if ($key == '群组类型') return '群组类型';
        if ($key == '群组所属项目') return '群组所属项目';
        
        $name = F::db()->query('select name from tag where form_key = ? limit 1', array($key))->fetch_column('name');
        return $name ? $name : '未知';
    }
    
    #获取用户所属项目
    private function getUserProjectId($userId) {
        $this->userModel = F::load_model('user', array());
        $allUserTag = $this->userModel->getUserTagList($userId);
        
        $retuanData = array();
        foreach ($allUserTag as $value) {
            if ($value['type'] == 2) $retuanData[] = $value['tagId'];
        }
        
        unset($allUserTag);
        return $retuanData;
    }
    
    #房间信息搜索
    public function seachGroupRoomListByName($name, $page, $num, $userId = 0) {
        #获取房间群组列表
        if ($name) {
            $list = F::db()->fetch_all('select id, name from `group` where name like ? and status = 1 and group_type = 1 order by id desc limit ?, ?',
                array('%'.$name.'%', ($page-1)*$num, $num));
        }else {
            #获取用户所属项目
            $userProjectInfo = $this->getUserProjectId($userId);
            if (empty($userProjectInfo)) return array();
            
            $list = F::db()->fetch_all('select id, name from `group` where status = 1 and group_type = 1 and group_project in (?) order by id desc limit ?, ?',
                array(implode(',', $userProjectInfo), ($page-1)*$num, $num));
        }
        
        foreach ($list as &$value) {
            #获取群组标签列表
            $value['tagList'] = $this->getGroupTagList($value['id']);
            #获取群组操作列表
            $value['acctionList'] = $this->getGroupAcctionUserList($value['id']);
            #获取群组最后一则事件信息
            $value['lastEvent'] = $this->getGroupEventList($value['id'], 1, 1);
            $value['lastEvent'] = $value['lastEvent'][0];
        }
        
        return is_array($list) ? $list : array();
    }
}

?>