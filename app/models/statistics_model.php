<?php

final class statistics_model
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

    public function __construct() {
        $this->userModel = F::load_model('user');
        //$this->tagModel = F::load_model('tag', array());
    }

    public function __destruct() {
        unset($this->userModel);
        //unset($this->tagModel);
    }

    /**
     * 获取创建事件统计信息
     *
     * @return array
     */
    public function event() {
        $statisticsResult = F::db()->fetch_all('select count(id) as num, create_user_id as userId from event where status = 1 and type != 1 group by create_user_id ');
        foreach ($statisticsResult as &$value) {
            #删除脚本倒入数据
            if ($value['userId'] == 1) $value['num'] = ($value['num'] - 5260) > 0 ? $value['num'] - 5260 : $value['num'];

            $userInfo = $this->userModel->get($value['userId']);
            $value['userInfo'] = $userInfo;

            $projectInfo = $this->userModel->getUserTagList($value['userId']);
            $value['projectInfo'] = array();
            foreach ($projectInfo as $projectInfoIndex) {
                if ($projectInfoIndex['type'] == 2 || $projectInfoIndex['type'] == 3)
                    $value['projectInfo'][] = array('tagId' => $projectInfoIndex['tagId'], 'name' => $projectInfoIndex['tagName']);
            }
            unset($projectInfo);

            $value['weekNum'] = $this->getEventNumByWeek($value['userId']);
            $value['createContentNumByWeek'] = $this->getCreateContentNumByWeek($value['userId']);
            $value['createGroupNumByWeek'] = $this->getCreateGroupByWeek($value['userId']);
        }

        return $this->multi_array_sort($statisticsResult, 'weekNum', SORT_DESC);
    }

    /**
     * 获取本周创建事件数目
     * @param int $userId 用户id
     * @return int
     */
    private function getEventNumByWeek($userId) {
        $startTime = date('Y-m-d', strtotime('this week')).' 00:00:00';
        $endTime = date('Y-m-d', strtotime('last day this week +7 day')).' 23:59:59';
        $num = F::db()->query('select count(id) as num from event where create_user_id = ? and status = 1 and type != 1 and create_time between ? and ?', array($userId, $startTime, $endTime))->fetch_column('num');
        #删除脚本倒入数据
        if ($userId == 1) $num = ($num - 5260) > 0 ? $num - 5260 : $num;
        return $num ? $num : 0;
    }

    /**
     * 获取本周创建新的联系人数目
     *
     * @param int $userId
     * @return int
     */
    private function getCreateContentNumByWeek($userId) {
        $startTime = date('Y-m-d', strtotime('this week')).' 00:00:00';
        $endTime = date('Y-m-d', strtotime('last day this week +7 day')).' 23:59:59';
        $num = F::db()->query('select count(id) as num from account where create_id = ? and type = 2 and status = 1 and regist_time between ? and ?', array($userId, $startTime, $endTime))->fetch_column('num');
        return $num ? $num : 0;
    }

    /**
     * 获取本周创建新的群组数目
     *
     * @param int $userId
     * @return int
     */
    private function getCreateGroupByWeek($userId) {
        $startTime = date('Y-m-d', strtotime('this week')).' 00:00:00';
        $endTime = date('Y-m-d', strtotime('last day this week +7 day')).' 23:59:59';
        $num = F::db()->query('select count(id) as num from `group` where create_user_id = ? and status = 1 and create_time between ? and ?', array($userId, $startTime, $endTime))->fetch_column('num');
        #删除脚本倒入数据
        if ($userId == 1) $num = ($num - 5260) > 0 ? $num - 5260 : $num;
        return $num ? $num : 0;
    }

    /**
     * 二维数组排序
     *
     * @access protected
     * @param array $multi_array
     * @param key $sort_key
     * @param string $sort
     * @return boolean|unknown
     */
    protected function multi_array_sort($multi_array,$sort_key,$sort=SORT_ASC) {
        if(is_array($multi_array)){
            foreach ($multi_array as $row_array){
                if(is_array($row_array)){
                    $key_array[] = $row_array[$sort_key];
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
        array_multisort($key_array,$sort,$multi_array);
        return $multi_array;
    }

    /**
     * 获取待统计数据
     * @return array
     */
    public function getCountTaskData() {
        $data = F::db()->fetch_all('select id, tag_name, begin_time, end_time, email from count where is_executed = 0 order by id desc');
        return is_array($data) ? $data : array();
    }

    /**
     * 执行统计
     * @param  array  $param 统计参数数组
     * @return boolean
     */
    public function count(array $param) {
        #定义redis使用key数组，用于程序执行结束后的相关信息回收
        $_redisKeyArray = array();
        
        #更换标签id为name
        $tagNameList = $this->_getTagNameListByTagIdString($param['tag_name']);
        #获取时间范围内事件列表
        $eventList = array();
        foreach ($tagNameList as $value) {
            $tmp = F::db()->fetch_all('select id, user_id, type from `event` where content like "%#'.$value.'%" and status = 1 and create_time between ? and ?',
                    array($param['begin_time'], $param['end_time']));
            foreach ($tmp as &$index) {
                if ($index['type'] == 3) {
                    $index['projectName'] = F::db()->query('select tag.name as name from group_tag left join tag on group_tag.tag_id = tag.id where tag.type = 2 and group_tag.group_id = ? limit 1',
                        array($index['user_id']))->fetch_column('name');
                }else {
                    $index['projectName'] = F::db()->query('select tag.name as name from user_tag left join tag on user_tag.tag_id = tag.id where tag.type = 2 and user_tag.user_id = ? limit 1',
                        array($index['user_id']))->fetch_column('name');
                }
            }

            $eventList[$value] = $tmp;
        }
        foreach ($eventList as $key => $value) {
            foreach ($value as $index) {
                F::redis()->getHandel()->sAdd($key, serialize($index));
                $_redisKeyArray[] = $key;
            }
        }

        $key = $tagNameList[0];
        $keyArray = array();
        for ($i = 1; $i < count($tagNameList); $i++) {
            $result = F::redis()->getHandel()->sInterStore($key.'#'.$tagNameList[$i], $key, $tagNameList[$i]);
            $_redisKeyArray[] = $key.'#'.$tagNameList[$i];
            $key = $key.'#'.$tagNameList[$i];
            $keyArray[] = $key;
        }

        #统计标签出现次数
        $result = array();
        $tagNameList = array_merge($tagNameList, $keyArray);
        foreach ($tagNameList as $value) {
            $membersData = array();
            $members = F::redis()->getHandel()->sMembers($value);
            foreach ($members as $membersIndex) {
                $membersIndex = unserialize($membersIndex);
                $membersData[$membersIndex['projectName']][] = $membersIndex;
            }

            $result[$value] = $membersData;
        }

        $returnData = array();
        foreach ($result as $key => $value) {
            $returnData[$key]['全部'] = 0;
            foreach ($value as $projectKey => $valueIndex) {
                $projectKey ? $returnData[$key][$projectKey] = count($valueIndex) : $returnData[$key]['未知'] = count($valueIndex);
                $returnData[$key]['全部'] += count($valueIndex);
            }
        }

        #销毁redis相关数据
        F::redis()->getHandel()->delete($_redisKeyArray);
        
        return $this->_countLog($returnData, $param['id']);
    }

    /**
     * 根据标签字符串获取标签名称
     * @param  string $tagNameString 标签名字符串，多个标签使用,链接
     * @return array
     */
    private function _getTagNameListByTagIdString($tagNameString) {
        $tagNameList = F::db()->query('select name from tag where id in ('.$tagNameString.')')->fetch_column_all('name');
        return is_array($tagNameList) ? $tagNameList : array();
    }

    /**
     * 写入统计结果
     * @param  array  $result 统计结果
     * @param  int    $id     申请统计id
     * @return boolean
     */
    private function _countLog(array $result, $id) {
        F::db()->execute('update count set result = ?, is_executed = 1 where id = ?', array(serialize($result), $id));
        return TRUE;
    }
}

?>