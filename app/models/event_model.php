<?php
/**
 * 事件model
 * 负责事件的增删改查操作
 * 
 * @author zhaoguang
 * @version 1.1
 */
final class event_model
{
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
    
    #获取事件详情
    public function get($id) {}
    
    #更新事件内容
    public function update($id, $content) {}
    
    #删除事件
    public function delete($id) {}
    
    #获取某人一段时间内的工作日志
    public function getListByUser($userId, $startTime, $endTime, $listType = 'event') {
        $list = F::db()->fetch_all('SELECT id, content, create_time, user_id FROM `event` where create_user_id = ? and create_time between ? and ? and status = 1', 
            array($userId, date(TIMESTYLE, $startTime), date(TIMESTYLE, $endTime) ));
        
        #按照日期分组
        $returnData = array();
        foreach ($list as $value) {
            $value['createTime'] = date(DAYSTYLE, strtotime($value['create_time']));
            unset($value['create_time']);
            #查询房间信息
            $value['room'] = $this->getRoomInfoFromContent($value['content'], $value['user_id']);
            $returnData[$value['createTime']][] = $value;
        }
        
        F::showLog($returnData);
    }
    
    /**
     * 尝试获取事件对应房间信息，若事件内容中存在房间号信息则使用房间号，否则通过标签查找用户对应的房间信息
     * 问题：若通过标签查找对应2个以上房间号如何操作, 现在为直接返回空
     * 
     * @param string $content 事件内容
     * @param int $userId 用户id
     * @return string
     */
    private function getRoomInfoFromContent($content, $userId) {
        preg_match('/#R\w{1,}/', $content, $match);
        if (isset($match[0])) return str_replace('#R', '', $match[0]);
        else {
            $roomInfoFromTag = $this->userModel->getUserRoom($userId);
            if (is_array($roomInfoFromTag) && count($roomInfoFromTag) == 1) return str_replace('R', '', $roomInfoFromTag[0]['name']);
            else return '';
        }
    }
    
    #获取项目一段时间内的工作日志
    public function getListByProject($projectId, $startTime, $endTime, $listType = 'repair') {}
    
    /**
     * 统计现在所有的人曾经创建的标签
     * 
     * @return array
     */
    public function sta() {
        $eventList = F::db()->fetch_all('select content, create_user_id from event where status = 1 order by event.id desc');
        
        #整理标签信息
        $returnData = array();
        foreach ($eventList as $value) {
            $tagArray = $this->getTagFromEvent($value['content']);
            foreach ($tagArray as $index) {
                preg_match('/R\w{1,}/', $index, $match);
                if (isset($match[0]) && $match[0]) continue;
                if (!in_array($index, $returnData[$value['create_user_id']])) $returnData[$value['create_user_id']][] = $index;
            } 
        }
        
        #补充用户信息
        foreach ($returnData as $key => $value) {
            $userInfo = $this->userModel->get($key);
            $returnData[$userInfo[0]['nickname']] = implode(',', $value);
            unset($returnData[$key]);
        }
        
        return $returnData;
    }
    
    /**
     * 正则方式匹配标签信息
     * 
     * @param string $content 待匹配信息字符串
     * @return array
     */
    private function getTagFromEvent($content) {
        preg_match_all('/#([^#^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/', $content, $matches);
        return $matches[1];
    }
}

?>