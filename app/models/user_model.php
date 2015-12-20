<?php
/**
 * 用户model
 *
 * @author zhaoguang
 * @version 1.0
 */
final class user_model extends \base_model
{
    /**
     * 标签model
     * @var tag_model
     */
    private $tagModel;

    /**
     * @var email
     */
    private $emailObject;

    /**
     * SMTP邮件服务配置信息
     * @var array
     */
    private $emailConfig = array();

    /**
     * 群组model
     * @var group_model
     */
    private $groupModel;

    /**
     * 异步通知图片地址拼接前缀
     * @var string
     */
    const NOTIFY_PHOTO_HOST_FIX = 'http://crm.nashspace.com:8080/test/crm/img';

    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->tagModel = F::load_model('tag');
        $this->emailConfig = F::load_config('email.php');
    }

    /**
     * (non-PHPdoc)
     * @see base_model::__destruct()
     */
    public function __destruct() {
        parent::__destruct();
        unset($this->tagModel);
        unset($this->emailObject);
        unset($this->emailConfig);
        unset($this->groupModel);
    }

    /**
     * 根据用户名获取用户id, 若用户不存在则返回false
     *
     * @access public
     * @param string $username 用户名
     * @return int | boolean
     */
    public function getUserIdByUsername($username) {
        #读取缓存数据
        $userId = F::cache()->get('vstone_userModel_getUserIdByUsername_username:'.$username);
        if (!$userId) {
            #读取数据库信息
            $userId = F::db()->query('select user_id from account where phone_num like '."'%$username%'".' and status = 1 limit 1')->fetch_column('user_id');
            #写入缓存
            F::cache()->add('vstone_userModel_getUserIdByUsername_username:'.$username, $userId, FALSE, self::DEFAULT_ONE_DAY);
        }
        return $userId ? $userId : FALSE;
    }

    /**
     * 获取用户账户信息, 包括账户状态和账户密码
     *
     * @access public
     * @param int $userId
     * @return array
     */
    public function getAccountInfo($userId) {
        $accountInfo = F::cache()->get('vstone_userModel_getAccountInfo_userId:'.$userId);
        if ($accountInfo) {
            $accountInfo = unserialize($accountInfo);
        }else {
            #获取数据库数据
            $accountInfoTmp = F::db()->fetch_all('
                select nickname, sex, phone_num, password, email from account left join user_profile on account.user_id = user_profile.user_id
                where account.user_id = ? and status = ?', array($userId, 1));
            #整理数据
            foreach ($accountInfoTmp as &$value) {
                $key = $value['phone_num'];unset($value['phone_num']);
                $accountInfo[$key] = $value;
                $accountInfo[$key]['sex'] = $accountInfo[$key]['sex'] ? ($accountInfo[$key]['sex'] == 1 ? '女' : '男') : '保密';
            }
            unset($accountInfoTmp);
            #写入缓存
            F::cache()->add('vstone_userModel_getAccountInfo_userId:'.$userId, serialize($accountInfo), FALSE, self::DEFAULT_ONE_DAY);
        }
        return is_array($accountInfo) ? $accountInfo : array();
    }

    /**
     * 重新生成一个新的用户id
     *
     * @access private
     * @return int
     */
    private function _newUserId(){
        return F::db()->execute('insert into user_id_tmp set time = ?', array(self::DEFAULT_TIME_STYLE))->insert_id();
    }

    /**
     * 生成一个新的用户
     *
     * @access public
     * @param string $phoneNum 手机号
     * @param string $password 登陆密码md5
     * @param int $accountType
     * @param int $createUserId
     * @return int
     */
    public function create($phoneNum, $password, $accountType = 0, $createUserId = 0) {
        $userId = $this->_newUserId();
        F::db()->execute('insert into account set user_id = ?, phone_num = ?, password = ?, status = ?, type = ?, create_id = ?, regist_time = ?',
            array($userId, $phoneNum, md5(md5($password)), 1, $accountType, $createUserId, date('Y-m-d H:i:s')));
        return $userId;
    }

    /**
     * 生成用户授权信息
     *
     * @access public
     * @param int $userId 用户id
     * @return array
     */
    public function createToken($userId) {
        return $this->tokenModel->create($userId);
    }

    /**
     * 用户绑定标签
     *
     * @access public
     * @param int $userId
     * @param string $tagName
     * @return void
     */
    public function bindTag($userId, $tagName) {
        #获取tag id
        $this->tagModel = F::load_model('tag');
        $tagId = $this->tagModel->getTagIdByName($tagName);
        #绑定tag
        if ($tagId) $this->_bindTag($userId, $tagId);
    }

    /**
     * 用户绑定标签
     *
     * @access private
     * @param int $userId 用户id
     * @param string $tagId 标签id
     * @return void
     */
    private function _bindTag($userId, $tagId) {
        $hadBindTag = $this->userBindTagTime($userId, $tagId);
        if (!$hadBindTag) {
            F::db()->begin();
            F::db()->execute('replace into user_tag set user_id = ?, tag_id = ?, bind_time = ?', array($userId, $tagId, date(self::DEFAULT_TIME_STYLE)));
            F::db()->execute('update user_profile set update_time = ? where user_id = ?', array(date(self::DEFAULT_TIME_STYLE), $userId));
            F::db()->commit();
        }
    }

    /**
     * 获取用户绑定tag时间，不存在则返回false
     *
     * @access private
     * @param int $userId
     * @param int $tagId
     * @return int | boolean
     */
    private function userBindTagTime($userId, $tagId) {
        $time = F::db()->query('select bind_time as time from user_tag where user_id = ? and tag_id = ? limit 1', array($userId, $tagId))->fetch_column('time');
        return $time ? $time : FALSE;
    }

    /**
     * 根据标签获取用户集合列表
     *
     * @access public
     * @param array $tagArray 标签名称数组
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     */
    public function getList($tagArray, $page = 1, $num = 20) {
        #获取用户id集合
        $this->tagModel = F::load_model('tag');
        $userList = array();#返回用户列表
        $flag = 0;#强制合并标志
        foreach ($tagArray as $value) {
            #获取用户list
            $userList = $this->tagModel->getUserList($value);
            if ($flag) {
                #强制合并结果
                //$userListTmp = $this->tagModel->getUserList($value);
                $returnData = array_intersect($returnData, $userList);
            }else {
                $flag = 1;#标记强制合并标志
                $returnData = $userList;
            }
        }

        #根据用户的更新用户信息时间排序
        if (!empty($returnData)) {
            $returnData = F::db()->query('select user_id from user_profile where user_id in ('.implode(',', $returnData).') order by update_time desc')->fetch_column_all('user_id');
        }

        #重置userList数组
        $userList = array();
        $userListTmp = array();

        $startFlag = ($page-1)*$num;
        $endFlag = $page*$num;
        $currentNum = 0;
        $returnUserList = array();
        foreach ($returnData as &$userIndex) {
            if ($currentNum >= $startFlag && $currentNum < $endFlag) {
                $userId = $userIndex;
                $userIndex = array();
                $userIndex['userId'] = $userId;
                $userIndex['userProfile'] = $this->_get($userId);
                $userIndex['tag'] = $this->_getUserTagList($userId);
                $userIndex['acctionList'] = $this->getAcctionList($userId);
                $userIndex['lastEventInfo'] = $this->getUserOwnerEventList($userId, 1, 1);
                $returnUserList[] = $userIndex;
            }
            $currentNum++;
        }
        unset($userListTmp);
        unset($returnData);

        return is_array($returnUserList) ? $returnUserList : array();
    }

    /**
     * 获取拥挤基本信息
     *
     * @access private
     * @param int $userId 用户id
     * @return array
     */
    private function _get($userId) {
        $userInfo = F::cache()->get('vstone_user_model__get_userId:'.$userId);
        if ($userInfo) $userInfo = unserialize($userInfo);
        else {
            $userInfo = F::db()->fetch_all('select nickname, sex, phone_num, user_profile as userProfile, photo, email from user_profile left join account on user_profile.user_id = account.user_id
                where user_profile.user_id = ?', array($userId));
            foreach ($userInfo as &$userIndex) {
                $userIndex['sex'] = $userIndex['sex'] ? ($userIndex['sex'] == 1 ? '女' : '男') : '保密';
                $userIndex['userProfile'] = unserialize($userIndex['userProfile']);
            }
            F::cache()->add('vstone_user_model__get_userId:'.$userId, serialize($userInfo), FALSE, self::DEFAULT_ONE_DAY);
        }
        return $userInfo;
    }

    /**
     * 生成事件
     *
     * @access public
     * @param int $createUserId 事件产生者
     * @param int $eventOwnUserId 事件所有者
     * @param string $content 事件内容
     * @param string $eventPhoto
     * @param int $eventType 事件类型: 1-系统事件 2-用户创建事件
     * @return int
     */
    public function createEvent($createUserId, $eventOwnUserId, $content, $eventPhoto = '', $eventType = self::USER_EVENT) {
        if (!$content) return FALSE;

        #验证@的人属性是联系人
        $content = str_replace('＠', '@', $content);
        preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $content, $contactList);
        foreach ($contactList[1] as $contactListIndex) {
            $toUserId = $this->getUserIdByNicknameOnlyForUser($contactListIndex);
            $userType = $this->getAccountType($toUserId);
            if ($userType == 2) {
                #防止联系人&内部用户重名
                $content = str_replace('@'.$contactListIndex, '', $content);
            }
        }

        if ($content == ' ') return FALSE;
        #如果事件内容备判定为系统标志信息（例如参加复试）则需要修改事件类型

        $eventId = F::db()->execute('insert into event set user_id = ?, content = ?, status = 1, create_time = ?, create_user_id = ?, photo = ?, type = ? ',
            array($eventOwnUserId, $content, date(self::DEFAULT_TIME_STYLE), $createUserId, $eventPhoto, $eventType))->insert_id();
        preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $content, $result);
        foreach ($result[1] as $noticeIndex) {
            $toUserId = $this->getUserIdByNicknameOnlyForUser($noticeIndex);
            $this->createNotice($createUserId, $toUserId, $content, $eventId);
        }

        #针对存在维修信息的异步通知
        $this->_sync2Capital($eventId, $eventOwnUserId, $eventPhoto, $content, $eventType);

        return $eventId;
    }

    /**
     * 异步通知纳什资本服务器
     * @param  int    $eventId        事件id
     * @param  int    $eventOwnUserId 事件所有者id
     * @param  string $photo          图片地址
     * @param  string $content        事件内容
     * @param  int    $eventType      事件类型
     * @return boolean
     */
    private function _sync2Capital($eventId, $eventOwnUserId, $photo, $content, $eventType) {
        #检测是否需要存在维修
        $flag = preg_match('/#维修/', $content);
        if (!$flag) return FALSE;

        #获取房间信息
        $syncModel = F::load_model('sync');
        $roomInfo = $syncModel->_getRoomInfo($eventOwnUserId, $eventType);
        #书写异步
        $queueModel = new queue_model(array());
        return $queueModel->setSendRequestParam(array(
            'room_id' => $roomInfo['roomName'],
            'room_project' => $roomInfo['projectName'],
            'event_id' => $eventId,
            'rootUrl' => self::NOTIFY_PHOTO_HOST_FIX,
            'photo' => $photo,
            'content' => $content,
            'create_time' => date(TIMESTYLE),
        ))->createSyncNotify();

    }

    /**
     * 获取一个账户的属性
     *
     * @access public
     * @param int $userId
     * @return int
     */
    public function getAccountType($userId) {
        $type = F::db()->query('select type from account where user_id = ? and status = 1', array($userId))->fetch_column('type');
        return $type ? $type : 0;
    }

    /**
     * 通过用户昵称，获取用户id。该接口为了解决联系人与内部人员姓名重复
     * @param  string $nickname 用户昵称
     * @return int|boolean
     */
    private function getUserIdByNicknameOnlyForUser($nickname) {
        $userId = F::db()->query('select account.user_id as user_id from account left join user_profile on account.user_id = user_profile.user_id
            where nickname = ? and account.type = 1 limit 1', array(trim($nickname)))->fetch_column('user_id');
        return $userId ? $userId : FALSE;
    }

    /**
     * 根据用户昵称获取用户id
     *
     * @access public
     * @param string $nickname
     * @return int
     */
    public function getUserIdByNickname($nickname) {
        $userId = F::db()->query('select user_id from user_profile where nickname = ? limit 1', array(trim($nickname)))->fetch_column('user_id');
        return $userId ? $userId : FALSE;
    }

    /**
     * 创建通知
     *
     * @access private
     * @param int $fromUserId
     * @param int $toUserId
     * @param string $content
     * @param int $eventId
     * @return void
     */
    private function createNotice($fromUserId, $toUserId, $content, $eventId) {
        if (!$toUserId ) return FALSE;
        F::db()->execute('insert into notice set from_user_id = ?, to_user_id = ?, content = ?, status = 1, param = ?',
            array($fromUserId, $toUserId, $content, $eventId))->insert_id();
        $this->_sendEmail($toUserId, $fromUserId, $content);
    }

    /**
     * 通知后，发送相关邮件
     * 发送成功返回true,失败返回false
     *
     * @access private
     * @param int $recivedUserId recive email user id
     * @param int $fromUserId send email user id
     * @param string $eventContent event content string
     * @return boolean
     * @todo 使用队列服务
     */
    private function _sendEmail($recivedUserId, $fromUserId, $eventContent) {
        #获取接受者邮箱信息
        $reciveEmail = $this->getEmailAddressByUserId($recivedUserId);
        $reciveUserInfo = $this->get($recivedUserId);
        if (!$reciveEmail || !FValidator::email($reciveEmail)) return FALSE;

//         #测试环境下代码，上线前需要移除
//         if ($reciveEmail != 'zhaoguang@nash.work') $reciveEmail = 'zhaoguang@nash.work';

        #发送邮件
        $this->emailObject = F::loadClass('email', $this->emailConfig);
        $sendResult = $this->emailObject->send($reciveEmail, $this->_getEmailSubject(), $this->_getEmailContent($fromUserId, $eventContent, $reciveUserInfo[0]['nickname']));
        return $sendResult['isSuccess'] ? TRUE : FALSE;
    }

    /**
     * 发送邮件方法
     * @param  string $email     接收邮件地址
     * @param  string $subject   邮件标题
     * @param  string $emailBody 邮件主题信息
     * @return boolean
     */
    private function _baseSendEmail($email, $subject, $emailBody) {
        $this->emailObject = F::loadClass('email', $this->emailConfig);
        $sendResult = $this->emailObject->send($email, $subject, $emailBody);
        return $sendResult['isSuccess'] ? TRUE : FALSE;
    }

    /**
     * 根据用户id，获取用户邮箱信息
     * 邮件存在则返回邮箱地址, 查找失败则返回false
     *
     * @access private
     * @param int $userId user id
     * @return string | boolean
     */
    private function getEmailAddressByUserId($userId) {
        $userInfo = $this->get($userId);
        return $userInfo[0]['email'] ? $userInfo[0]['email'] : FALSE;
    }

    /**
     * 获取邮件发送主题
     *
     * @access private
     * @return string
     */
    private function _getEmailSubject() {
        return '您有新的通知';
    }

    /**
     * 获取邮件发送内容
     *
     * @access private
     * @param int $fromUserId send email user id
     * @param string $eventContent event content
     * @return string
     */
    private function _getEmailContent($fromUserId, $eventContent, $reciveUserName = '未知') {
        $userProfile = $this->get($fromUserId);
        return '<div>
                <div>你好，'.$reciveUserName.'：</div>
                <div><br>
                </div>
                <div>'.$userProfile[0]['nickname'].' 于 '.date(TIMESTYLE).' 发布了一条跟进信息并@了你：</div><div><br></div>
                <hr>
                <div>信息内容:</div>
                <div>
                    <span style="line-height: 1.5;">'.$eventContent.'</span>
                </div>
                <hr>
                <div><br>
                </div>
                <div><br>
                </div>
                <div>请进入你的内部系统APP查看详情。</div>
                <div><br>
                </div>
                <div>**********</div>
                <div>本邮件为纳米系统自动产生，请勿回复，谢谢！</div>
            </div>';
    }

    /**
     * 获取用户创建事件列表
     *
     * @access public
     * @param int $currentUserId 当前用户id
     * @param int $userId 用户id
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     */
    public function getUserEventList($userId, $page, $num, $currentUserId = 0) {
        $pageInfo = F::cache()->get('vstone_user_model_getUserEventList_userId:'.$userId.'_page:'.$page.'_num:'.$num);
        $currentUserInfo['profile'] = $this->_get($userId);
        $currentUserInfo['tagList'] = $this->_getUserTagList($userId);

        if ($pageInfo) $pageInfo = unserialize($pageInfo);
        else {
            #获取事件列表
            $pageInfoTmp = F::db()->fetch_all(
                'select id as eventId, content, create_time as createTime, create_user_id, id, user_id, photo, type from event where create_user_id = ? and status = 1 order by id desc limit ?, ?',
                array($userId, ($page-1)*$num, $num));
            foreach ($pageInfoTmp as &$value) {
                $value['createTime'] = $this->_translateTime(strtotime($value['createTime']));
                $userProfile = $this->_get($value['create_user_id']);
                $value['createUserName'] = $userProfile[0]['nickname'];
                $value['createUserPhoto'] = $userProfile[0]['photo'];
                $value['eventType'] = $value['type'] == 3 ? 'group' : 'contact';

                if ($value['type'] == 3) {
                    $groupModel = F::load_model('group', array('groupId' => $value['user_id']));
                    $groupInfo = $groupModel->get();
                    $value['eventCreateUserNickname'] = $groupInfo['name'];
                    foreach ($groupInfo['tagList'] as $tagListIndex) {
                        $value['eventOwnInfo'][] = array(
                            'tagId' => $tagListIndex['id'],
                            'tagName' => $tagListIndex['name'],
                            'coler' => $tagListIndex['tagClass']
                        );
                    }
                    #补充relation信息
                    $value['relation'] = $groupInfo['relation'];
                }else {
                    $eventCreateUserInfo = $this->_get($value['user_id']);
                    $value['eventCreateUserNickname'] = $eventCreateUserInfo[0]['nickname'];
                    $value['eventOwnInfo'] = $this->_getUserTagList($value['user_id']);
                    $value['relation'] = array();

                    #检查联系人性质,若时公司内部人员则直接跳出
                    $userAccountType = $this->getAccountType($value['user_id']);
                    if ($userAccountType == 1 || $userAccountType == 3) {
                        unset($value);
                        continue;
                    }
                }

                $noticeList = $this->_getNoticeListByEventId($value['id']);
                foreach ($noticeList as $noticeListIndex) {
                    $value['noticeUser'][] = array('name' => $noticeListIndex, 'userId' => $this->getUserIdByNickname(str_replace('@', '', $noticeListIndex)));
                }

                $praiseList = $this->_getPraiseList($value['id']);
                $value['praise'] = array();
                foreach ($praiseList as $praiseListIndex) {
                    $praiseUserInfo = $this->_get($praiseListIndex['userId']);
                    $value['praise'][] = $praiseUserInfo[0]['nickname'];
                }
                $value['hadPraise'] = $this->_hadPraise($currentUserId, $value['id']);

                #是否可以点击打开
                $value['enable_open'] = $this->isEnableOpen($currentUserId, $value['user_id'], $value['type']);

                unset($value['id']);
                unset($value['create_user_id']);

                $pageInfo[] = $value;
            }
            #写入缓存
            F::cache()->add('vstone_user_model_getUserEventList_userId:'.$userId.'_page:'.$page.'_num:'.$num, serialize($pageInfo), FALSE, self::DEFAULT_ONE_DAY);
        }

        return $pageInfo;
    }

    /**
     * 获取用户所有的事件列表
     *
     * @access public
     * @param int $currentUserId 当前用户id
     * @param int $userId 用户id
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     */
    public function getUserOwnerEventList($userId, $page, $num, $currentUserId = 0) {
        $pageInfo = F::cache()->get('vstone_user_model_getUserEventList_userId:'.$userId.'_page:'.$page.'_num:'.$num);
        $currentUserInfo['profile'] = $this->_get($userId);
        $currentUserInfo['tagList'] = $this->_getUserTagList($userId);

        if ($pageInfo) $pageInfo = unserialize($pageInfo);
        else {
            #获取事件列表
            $pageInfo = F::db()->fetch_all(
            'select id as eventId, content, create_time as createTime, create_user_id, id, user_id, photo, type from event where user_id = ? and status = 1 and type != 3 order by id desc limit ?, ?',
            array($userId, ($page-1)*$num, $num));
            foreach ($pageInfo as &$value) {
                $value['createTime'] = $this->_translateTime(strtotime($value['createTime']));
                $userProfile = $this->_get($value['create_user_id']);
                $value['createUserName'] = $userProfile[0]['nickname'];
                $value['createUserId'] = $value['create_user_id'];

                $noticeList = $this->_getNoticeListByEventId($value['id']);
                //$value['noticeUser'] = implode(',', $noticeList);
                foreach ($noticeList as $noticeListIndex) {
                    $value['noticeUser'][] = array('name' => $noticeListIndex, 'userId' => $this->getUserIdByNickname(str_replace('@', '', $noticeListIndex)));
                }


                $value['eventOwnInfo'] = $this->_getUserTagList($value['user_id']);
                $praiseList = $this->_getPraiseList($value['id']);
                $value['praise'] = array();
                foreach ($praiseList as $praiseListIndex) {
                    $praiseUserInfo = $this->_get($praiseListIndex['userId']);
                    $value['praise'][] = $praiseUserInfo[0]['nickname'];
                }
                $value['hadPraise'] = $this->_hadPraise($currentUserId, $value['id']);

                unset($value['id']);
                unset($value['create_user_id']);
            }
            #写入缓存
            F::cache()->add('vstone_user_model_getUserEventList_userId:'.$userId.'_page:'.$page.'_num:'.$num, serialize($pageInfo), FALSE, self::DEFAULT_ONE_DAY);
        }

        return $pageInfo;
    }



    /**
     * 根据事件id获取相关通知内容
     *
     * @access private
     * @param int $eventId 事件id
     * @return array
     */
    private function _getNoticeListByEventId($eventId) {
        $noticeList = F::db()->fetch_all('SELECT nickname FROM `notice` left join user_profile on to_user_id =  user_id where param = ?', array($eventId));
        foreach ($noticeList as &$value) $value = '@'.$value['nickname'];
        return is_array($noticeList) ? $noticeList : array();
    }

    /**
     * 根据部分手机号，获取相关用户列表
     * 根据用户昵称获取用户列表
     * 若输入内容是标签，则搜索标签内信息列表
     *
     * @access public
     * @param string $phoneNum 手机号
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     */
    public function getUserList($phoneNum, $page, $num, $userId) {
        #获取待筛选用户id
        if (!$phoneNum) $list = $this->getUserAcctionList($userId, $page, $num);
        else {
            $list = F::db()->fetch_all('select user_profile.user_id as userId, phone_num as phoneNum, nickname, sex
                from user_profile left join account on user_profile.user_id = account.user_id where (phone_num like '."'%$phoneNum%'".'
                or nickname like '."'%$phoneNum%'".') and status = 1 and type = 2 order by user_profile.update_time desc limit ?, ?', array(($page-1)*$num, $num));
        }

        $returnData = array();
        foreach ($list as $value) {
            $lastEvent = $this->getUserOwnerEventList($value['userId'], 1, 1);
            $returnData[] = array(
                'userId' => $value['userId'],
                'nickname' => $value['nickname'],
                'phoneNum' => $value['phoneNum'],
                'eventOwnInfo' => $this->_getUserTagList($value['userId']),
                'acctionList' => $this->_getAcctionList($value['userId']),
                'lastEventInfo' => array(
                    'content' => $lastEvent[0]['content'] ? $lastEvent[0]['content'] : '&nbsp;' ,
                    'noticeUser' => $lastEvent[0]['createUserName'] ? $lastEvent[0]['createUserName'] : '&nbsp;',
                    'createTime' => $lastEvent[0]['createTime'] ? $lastEvent[0]['createTime'] : '&nbsp;',
                    'photo' => $lastEvent[0]['photo'],
                ),
            );
        }
        return is_array($returnData) ? $returnData : array();
    }

    /**
     * 根据房间号和项目名称搜索具有相关标签的用户列表
     * 若$roomNum&&$projectName都存在，则展示的用户列表同时具有房间号和项目号标签
     * 若指存在项目号，则展示的用户列表具有项目号标签且存在房间号标签
     * 若不存在roomNum和$projectName 则返还空数组
     *
     * @access public
     * @param int $roomNum
     * @param int $projectName
     * @param int $page
     * @param int $num
     * @param int $userId
     * @return array
     */
    public function seachUserListByRoomNum($roomNum, $projectName, $page, $num, $userId) {
        if (!$roomNum && !$projectName) return array();

        $tagHashSeachArray = array();
        if ($roomNum) $roomNumList = $this->tagModel->seachRoomNameByKeyword($roomNum);
        else $roomNumList = array();

        if (empty($roomNumList)) $returnData = $this->getList(array($projectName), $page, $num);
        else {
            foreach ($roomNumList as $roomNumListIndex) {
                if (!preg_match('/^R\w{1,}/', $roomNumListIndex)) continue;
                $roomNumListId[] = $this->tagModel->getTagIdByName($roomNumListIndex);
            }

            #获取所有具有$projectName的用户列表
            $projectNameID = $this->tagModel->getTagIdByName($projectName);
            $userListByPro = F::db()->query('select user_id from user_tag where tag_id = ?', array($projectNameID))->fetch_column_all('user_id');
            if (!empty($roomNumListId)) $userListByRoom = F::db()->query('select user_id from user_tag where tag_id in ('.implode(',', $roomNumListId).')')->fetch_column_all('user_id');

            $returnData = array_intersect($userListByPro, $userListByRoom);

            #根据用户的更新用户信息时间排序
            if (!empty($returnData)) {
                $returnData = F::db()->query('select user_id from user_profile where user_id in ('.implode(',', $returnData).') order by update_time desc')->fetch_column_all('user_id');
            }

            #重置userList数组
            $userList = array();
            $userListTmp = array();

            $startFlag = ($page-1)*$num;
            $endFlag = $page*$num;
            $currentNum = 0;
            $returnUserList = array();
            foreach ($returnData as &$userIndex) {
                if ($currentNum >= $startFlag && $currentNum < $endFlag) {
                    $userId = $userIndex;
                    $userIndex = array();
                    $userIndex['userId'] = $userId;
                    $userIndex['userProfile'] = $this->_get($userId);
                    $userIndex['tag'] = $this->_getUserTagList($userId);
                    $userIndex['acctionList'] = $this->getAcctionList($userId);
                    $userIndex['lastEventInfo'] = $this->getUserOwnerEventList($userId, 1, 1);
                    $returnUserList[] = $userIndex;
                }
                $currentNum++;
            }
            unset($userListTmp);
            unset($returnData);

            $returnData = $returnUserList;
        }

        return $returnData;
    }

    /**
     * 获取用户操作过的用户列表
     *
     * @access private
     * @param int $userId
     * @return array
     */
    private function getUserAcctionList($userId, $page, $num) {
        $list = F::db()->fetch_all('select user_profile.user_id as userId, phone_num as phoneNum, nickname, sex
            from event left join user_profile on event.user_id = user_profile.user_id left join account on user_profile.user_id = account.user_id
            where create_user_id = ? and event.status = 1 and account.type = 2 and event.type != 3 group by user_profile.user_id order by user_profile.update_time desc limit ?,?', array($userId, ($page-1)*$num, $num));
        return is_array($list) ? $list : array();
    }

    /**
     * 获取操作过某人的所有用户列表
     *
     * @access private
     * @param int $userId 用户id
     * @return array
     */
    private function _getAcctionList($userId) {
        $acctionUserListTmp = F::db()->fetch_all(
            'SELECT nickname FROM `event` left join user_profile on create_user_id = user_profile.user_id where event.user_id = ? and event.type != 3 group by create_user_id',
            array($userId));
        foreach ($acctionUserListTmp as $value) $acctionUserList[] = $value['nickname'];
        return is_array($acctionUserList) ? $acctionUserList : array();
    }

    /**
     * 获取操作过某人的所有用户列表
     *
     * @access public
     * @param int $userId 用户id
     * @return array
     */
    public function getAcctionList($userId) {
        return $this->_getAcctionList($userId);
    }

    /**
     * 根据部分手机号内容，搜索用户id
     *
     * @access private
     * @param string $username 部分手机号
     * @param string $seachTagName 特殊标签限制
     * @return array
     */
    private function _seachUserList($username, $seachTagName = '') {
        $userListTmp = F::db()->fetch_all('select distinct user_id as userId from account where phone_num like '."'%$username%'".' and status = 1');
        foreach ($userListTmp as $value) $userList[] = $value['userId'];
        unset($userListTmp);

        if ($seachTagName) {
            $this->tagModel = F::load_model('tag');
            $tagUserList = $this->tagModel->getUserList($seachTagName);
            if (empty($tagUserList)) return array();
            $userList = array_intersect($userList, $tagUserList);
        }

        return is_array($userList) ? $userList : array();
    }

    /**
     * 获取用户拥有标签列表
     *
     * @access private
     * @param int $userId 用户id
     * @return array
     */
    public function _getUserTagList($userId) {
        $tagList = F::db()->fetch_all('SELECT tag_id as tagId, name as tagName, color as coler, type FROM `user_tag` left join tag on tag_id = id where user_id = ?', array($userId));
        foreach ($tagList as &$value) {
            if (!$value['coler']) $value['coler'] = 'white-text grey';
        }
        return is_array($tagList) ? $tagList : array();
    }

    /**
     * 获取用户拥有标签列表
     *
     * @access public
     * @param int $userId 用户id
     * @return array
     */
    public function getUserTagList($userId) {
        return $this->_getUserTagList($userId);
    }

    /**
     * 获取事件赞信息列表
     *
     * @access private
     * @param int $eventId 事件id
     * @return array
     */
    private function _getPraiseList($eventId) {
        $list = F::db()->fetch_all('SELECT user_id as userId FROM `user_event` where event_id = ? and type = 1', array($eventId));
        return is_array($list) ? $list : array();
    }

    /**
     * 判断用户是否赞了事件
     *
     * @access private
     * @param int $userId 用户id
     * @param int $eventId 事件id
     * @return boolean
     */
    private function _hadPraise($userId, $eventId) {
        $praiseInfo = F::db()->query('SELECT time FROM `user_event` where user_id = ? and event_id = ? and type = 1 limit 1', array($userId, $eventId))->fetch_column('time');
        return $praiseInfo ? TRUE : FALSE;
    }

    /**
     * 获取所有事件列表
     *
     * @access public
     * @param int $userId 用户id
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     * @todo bug检查 多个notice问题
     */
    public function getEventList($userId, $page, $num) {
        $eventListTmp = F::db()->fetch_all(
            'select event.id as eventId, user_id as userId, event.content as eventContent, create_time as createTime, create_user_id as createUserId, photo, type
            from event where event.status = 1 order by event.id desc limit ?,?', array(($page-1)*$num, $num));

        $eventList = array();
        foreach ($eventListTmp as $value) {
            if ($value['type'] == 3) {
                $groupModel = F::load_model('group', array('groupId' => $value['userId']));
                $groupInfo = $groupModel->setGroupId($value['userId'])->get();
                $ownInfo[0]['nickname'] = $groupInfo['name'];

                $ownTagInfo = array();
                foreach ($groupInfo['tagList'] as $tagListIndex) {
                    $ownTagInfo[] = array(
                        'tagId' => $tagListIndex['id'],
                        'tagName' => $tagListIndex['name'],
                        'coler' => $tagListIndex['tagClass']
                    );
                }

                #补充relation信息
                $ownRelation = $groupInfo['relation'];

            }else {
                #检查联系人性质,若时公司内部人员则直接跳出
                $userAccountType = $this->getAccountType($value['userId']);
                if ($userAccountType == 1 || $userAccountType == 3) continue;

                $ownInfo = $this->_get($value['userId']);
                $ownTagInfo = $this->_getUserTagList($value['userId']);
                $ownRelation = array();
            }

            $createUserInfo = $this->_get($value['createUserId']);
            $praiseList = $this->_getPraiseList($value['eventId']);
            $praiseListArray = array();
            foreach ($praiseList as $praiseListIndex) {
                $praiseUserInfo = $this->_get($praiseListIndex['userId']);
                $praiseListArray[] = $praiseUserInfo[0]['nickname'];
            }
            $result = array();
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['eventContent'], $result);
            $noticeUserInfo = array();
            foreach ($result[0] as $noticeIndex) {
                //$noticeUserInfo[] = $noticeIndex;
                $noticeUserInfo[] = array('name' => $noticeIndex, 'userId' => $this->getUserIdByNickname(str_replace('@', '', $noticeIndex)));
            }

            $eventList[] = array(
                'eventId' => $value['eventId'],
                'eventCreateUserNickname' => $ownInfo[0]['nickname'],
                'photo' => $value['photo'],
                'createUserId' => $value['userId'],
                'createUserPhoto' => $createUserInfo[0]['photo'] ? $createUserInfo[0]['photo'] : '',
                'eventCreateUserId' => $value['createUserId'],
                'createUserName' => $createUserInfo[0]['nickname'],
                'eventContent' => $value['eventContent'],
                'eventContantUser' => is_array($noticeUserInfo) ? $noticeUserInfo : array(),
                'eventOwnInfo' => $ownTagInfo,
                'time' => $this->_translateTime(strtotime($value['createTime'])),
                'praise' => $praiseListArray,
                'hadPraised' => $this->_hadPraise($userId, $value['eventId']),
                'createUserPhoneNum' => $createUserInfo[0]['phone_num'],
                'eventType' => $value['type'] == 3 ? 'group' : 'contact',
                'relation' => $ownRelation,
                'enable_open' => $this->isEnableOpen($userId, $value['userId'], $value['type']),
            );
        }

        return $eventList;
    }

    #查看权限检查
    #检查原则：联系人情况下，联系人拥有项目标签与当前用户也拥有改项目标签
    #        群组情况下： 群组所属项目和当前用户一致
    public function isEnableOpen($userId, $paramId, $type) {
        #读取拥有全部权限的用户列表
        $levelConfig = F::load_config('levelConfig.php');
        if (in_array($userId, $levelConfig)) return TRUE;
        unset($levelConfig);

        #获取当前用户所属项目标签
        $userTag = $this->getUserTagList($userId);
        foreach ($userTag as $userTagIndex) if ($userTagIndex['type'] == 2) $userTagArray[] = $userTagIndex['tagId'];
        unset($userTag);
        $userTagArray = is_array($userTagArray) ? $userTagArray : array();

        #获取param属于项目标签
        if ($type == 3) {
            #群组类型
            $this->groupModel = F::load_model('group', array());
            $groupInfo = $this->groupModel->setGroupId($paramId)->get();
            #group create user can open
            if ($groupInfo['createUserId'] == $userId) return TRUE;
            #拥有纳什空间的群组，全体可见
            if ($groupInfo['groupProject']['name'] == '纳什空间') return TRUE;
            #群组所属项目下人员拥有查看权限
            if (in_array($groupInfo['groupProject']['id'], $userTagArray)) return TRUE;
            #检查能否通过@获取临时权限
            if ($this->getOpenLevelByNotice($userId, $paramId, 'group')) return TRUE;

            #检查群组拥有的项目标签，其项目类型下的成员拥有查看权限
            foreach ($groupInfo['tagList'] as $groupInfoTagListIndex) {
                if ($groupInfoTagListIndex['type'] == 2) $groupInfoTagListArray[] = $groupInfoTagListIndex['id'];
                if ($groupInfoTagListIndex['name'] == '纳什空间') return TRUE;
            }
            $groupInfoTagListArray = is_array($groupInfoTagListArray) ? $groupInfoTagListArray : array() ;
            #计算交集
            $result = array_intersect($userTagArray, $groupInfoTagListArray);
            unset($groupInfoTagListArray);
            return empty($result) ? FALSE : TRUE;

        }else {
            #contact create user can open
            $createContactUserId = $this->getAccountCreateUserId($paramId);
            if ($createContactUserId == $userId) return TRUE;
            #检查能否通过@获取临时权限
            if ($this->getOpenLevelByNotice($userId, $paramId, 'contact')) return TRUE;

            #联系人类型
            $contactTagList = $this->getUserTagList($paramId);
            foreach ($contactTagList as $contactTagListIndex) if ($contactTagListIndex['type'] == 2) $contactTagListArray[] = $contactTagListIndex['tagId'];
            $contactTagListArray = is_array($contactTagListArray) ? $contactTagListArray : array();
            #计算交集
            $result = array_intersect($userTagArray, $contactTagListArray);

            #若联系人不存在有效的项目标签，则使用该联系人的创建者的项目标签代替之
            if (empty($contactTagListArray)) {
                $createContactUserTag = $this->getUserTagList($createContactUserId);
                foreach ($createContactUserTag as $createContactUserTagIndex) {
                    if ($createContactUserTagIndex['type'] == 2) $createContactUserTagArray[] = $createContactUserTagIndex['tagId'];
                    if ($createContactUserTagIndex['tagName'] == '纳什空间') return TRUE;
                }
                $result = is_array($createContactUserTagArray) ? $createContactUserTagArray : array();

            }

            unset($contactTagListArray);
            return empty($result) ? FALSE : TRUE;
        }
    }

    #通过@获取的临时权限
    private function getOpenLevelByNotice($userId, $paramId, $type) {
        $hadNotice = F::db()->query('select notice.id as id from `notice` left join `event` on notice.param = event.id where to_user_id = ? and event.user_id = ? and type = ?
            and event.status = 1 and notice.status = 1 limit 1', array($userId, $paramId, $type == 'group' ? 3 : 2 ))->fetch_column('id');
        return $hadNotice ? TRUE : FALSE;
    }

    #获取账户的创建者id
    private function getAccountCreateUserId($contactId) {
        $createUserId = F::db()->query('select create_id as createUserId from account where user_id = ? and status = 1 limit 1', array($contactId))->fetch_column('createUserId');
        return $createUserId ? $createUserId : 0;
    }

    /**
     * 获取某几个项目下的事件列表
     *
     * @access public
     * @param array $tagID 事件数组
     * @return array
     */
    public function getEventListByGroupId(array $tagId, $page, $num) {
        if (empty($tagId)) return array();
        $userId = $this->tagModel->getUserListByTadId($tagId);
        if (empty($userId)) return array();

        $eventListTmp = F::db()->fetch_all('SELECT event.id as eventId, user_id as userId, event.content as eventContent, create_time as createTime,
            create_user_id as createUserId, photo, type FROM `event` where create_user_id in ('.implode(',', $userId).') and status = 1 ORDER BY event.id desc limit ?, ?',
            array(($page-1)*$num, $num));

        $eventList = array();
        foreach ($eventListTmp as $value) {
            if ($value['type'] == 3) {
                $groupModel = F::load_model('group', array('groupId' => $value['userId']));
                $groupInfo = $groupModel->get();
                $ownInfo[0]['nickname'] = $groupInfo['name'];

                $ownTagInfo = array();
                foreach ($groupInfo['tagList'] as $tagListIndex) {
                    $ownTagInfo[] = array(
                        'tagId' => $tagListIndex['id'],
                        'tagName' => $tagListIndex['name'],
                        'coler' => $tagListIndex['tagClass']
                    );
                }
                #补充relation信息
                $ownRelation = $groupInfo['relation'];

            }else {
                #检查联系人性质,若时公司内部人员则直接跳出
                $userAccountType = $this->getAccountType($value['userId']);
                if ($userAccountType == 1 || $userAccountType == 3) continue;


                $ownInfo = $this->_get($value['userId']);
                $ownTagInfo = $this->_getUserTagList($value['userId']);
                $ownRelation = array();
            }

            $createUserInfo = $this->_get($value['createUserId']);
            $praiseList = $this->_getPraiseList($value['eventId']);
            $praiseListArray = array();
            foreach ($praiseList as $praiseListIndex) {
                $praiseUserInfo = $this->_get($praiseListIndex['userId']);
                $praiseListArray[] = $praiseUserInfo[0]['nickname'];
            }
            $result = array();
            preg_match_all('/@([^@^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $value['eventContent'], $result);
            $noticeUserInfo = array();
            foreach ($result[0] as $noticeIndex) {
                //$noticeUserInfo[] = $noticeIndex;
                $noticeUserInfo[] = array('name' => $noticeIndex, 'userId' => $this->getUserIdByNickname(str_replace('@', '', $noticeIndex)));
            }

            $eventList[] = array(
                'eventId' => $value['eventId'],
                'eventCreateUserNickname' => $ownInfo[0]['nickname'],
                'photo' => $value['photo'],
                'createUserId' => $value['userId'],
                'eventCreateUserId' => $value['createUserId'],
                'createUserName' => $createUserInfo[0]['nickname'],
                'createUserPhoto' => $createUserInfo[0]['photo'],
                'eventContent' => $value['eventContent'],
                //'eventContantUser' => $noticeUserInfo[0]['nickname'] ? '@'.$noticeUserInfo[0]['nickname'] : '',
                'eventContantUser' => is_array($noticeUserInfo) ? $noticeUserInfo : array(),
                'eventOwnInfo' => $ownTagInfo,
                'time' => $this->_translateTime(strtotime($value['createTime'])),
                'praise' => $praiseListArray,
                'hadPraised' => $this->_hadPraise($userId, $value['eventId']),
                'createUserPhoneNum' => $createUserInfo[0]['phone_num'],
                'eventType' => $value['type'] == 3 ? 'group' : 'contact',
                'relation' => $ownRelation,
                'enable_open' => TRUE,
            );
        }

        return $eventList;
    }

    /**
     * 翻译事件信息为前端显示格式
     *
     * @access private
     * @param int $time 待显示事件
     * @return string
     */
    private function _translateTime($time) {
        return date('Y年m月d日 H时i分s秒', $time);
    }

    /**
     * 获取被通知信息列表
     *
     * @access public
     * @param int $userId 用户id
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     */
    public function getNoticeList($userId, $page, $num) {
        $listTmp = F::db()->fetch_all(
            'SELECT event.id as eventId, from_user_id as createUserId, event.content as noticeContent, create_time as noticeCreateTime, user_id as eventOwnId, photo, type
            FROM `notice` left join event on param = event.id where to_user_id = ? and notice.status = 1 and event.status = 1 order by notice.id desc limit ?, ?',
            array($userId, ($page-1)*$num, $num));

        $list = array();
        $userInfo = $this->_get($userId);
        foreach ($listTmp as $value) {
            $createUserInfo = $this->_get($value['createUserId']);
            if ($value['type'] == 3) {
                $groupModel = F::load_model('group', array('groupId' => $value['eventOwnId']));
                $groupInfo = $groupModel->get();
                $ownUserInfo[0]['nickname'] = $groupInfo['name'];

                $ownTagInfo = array();
                foreach ($groupInfo['tagList'] as $tagListIndex) {
                    $ownTagInfo[] = array(
                        'tagId' => $tagListIndex['id'],
                        'tagName' => $tagListIndex['name'],
                        'coler' => $tagListIndex['tagClass']
                    );
                }

                $relation = $groupInfo['relation'];

            }else {
                $ownUserInfo = $this->_get($value['eventOwnId']);
                $ownTagInfo = $this->_getUserTagList($value['eventOwnId']);
                $relation = array();
            }

            $priseList = array();
            $priseListTmp = $this->_getPraiseList($value['eventId']);
            foreach ($priseListTmp as $priseListTmpIndex) {
                $priseUserInfo = $this->_get($priseListTmpIndex['userId']);
                $priseList[] = $priseUserInfo[0]['nickname'];
            }

            $list[] = array(
                'eventId' => $value['eventId'],
                'createUserId' => $value['eventOwnId'],
                'eventCreateUserNickname' => $ownUserInfo[0]['nickname'],
                'photo' => $value['photo'],
                'createUser' => $createUserInfo[0]['nickname'],
                'createUserPhoto' => $createUserInfo[0]['photo'],
                'noticeContent' => $value['noticeContent'],
                'noticeUser' => $userInfo[0]['nickname'] ? array(array('name' => '@'.$userInfo[0]['nickname'], 'userId' => $this->getUserIdByNickname($userInfo[0]['nickname']))) : array(),
                'eventOwnInfo' => $ownTagInfo,
                'createTime' => $this->_translateTime(strtotime($value['noticeCreateTime'])),
                'eventType' => $value['type'] == 3 ? 'group' : 'contact',
                'relation' => $relation,
                'praise' => $priseList,
                'hadPraised' => $this->_hadPraise($userId, $value['eventId']),
                'enable_open' => $this->isEnableOpen($userId, $value['eventOwnId'], $value['type']),
            );
        }

        #设置全部通知已读
        F::db()->execute('update notice set is_read = 1, read_time = ? where to_user_id = ? and is_read = 0', array(date(self::DEFAULT_TIME_STYLE), $userId));
        return $list;
    }

    /**
     * 获取自己创建的事件列表
     *
     * @access public
     * @param int $userId 用户id
     * @param number $page 页数
     * @param number $num 每页信息数目
     * @return array
     */
    public function getLikeEventUserList($userId, $page, $num) {
        $likeEventList = F::db()->query('select distinct event_id from user_event where type = 1')->fetch_column_all('event_id');
        if (empty($likeEventList)) return array();

        $eventListTmp = F::db()->fetch_all(
            'select event.id as eventId, user_id as userId, event.content as eventContent, create_time as createTime, create_user_id as createUserId, to_user_id as noticeUserId, photo, type
            from event left join notice on event.id = param where create_user_id = ? and event.status = 1 and event.id in ('.implode(',', $likeEventList).') order by event.id desc limit ?,?', array($userId, ($page-1)*$num, $num));

        $eventList = array();
        foreach ($eventListTmp as $value) {

            if ($value['type'] == 3) {
                $groupModel = F::load_model('group', array('groupId' => $value['userId']));
                $groupInfo = $groupModel->setGroupId($value['userId'])->get();
                $ownInfo[0]['nickname'] = $groupInfo['name'];
                foreach ($groupInfo['tagList'] as $tagListIndex) {
                    $ownTagInfo[] = array(
                        'tagId' => $tagListIndex['id'],
                        'tagName' => $tagListIndex['name'],
                        'coler' => $tagListIndex['tagClass']
                    );
                }
                $relation = $groupInfo['relation'];
            }else {
                $ownInfo = $this->_get($value['userId']);
                $ownTagInfo = $this->_getUserTagList($value['userId']);
                $relation = array();
            }

            $createUserInfo = $this->_get($value['createUserId']);
            $noticeUserInfo = $this->_get($value['noticeUserId']);
            $praiseList = $this->_getPraiseList($value['eventId']);
            $praiseListArray = array();
            foreach ($praiseList as $praiseListIndex) {
                $praiseUserInfo = $this->_get($praiseListIndex['userId']);
                $praiseListArray[] = $praiseUserInfo[0]['nickname'];
            }

            $eventList[] = array(
                'createUserId' => $value['userId'],
                'eventCreateUserNickname' => $ownInfo[0]['nickname'],
                'photo' => $value['photo'],
                'createUserPhoto' => $createUserInfo[0]['photo'],
                'createUser' => $createUserInfo[0]['nickname'],
                'eventContent' => $value['eventContent'],
                'noticeUser' => $noticeUserInfo[0]['nickname'] ? '@'.$noticeUserInfo[0]['nickname'] : '',
                'eventOwnInfo' => $ownTagInfo,
                'createTime' => $this->_translateTime(strtotime($value['createTime'])),
                'praiseList' => $praiseListArray,
                'eventType' => $value['type'] == 3 ? 'group' : 'contact',
                'relation' => $relation,
                'enable_open' => $this->isEnableOpen($userId, $value['userId'], $value['type']),
            );
        }

        #设置所有事件已读
        F::db()->execute('update event set unread_num = 0 where create_user_id = ? and status = 1', array($userId));
        return $eventList;
    }

    /**
     * 获取拥挤基本信息
     *
     * @access public
     * @param int $userId 用户id
     * @return array
     */
    public function get($userId) {
        return $this->_get($userId);
    }

    /**
     * 获取用户未读信息数目
     *
     * @access public
     * @param int $userId
     * @return array
     */
    public function getUserUnreadInfo($userId) {
        $unreadNoticeNum = F::db()->query('select count(id) as num from notice where to_user_id = ? and status = 1 and is_read = 0',
            array($userId))->fetch_column('num');
        $unreadLikeNum = F::db()->query('select sum(unread_num) as num from event where create_user_id = ? and status = 1', array($userId))->fetch_column('num');
        return array(
            'notice' => $unreadNoticeNum ? $unreadNoticeNum : 0,
            'like' => $unreadLikeNum ? $unreadLikeNum : 0
        );
    }

    /**
     * 用户喜欢一个事件
     *
     * @access public
     * @param int $userId
     * @param int $eventId
     * @return void
     */
    public function likeEvent($userId, $eventId) {
        F::db()->begin();
        F::db()->execute('replace into user_event set user_id = ?, event_id = ?, time = ?, type = 1, is_read = 0',
            array($userId, $eventId, date(self::DEFAULT_TIME_STYLE)));
        F::db()->execute('update event set unread_num = unread_num + 1 where id = ?', array($eventId));
        F::db()->commit();
    }

    /**
     * 用户取消喜欢一个事件
     *
     * @access public
     * @param int $userId
     * @param int $eventId
     * @return void
     */
    public function unlikeEvent($userId, $eventId) {
        F::db()->execute('delete from user_event where user_id = ? and event_id = ? and type = 1', array($userId, $eventId));
    }

    /**
     * 更新用户名称
     *
     * @access public
     * @param int $userId
     * @param string $username
     * @return void
     */
    public function updateUserProfile($userId, $username, $photo = '') {
        if ($photo) {
            F::db()->execute('replace into user_profile set nickname = ?, user_id = ?, update_time = ?, photo = ?',
                array($username, $userId, date(self::DEFAULT_TIME_STYLE), $photo));
        }else {
            F::db()->execute('replace into user_profile set nickname = ?, user_id = ?, update_time = ?', array($username, $userId, date(self::DEFAULT_TIME_STYLE)));
        }
    }

    /**
     * 授予用户相关项目管理权限
     *
     * @access public
     * @param int $userId
     * @param array $projectArray 被授权项目名称
     * @return boolean
     */
    public function getProjectAuth($userId, array $projectArray) {
        foreach ($projectArray as $value) {
            #获取项目对应tagid
            $value = trim($value);
            $projectTagId = $this->tagModel->getTagIdByName($value);
            $this->_bindTag($userId, $projectTagId);
        }
    }

    /**
     * 更新账户密码
     * 若更新了密码则返回true，未更新密码返回false
     *
     * @access public
     * @param int $userId
     * @param string $password
     * @param boolean $ignoreCheck 强制忽略检查密码
     * @return boolean
     */
    public function updatePassword($userId, $password, $ignoreCheck = FALSE) {
        $accountInfo = $this->getAccountInfo($userId);
        $accountInfo = array_shift($accountInfo);
        if ($accountInfo['password'] == md5(md5($password)) && !$ignoreCheck ) return FALSE;
        else {
            F::db()->execute('update account set password = ? where user_id = ?', array(md5(md5($password)), $userId));
            if (!$ignoreCheck) $this->createToken($userId);
            return TRUE;
        }
    }

    /**
     * 删除事件
     *
     * @access public
     * @param int $userId
     * @param int $eventId
     * @return void
     */
    public function deleteEvent($userId, $eventId) {
        #判断删除权限
        $eventInfo = $this->getEventInfo($eventId);
        if ($eventInfo['createUserId'] != $userId) throw new Exception('只有事件创建者才可删除.', 203);

        #删除事件
        F::db()->execute('update event set status = 0 where id = ?', array($eventId));
        #判断删除事件后是否依然具有相关标签信息
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $eventInfo['eventContent'], $result);
        foreach ($result[0] as $eventTagIndex) {
            $eventTagIndex = str_replace('\'', '', $eventTagIndex);
            $exist = F::db()->query('select id from event where content like '."'%$eventTagIndex%'".' and user_id = ? and status = 1 limit 1',
                array($eventInfo['userId']))->fetch_column('id');
            if (!$exist) {
                #去除相关标签
                $tagName = str_replace('#', '', $eventTagIndex);
                $tagId = $this->tagModel->getTagIdByName($tagName);
                $this->unbindTag($eventInfo['userId'], $tagId);
            }
        }

        unset($eventInfo);
    }

    /**
     * 获取事件详细信息
     *
     * @access private
     * @param int $id
     * @return array
     */
    private function getEventInfo($id) {
        $eventInfo = F::db()->fetch('select user_id as userId, content as eventContent, create_user_id as createUserId from event where id = ? and status = 1 limit 1',
            array($id));
        return is_array($eventInfo) ? $eventInfo : array();
    }

    /**
     * 取消绑定用户标签
     *
     * @access private
     * @param int $userId
     * @param int $tagId
     * @return void
     */
    private function unbindTag($userId, $tagId) {
        F::db()->execute('delete from user_tag where user_id = ? and tag_id = ?', array($userId, $tagId));
    }

    /**
     * 根据用户昵称关键字搜索用户列表
     *
     * @access public
     * @param string $keyword
     * @param integer $page
     * @param integer $num
     * @return array
     */
    public function getUserListByKey($keyword, $page, $num) {
        if ($keyword) {
            $userList = F::db()->query('select nickname from user_profile left join account on user_profile.user_id = account.user_id
                where type != 2 and nickname like '."'$keyword%'".' limit ?, ?', array(($page-1)*$num, $num))->fetch_column_all('nickname');
        }else {
            $userList = F::db()->query('select nickname from user_profile left join account on user_profile.user_id = account.user_id
                where type != 2 limit ?, ?', array(($page-1)*$num, $num))->fetch_column_all('nickname');
        }
        return is_array($userList) ? $userList : array();
    }

    /**
     * 获取用户房间信息
     *
     * @param int $userId 用户id
     * @return array
     */
    public function getUserRoom($userId) {
        $roomList = F::db()->fetch_all('SELECT name FROM `user_tag` left join tag on user_tag.tag_id = tag.id where user_tag.user_id = ?
            and tag.name like \'R%\'', array($userId));
        return is_array($roomList) ? $roomList : array();
    }

    /**
     * 根据账户类型，获取其下所有用户信息列表
     *
     * @param int $accountType 账户类型
     * @return array
     */
    public function getUserProfileListByAccountType($accountType) {
        #获取对应账户类型下用户列表
        if ($accountType == 1) {
            $userList = F::db()->query('select user_id from account where status = 1 and (type = ? or type = 3) order by level desc, id asc', array($accountType))->fetch_column_all('user_id');
        }else {
            $userList = F::db()->query('select user_id from account where status = 1 and type = ? order by level desc, id asc ', array($accountType))->fetch_column_all('user_id');
        }
        #填充用户信息
        foreach ($userList as $value) {
            $userInfo = $this->_get($value);
            $returnUserList[] = array(
                'photo' => $userInfo[0]['photo'],
                'nickname' => $userInfo[0]['nickname'],
                'phoneNum' => $userInfo[0]['phone_num']
            );
            unset($userInfo);
        }
        unset($userList);

        return is_array($returnUserList) ? $returnUserList : array();
    }

    /**
     * 获取内部员工信息列表
     * @param  int $page 页书
     * @param  int $num  每页信息数目
     * @param  string $keyword 搜索关键字内容
     * @return array
     */
    public function getUserListForAdmin($keyword, $page, $num) {
        if ($keyword) {
            $list = F::db()->fetch_all('select id, account.user_id as user_id, phone_num, regist_time, email, nickname from account left join user_profile on account.user_id = user_profile.user_id
                where (nickname like '."'%$keyword%'".' or phone_num like '."'%$keyword%'".') and account.status = 1 and type = 1 limit ?, ?',
                array( ($page-1)*$num, $num ));
        }else {
            $list = F::db()->fetch_all('select id, account.user_id as user_id, phone_num, regist_time, email, nickname from account left join user_profile on account.user_id = user_profile.user_id where account.status = 1 and type = 1 limit ?, ?',
                array( ($page-1)*$num, $num ));
        }
        return is_array($list) ? $list : array();
    }

    /**
     * 获取总计注册内容账户数目
     * @return int
     */
    public function getTotalUserNum($keyword) {
        if ($keyword) {
            $num = F::db()->query('select count(id) as num from account left join user_profile on account.user_id = user_profile.user_id
                where (nickname like '."'%$keyword%'".' or phone_num like '."'%$keyword%'".') and type = ? and status = 1', array(1))->fetch_column('num');
        }else {
            $num = F::db()->query('select count(1) as num from account where type = ? and status = 1', array(1))->fetch_column('num');
        }
        return $num ? $num : 0;
    }

    /**
     * 发送统计通知右键
     * @param  string $email 接收邮件地址
     * @return boolean
     */
    public function sendCountTaskEmail($email) {
        if (!$email) return TRUE;
        return $this->_baseSendEmail($email, '通知:统计已经结束', '您申请的统计已经结束，<a href="http://crm.nashspace.com:8899/Home/Count/lst">点击查看统计结果</a>');
    }
 }

?>