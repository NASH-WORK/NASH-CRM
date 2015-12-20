<?php
/**
 * 用户controller
 * 提供登陆
 *
 * @author zhaoguang
 * @version 1.0
 */
final class user_controller extends \base_controller
{
    const IMG_PATH_BASE = '../../test/crm/img/';
    const IMG_PATH_URL_BASE = 'test/crm/img/';
    const CRM_ADMIN_BASE = 'test/crmAdmin/';

    /**
     * 内部人员账户
     * @var int
     */
    const ADMIN_ACCOUNT = 1;

    /**
     * 业主账户
     * @var int
     */
    const OWNER_ACCOUNT = 2;

    /**
     * 面试账户
     * @var int
     */
    const AUDITION_ACCOUNT = 10;

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

    /**
     * 图片处理类库
     * @var photo
     */
    private $photoTool;

    /**
     * 群组model
     * @var group_model
     */
    private $groupModel;

    /**
     * 联系人model
     * @var contact_model
     */
    private $contactModel;

    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = F::load_model('user');
        $this->tagModel = F::load_model('tag');
        $this->photoTool = F::loadClass('photo');
        $this->contactModel = F::load_model('contact', array());
    }

    /**
     * (non-PHPdoc)
     * @see base_controller::__destruct()
     */
    public function __destruct() {
        unset($this->userModel);
        unset($this->tagModel);
        unset($this->photoTool);
        unset($this->groupModel);
        unset($this->contactModel);
    }

    /**
     * 注册内部管理账户
     *
     * @access public
     * @param string phoneNum 手机号
     * @param string password 登陆密码md5
     * @return array
     */
    public function create() {
        #参数检查
        $this->params = $this->require_params(array('phoneNum', 'password'));
        $this->params['nickname'] = F::request('nickname', '');
        $this->params['project'] = F::request('project', '');
        $this->params['project'] = str_replace('，', ',', $this->params['project']);

        if (!FValidator::phone($this->params['phoneNum'])) throw new Exception('手机号格式非法', 100);
        #检查手机是否已经注册
        $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
        if (!$userId) {
            #生成一个新的账户
            $userId = $this->userModel->create($this->params['phoneNum'], $this->params['password'], self::ADMIN_ACCOUNT);
        }else {
            #更新账户密码信息
            $updateResult = $this->userModel->updatePassword($userId, $this->params['password']);
        }

        #保存图片信息
        $filePath = 'avatar/'.$userId.'_'.md5_file($_FILES['photo']['tmp_name']).'.'.end(explode('.', $_FILES['photo']['name']));
        $this->photoTool->copyImageWithSize($_FILES['photo']['tmp_name'], self::IMG_PATH_BASE.$filePath, 60, 60);
        $this->params['photo'] = $filePath;

        #更新用户昵称
        $this->userModel->updateUserProfile($userId, $this->params['nickname'], $filePath);
        #授权相关项目授权
        $this->userModel->getProjectAuth($userId, explode(',', $this->params['project']));
        #跳转回user_add页面
        header('location:http://'.$_SERVER['HTTP_HOST'].'/'.self::CRM_ADMIN_BASE.'user_add.php');
    }

    /**
     * 创建账户信息
     *
     * @access public
     * @param string phoneNum
     * @param string password
     * @param string nickname
     * @param string project
     * @return int
     */
    public function createUserAccount() {
        $this->params = $this->require_params(array('phoneNum', 'password'));
        $this->params['nickname'] = F::request('nickname', '');
        $this->params['project'] = F::request('project', '');
        $this->params['project'] = str_replace('，', ',', $this->params['project']);

//         if (!FValidator::phone($this->params['phoneNum'])) throw new Exception('手机号格式非法', 100);
        #检查手机是否已经注册
        $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
        if (!$userId) {
            #生成一个新的账户
            $userId = $this->userModel->create($this->params['phoneNum'], $this->params['password'], self::ADMIN_ACCOUNT);
        }else {
            #更新账户密码信息
            $updateResult = $this->userModel->updatePassword($userId, $this->params['password']);
        }

        #更新用户昵称
        $this->userModel->updateUserProfile($userId, $this->params['nickname']);
        #授权相关项目授权
        $this->userModel->getProjectAuth($userId, explode(',', $this->params['project']));

        F::rest()->show_result($userId);
    }

    /**
     * 更新用户照片信息
     *
     * @access public
     * @param int $userId
     * @param string $photo
     * @return boolean
     */
    public function updateUserPhoto() {
        $this->params = $this->require_params(array('userId', 'photo'));

        $userInfo = $this->userModel->get($this->params['userId']);
        $this->params['nickname'] = $userInfo[0]['nickname'];
        unset($userInfo);

        $this->userModel->updateUserProfile($this->params['userId'], $this->params['nickname'], $this->params['photo']);
        F::rest()->show_result();
    }

    /**
     * 注册新的联系人
     *
     * @access public
     * @param string phoneNum
     * @param string username
     * @param string event
     * @return void
     */
    public function createV2() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('phoneNum', 'username', 'event'));
        $this->params['systemEvent'] = F::request('systemEvent', 0);
        //if (!FValidator::phone($this->params['phoneNum'])) throw new Exception('手机号格式非法', 100);

        #检查手机是否已经注册
        $userId = $this->contactModel->getContactIdByPhoneNum($this->params['phoneNum']);
        if (!$userId) {
            #生成一个新的账户
            $userId = $this->userModel->create($this->params['phoneNum'], $this->params['username'], self::OWNER_ACCOUNT, $GLOBALS['userId']);
            #更新用户昵称
            $this->userModel->updateUserProfile($userId, $this->params['username']);
        }

        #书写对应事件
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $this->params['event'], $result);
        foreach ($result[1] as $value) {
            $this->userModel->bindTag($userId, $value);
        }

        $eventType = $this->params['systemEvent'] ? self::SYSTEM_EVENT : self::USER_EVENT;
        $this->userModel->createEvent($GLOBALS['userId'], $userId, $this->params['event'], '', $eventType);
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($userId);
    }

    /**
     * 注册新的面试人员信息
     *
     * @access public
     * @param string phoneNum
     * @param string username
     * @return void
     */
    public function createV3() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('phoneNum', 'username'));

        #生成一个新的账户
        $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
        if (!$userId) {
            $userId = $this->userModel->create($this->params['phoneNum'], $this->params['username'], self::AUDITION_ACCOUNT);
            #更新用户昵称
            $this->userModel->updateUserProfile($userId, $this->params['username']);
        }

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($userId);
    }

    /**
     * 登陆
     *
     * @access public
     * @param string phoneNum 手机号
     * @param string password 登陆密码md5
     * @return array
     */
    public function login() {
        #参数检查
        $this->params = $this->require_params(array('phoneNum', 'password'));
        //if (!FValidator::phone($this->params['phoneNum'])) throw new Exception('手机号格式非法', 100);
        #获取用户id
        $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
        if (!$userId) throw new Exception('用户不存在', 101);

        #获取用户账户信息
        $accountInfo = $this->userModel->getAccountInfo($userId);
        if ($accountInfo[$this->params['phoneNum']]['password'] != md5(md5($this->params['password']))) throw new Exception('密码错误', 102);
        unset($accountInfo);

        #生成相关授权信息
        $this->returnData = $this->userModel->createToken($userId);
        $this->userLog($userId, __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取用户项目信息
     *
     * @access public
     * @return array
     */
    public function getUserGroupInfo() {
        $this->checkAccessToken();
        $this->returnData = $this->userModel->_getUserTagList($GLOBALS['userId']);

        $returnData = array();
        foreach ($this->returnData as $value) {
            if ($value['type'] == 2) $returnData[] = $value['tagId'];
        }
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result(array('userId' => $GLOBALS['userId'], 'tagList' => implode(',', $returnData)));
    }

    /**
     * 获取用户所在项目的工作圈信息列表
     *
     * @access public
     * @param string tagId
     * @return array
     */
    public function getEventListByGroupId() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('tagId'));
        $this->params['tagId'] = explode(',', $this->params['tagId']);
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 20);

        $this->returnData = $this->userModel->getEventListByGroupId($this->params['tagId'], $this->params['page'], $this->params['num']);
        foreach ($this->returnData as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['eventContent'], $result);
            foreach ($result[0] as $notice) {
                $value['eventContent'] = str_replace($notice, '', $value['eventContent']);
            }
        }
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 绑定标签
     *
     * @access public
     * @param string name 标签名称
     * @return void
     */
    public function bindTag() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('name'));
        $this->params['name'] = explode(',', $this->params['name']);
        #绑定标签
        foreach ($this->params['name'] as $value) $this->userModel->bindTag($GLOBALS['userId'], $value);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();
    }

    /**
     * 根据标签获取用户信息列表
     *
     * @access public
     * @param string name 标签名称
     * @param int page 页数
     * @param int num 每页信息数目
     * @return array
     */
    public function getList() {
        #参数检查
        $this->params = $this->require_params(array('name'));
        $this->params['name'] = explode('&', $this->params['name']);
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        #获取用户列表
        $this->returnData = $this->userModel->getList($this->params['name'], $this->params['page'], $this->params['num']);
        foreach ($this->returnData as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['lastEventInfo'][0]['content'], $result);
            foreach ($result[0] as $notice) {
                $value['lastEventInfo'][0]['content'] = str_replace($notice, '', $value['lastEventInfo'][0]['content']);
            }
        }

        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取内部用户列表，支持关键字搜索
     *
     * @access public
     * @param string $keyword
     * @return array
     */
    public function getUserList() {
        $this->params['keyword'] = F::request('keyword', '');
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 10);

        $this->returnData = $this->userModel->getUserListByKey($this->params['keyword'], $this->params['page'], $this->params['num']);
        F::rest()->show_result($this->returnData);
    }

    /**
     * 根据用户昵称获取用户id
     *
     * @access public
     * @return string
     */
    public function getUserIdByNickname() {
        $this->params = $this->require_params(array('name'));
        $this->returnData = $this->userModel->getUserIdByNickname($this->params['name']);
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取用户事件列表
     *
     * @access public
     * @param int page 页数
     * @param int num 每页信息数目
     * @param string phoneNum 手机号
     * @return array
     */
    public function getUserEventList() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('userId'));
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        #获取当前用户信息
        $userId = $this->params['userId'];
        $currentUserInfo = $this->userModel->get($GLOBALS['userId']);
        $this->returnData['currentUserNickname'] = $currentUserInfo[0]['nickname'];
        $userInfo = $this->userModel->get($userId);
        $this->returnData['seachUserNickname'] = $userInfo[0]['nickname'];
        unset($userInfo);
        unset($currentUserInfo);



        #获取用户事件列表
        $this->returnData['returnData'] = $this->userModel->getUserEventList($userId, $this->params['page'], $this->params['num'], $GLOBALS['userId']);
        foreach ($this->returnData['returnData'] as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['content'], $result);
            foreach ($result[0] as $notice) {
                $value['content'] = str_replace($notice, '', $value['content']);
            }
        }

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取用户所有的事件列表
     *
     * @access public
     * @param int page 页数
     * @param int num 每页信息数目
     * @param string phoneNum 手机号
     * @return array
     */
    public function getUserOwnerEventList() {
        #参数检查
        $this->checkAccessToken();
//         $this->params = $this->require_params(array('phoneNum'));
        $this->params['phoneNum'] = F::request('phoneNum', 0);
        $this->params['id'] = F::request('id', 0);
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        #获取用户id
        if ($this->params['id']) {
            $userId = $this->params['id'];
        }else {
            $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
            if (!$userId) throw new Exception('用户不存在', 101);
        }

        #获取当前用户信息
        $currentUserInfo = $this->userModel->get($GLOBALS['userId']);
        $this->returnData['currentUserNickname'] = $currentUserInfo[0]['nickname'];
        unset($currentUserInfo);

        #获取用户事件列表
        $this->returnData['returnData'] = $this->userModel->getUserOwnerEventList($userId, $this->params['page'], $this->params['num'], $GLOBALS['userId']);
        foreach ($this->returnData['returnData'] as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['content'], $result);
                foreach ($result[0] as $notice) {
                $value['content'] = str_replace($notice, '', $value['content']);
            }
            $value['currentUserId'] = $GLOBALS['userId'];
        }

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取全部事件列表
     *
     * @access public
     * @param int page 页数
     * @param int num 每页信息数目
     * @return array
     */
    public function getEventList() {
        #参数检查
        $this->checkAccessToken();
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        #获取列表
        $this->returnData = $this->userModel->getEventList($GLOBALS['userId'], $this->params['page'], $this->params['num']);
        foreach ($this->returnData as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['eventContent'], $result);
            foreach ($result[0] as $notice) {
                $value['eventContent'] = str_replace($notice, '', $value['eventContent']);
            }
        }

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取用户通知列表
     *
     * @access public
     * @param int page 页数
     * @param int num 每页信息数目
     * @return array
     */
    public function getNoticeList() {
        #参数检查
        $this->checkAccessToken();
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        $this->returnData = $this->userModel->getNoticeList($GLOBALS['userId'], $this->params['page'], $this->params['num']);
        foreach ($this->returnData as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['noticeContent'], $result);
            foreach ($result[0] as $notice) {
                $value['noticeContent'] = str_replace($notice, '', $value['noticeContent']);
            }
        }

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 生成一则事件纪录
     *
     * @access public
     * @param string content 事件内容
     * @param string phoneNum 手机号
     * @return void
     * @todo 针对@和#内容处理
     */
    public function createEvent() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('content', 'phoneNum'));
        //if (!FValidator::phone($this->params['phoneNum'])) throw new Exception('手机号格式非法', 100);

        #手机号是合法账户
        $userId = $this->userModel->getUserIdByUsername($this->params['phoneNum']);
        if (! $userId) throw new Exception('用户不存在', 101);
        #书写对应事件
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $this->params['content'], $result);
        foreach ($result[1] as $value) {
            $this->userModel->bindTag($userId, $value);
        }
        #生成事件
        $this->returnData = $this->userModel->createEvent($GLOBALS['userId'], $userId, $this->params['content']);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();
    }

    /**
     * 给联系人生成一则事件
     *
     * @access public
     * @param string evnet
     * @param int id
     * @return void
     */
    public function createEventForContact() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id', 'event'));
        $this->params['photo'] = F::request('photo', '');
        $this->params['systemEvent'] = F::request('systemEvent', 0);
        if ($this->params['photo']) $this->params['event'] .= ' #图片 ';
        $userId = $this->params['id'];

        $this->params['event'] = str_replace('＃', '#', $this->params['event']);
        $this->params['event'] = str_replace('#r', '#R', $this->params['event']);
        $this->params['event'] = str_replace('@', '@', $this->params['event']);
        #书写对应事件
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $this->params['event'], $result);

        #针对来访,需要追加相应房间的事件
        $hadVisterTag = FALSE;
        $projectTag = '';
        $roomTagArray = array();

        foreach ($result[1] as $value) {
            $this->userModel->bindTag($userId, $value);
            if ($value == '来访') $hadVisterTag = TRUE;
            if (preg_match('/^R\d{1,}/', $value)) $roomTagArray[] = $value;
            if ($this->isProjectTag($value)) $projectTag = $value;
        }

        if ($hadVisterTag && $projectTag) {
            #存在来访标签
            $this->groupModel = F::load_model('group', array());
            #获取当前操作联系人姓名
            $contactInfo = $this->userModel->get($this->params['id']);
            $contactName = $contactInfo[0]['nickname'];

            foreach ($roomTagArray as $roomTagArrayIndex) {
                $groupName = $projectTag.' '.str_replace('R', '', $roomTagArrayIndex);
                $groupId = $this->groupModel->getGroupIdByName($groupName);
                if ($groupId) {
                    #创建来访事件
                    $event = '#来访 '.$contactName.'来访看房';
                    $this->groupModel->setGroupId($groupId)->createEvent($GLOBALS['userId'], $event);
                }else {
                    #todo 房间不存在
                }
            }
        }

        $eventContent = $this->params['event'];
        $eventType = $this->params['systemEvent'] ? self::SYSTEM_EVENT : self::USER_EVENT ;
        $this->userModel->createEvent($GLOBALS['userId'], $userId, $eventContent, $this->params['photo'], $eventType );

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();
    }

    #检查一个标签是否是一个项目标签
    private function isProjectTag($value) {
        $allProjectTag = $this->tagModel->getALLProjectInfo();

        $allProjectTagArray = array();
        foreach ($allProjectTag as $allProjectTagIndex) $allProjectTagArray[] = $allProjectTagIndex['name'];
        unset($allProjectTag);

        return in_array($value, $allProjectTagArray) ? TRUE : FALSE;
    }

    /**
     * 获取被赞的事件列表
     *
     * @access public
     * @param int page 页数
     * @param int num 每页信息数目
     * @return array
     */
    public function getLikeEventUserList() {
        #参数检查
        $this->checkAccessToken();
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        $this->returnData = $this->userModel->getLikeEventUserList($GLOBALS['userId'], $this->params['page'], $this->params['num']);
        foreach ($this->returnData as &$value) {
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['eventContent'], $result);
            foreach ($result[0] as $notice) {
                $value['eventContent'] = str_replace($notice, '', $value['eventContent']);
            }
        }

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 通过部分手机号码, 检索用户列表
     *
     * @access public
     * @param string phoneNum 部分手机号码
     * @param int page 页数
     * @param int num 每页信息数目
     * @return array
     */
    public function seachUserList() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('phoneNum'));
        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 100);

        $this->returnData = $this->userModel->getUserList($this->params['phoneNum'], $this->params['page'], $this->params['num'], $GLOBALS['userId']);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取用户未读信息数目
     *
     * @access public
     * @return array
     */
    public function getUnreadInfo() {
        #参数检查
        $this->checkAccessToken();
        #获取未读信息数目
        $this->returnData = $this->userModel->getUserUnreadInfo($GLOBALS['userId']);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 用户喜欢一个事件
     *
     * @access public
     * @param int eventId
     * @return void
     */
    public function likeEvent() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('eventId'));
        $this->userModel->likeEvent($GLOBALS['userId'], $this->params['eventId']);

        $this->returnData = $this->userModel->get($GLOBALS['userId']);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData[0]['nickname']);
    }

    /**
     * 用户取消喜欢一个事件
     *
     * @access public
     * @param int eventId
     * @return void
     */
    public function unlikeEvent() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('eventId'));
        $this->userModel->unlikeEvent($GLOBALS['userId'], $this->params['eventId']);

        $this->returnData = $this->userModel->get($GLOBALS['userId']);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData[0]['nickname']);
    }

    /**
     * 更新账户信息
     *
     * @param string phoneNum 登陆手机号
     * @param string password 登陆密码md5
     * @param string photo 头像地址
     * @throws Exception 103 if 手机号格式不正确
     * @throws Exception 112 if 尝试更新一个非公司人员的账户信息
     * @todo 考虑一种特殊情况：输入的手机号是一个在数据库中已经存在的联系人的手机号码
     */
    public function updateProfile() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('phoneNum', 'password'));
        $this->params['photo'] = F::request('photo', '');
        if (!FValidator::phone($this->params['phoneNum']))
            throw new Exception('手机号格式不正确', 103);

        #账户信息检查
        $this->returnData = $this->userModel->getAccountType($GLOBALS['userId']);
        if ($this->returnData != self::ADMIN_ACCOUNT && $this->returnData != 3)
            throw new Exception('该接口只可以更新内容人员账户信息', 112);

        #更新账户信息
        $this->userModel->updatePassword($GLOBALS['userId'], $this->params['password'], TRUE);
        if ($this->params['photo']) {
            $this->returnData = $this->userModel->get($GLOBALS['userId']);
            $this->params['username'] = $this->returnData[0]['nickname'];
            $this->userModel->updateUserProfile($GLOBALS['userId'], $this->params['username'], $this->params['photo']);
        }

        F::rest()->show_result();
    }

    /**
     * 撤销事件
     *
     * @access public
     * @param int id
     * @return void
     */
    public function callbackEvent() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id'));

        $this->userModel->deleteEvent($GLOBALS['userId'], $this->params['id']);
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();
    }

    /**
     * 根据项目&房间信息搜索相应联系人信息
     *
     * @access public
     * @return array
     */
    public function seachUserListByRoomNum() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('roomNum', 'projectName'));
        $this->params['page'] = F::request('page', '1');
        $this->params['num'] = F::request('num', '10');

        $this->returnData = $this->userModel->seachUserListByRoomNum($this->params['roomNum'], $this->params['projectName'], $this->params['page'], $this->params['num'], $GLOBALS['userId']);

        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取公司内部人员信息
     *
     * @return array
     */
    public function getUserProfileList() {
        $this->checkAccessToken();
        $this->returnData = $this->userModel->getUserProfileListByAccountType(self::ADMIN_ACCOUNT);
        F::rest()->show_result($this->returnData);
    }
}

?>