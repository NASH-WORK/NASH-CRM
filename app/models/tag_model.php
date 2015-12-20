<?php
/**
 * 标签model
 * 
 * @author zhaoguang
 * @version 1.0
 */
final class tag_model extends \base_model
{
    const ROOM_COLOR = 'white-text blue-grey lighten-2';
    
    /**
     * 用户model
     * @var user_model
     */
    private $userModel;
    
    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        //$this->userModel = F::load_model('user');
    }
    
    /**
     * (non-PHPdoc)
     * @see base_model::__destruct()
     */
    public function __destruct() {
        parent::__destruct();
        //unset($this->userModel);
    }
    
    /**
     * 创建标签
     * 
     * @access public
     * @param string name 标签名称
     * @param int $type 标签类型
     * @param string $color 标签显示信息
     * @param int $createUserId 创建用户id
     * @param string $keyName 用户信息上传使用name,只有type＝10时有效
     * @return int
     */
    public function create($name, $type, $color, $createUserId, $keyName = '') {
        #获取用户创建权限
        $accountType = F::db()->query('select type from account where user_id = ? and status = 1', array($createUserId))->fetch_column('type');
        if ($accountType != 3) throw new Exception('权限不足，只有内容人员才可创建系统标签', 202);
        
        #根据标签名称判断是否需要创建
        $tagId = $this->tagExist($name);
        if (!$tagId) {
            #创建标签
            $tagId = F::db()->execute('insert into tag set name = ?, create_time = ?, color = ?, type = ?, form_key = ?', 
                array($name, date(self::DEFAULT_TIME_STYLE), $color, $type, $keyName))->insert_id();
        }
        
        return $tagId;
    }
    
    /**
     * 判断一个标签是否意见存在
     * 
     * @access private
     * @param string $name 标签名称
     * @return boolean
     */
    private function tagExist($name) {
        $tagId = F::db()->query('select id from tag where name = ? limit 1', array($name))->fetch_column('id');
        return $tagId ? TRUE : FALSE;
    }
    
    #获取tag的详细信息
    public function getTagInfo($tagId) {
        $tagInfo = F::db()->fetch('select id, name, type from tag where id = ? limit 1', array($tagId));
        return is_array($tagInfo) ? $tagInfo : array();
    }
     
    /**
     * 根据标签名称，获取标签id
     * 
     * @access public
     * @param string name 标签名称
     * @return int | boolean
     */
    public function getTagIdByName($name) {
        $tagId = F::cache()->get('vstone_tagModel_getTagIdByName_tagName:'.$name);
        if (!$tagId) {
            #缓存不存在, 查询数据
            $tagId = F::db()->query('select id from tag where name = ? limit 1', array($name))->fetch_column('id');
            if (!$tagId) {
                if ($name) {
                    if (preg_match('/^R\d{1,}/', $name)) $color = self::ROOM_COLOR;
                    else $color = '';
                    $tagId = F::db()->execute('insert into tag set name = ?, color = ?, type = 1000', array($name, $color))->insert_id();
                }
            }
            #写入缓存纪录
            F::cache()->add('vstone_tagModel_getTagIdByName_tagName:'.$name, $tagId, FALSE, self::DEFAULT_ONE_DAY); 
        }
        return $tagId ? $tagId : FALSE;
    }
    
    /**
     * 获取标签下所有用户list集合
     * 
     * @access public
     * @param string name 标签名称
     * @return array
     */
    public function getUserList($tagName) {
        $userList = F::cache()->get('vstone_tagModel_getUserList_name:'.$tagName);
        #查询缓存数据
        if ($userList) {
            $userList = unserialize($userList);
        }else {
            #获取标签id
            $tagId = $this->getTagIdByName($tagName);
            if ($tagId) {
                #获取标签下用户列表
                $userListTmp = F::db()->fetch_all('select user_tag.user_id as user_id from user_tag left join account on user_tag.user_id = account.user_id where tag_id = ? and (type = 2 or type = 10)', array($tagId));
                foreach ($userListTmp as $value) $userList[] = $value['user_id'];
            }else $userList = array();
            #增加相关缓存信息
            F::cache()->add('vstone_tagModel_getUserList_name:'.$tagName, serialize($userList), FALSE, self::DEFAULT_ONE_DAY);
        }

        return $userList;
    }
    
    /**
     * 根据部分房间号搜索相关所有房间号列表
     * 
     * @access public
     * @param string $keyword
     * @return array
     */
    public function seachRoomNameByKeyword($keyword) {
        $roomList = F::db()->query('select name from tag where name like '."'R%$keyword%'")->fetch_column_all('name');
        return is_array($roomList) ? $roomList : array();
    }
    
    /**
     * 获取具有相关标签id的user id数组
     * 
     * @access public
     * @param array $tagId
     * @return array
     */
    public function getUserListByTadId(array $tagId) {
        if (empty($tagId)) return array();
        
        $userIdArray = F::db()->query('select user_id from user_tag where tag_id in ('.implode(',', $tagId).')')->fetch_column_all('user_id');
        return $userIdArray;
    }
    
    /**
     * 获取标签列表
     * 
     * @access public
     * @return array
     */
    public function getList() {
        $listTmp = F::db()->fetch_all('select name, color, type from tag order by type');
        foreach ($listTmp as &$value) {
            $value['color'] = $value['color'] ? $value['color'] : 'white-text grey';
            $list[$value['type']][] = $value;
        }
        unset($listTmp);
        
        return is_array($list) ? $list : array();
    }
    
    /**
     * 创建系统创建的非用户信息标签
     * 
     * @access public
     * @return array
     */
    public function getListBySystem() {
        $listTmp = F::db()->fetch_all('select name, color, type from tag where type < 1000 and type != 10 order by type');
        foreach ($listTmp as $value) $list[$value['type']][] = $value;
        unset($listTmp);
        
        return is_array($list) ? $list : array();
    }
    
    /**
     * 获取用户profile标签列表
     * 
     * @access public
     * @return array
     */
    public function getUserProfileList() {
        $listTmp = F::db()->fetch_all('select name, color, form_key as formKey from tag where type = 10');
        foreach ($listTmp as $value) $list[$value['color']][] = $value;
        unset($listTmp);
        
        return is_array($list) ? $list : array();
    }
    
    #获取群组profile标签列表
    public function getGroupProfileList() {
        $listTmp = F::db()->fetch_all('select name, color, form_key as formKey from tag where type = 11');
        foreach ($listTmp as $value) $list[$value['color']][] = $value;
        unset($listTmp);
        
        return is_array($list) ? $list : array();
    }
    
    /**
     * 根据form表单的key获取真正显示name
     * 
     * @access public
     * @param string $key
     * @return string
     */
    public function getUserProfileName($key) {
        $name = F::db()->query('select name from tag where form_key = ? and type = 10', array($key))->fetch_column('name');
        return $name ? $name : '';
    }
    
    /**
     * 获取标签的显示样式信息
     * 
     * @access public
     * @param string $tagName
     * @return string
     */
    public function getStyle($tagName) {
        $style = F::db()->query('select color from tag where name = ? limit 1', array($tagName))->fetch_column('color');
        $style = $style ? $style : 'white-text grey';
        return $style;
    }
    
    /**
     * 获取所有系统创建的和用户profile属性标签列表
     * 
     * @access public
     * @return array
     */
    public function getListByAllSystem() {
        $listTmp = F::db()->fetch_all('select name, color, type from tag where type < 1000 order by type');
        foreach ($listTmp as $value) $list[$value['type']][] = $value;
        unset($listTmp);
        
        return is_array($list) ? $list : array();
    }
    
    /**
     * 获取全部项目信息列表
     * 
     * @access public
     * @return array
     */
    public function getALLProjectInfo() {
        $list = F::db()->fetch_all('select id, name from tag where type = 2');
        return is_array($list) ? $list : array();
    }
}

?>