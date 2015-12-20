<?php

final class sync_model
{
    /**
     * 获取所有房间维修数据
     * @param  int $lastId 最后一则信息id
     * @param  int $num    返回信息数目
     * @return array
     */
    public function getFixList($lastId, $num) {
        #获取原始数据
        $list = $this->_getFixListSrc($lastId, $num);
        foreach ($list as &$value) {
            #去除#和@信息
            $value['content'] = trim($this->_deleteNoticeInfo($this->_deleteTagInfo($value['content'])));
            #根据user_id&type 转化为项目名称&房间名格式
            $value['roomInfo'] = $this->_getRoomInfo($value['user_id'], $value['type']);
        }
        return is_array($list) ? $list : array();
    }

    /**
     * 获取原始维修房间数据
     * @param  int $lastId 最后一则信息id
     * @param  int $num    返回信息数目
     * @return array
     */
    private function _getFixListSrc($lastId, $num) {
        $list = F::db()->fetch_all('select id, user_id, content, create_time, create_user_id, photo, type from `event` where id > ? and content like "%#维修%" and status = 1 order by id desc limit ?',
            array($lastId, $num));
        return is_array($list) ? $list : array();
    }

    /**
     * 删除事件中存在的tag信息
     * @param  string $content 维修事件内容
     * @return string
     */
    private function _deleteTagInfo($content) {
        return preg_replace($this->_getRegixString('#'), '', $content);
    }

    /**
     * 删除事件中存在的notice信息
     * @param  string $content 维修时间内容
     * @return string
     */
    private function _deleteNoticeInfo($content) {
        return preg_replace($this->_getRegixString('@'), '', $content);
    }

    /**
     * 获取正则表达式字符串
     * @param  string $char 需要代替的字符
     * @return string
     */
    private function _getRegixString($char) {
        return '/'.$char.'([^'.$char.'^\\s^:]{1,})([\\s\\:\\,\\;]{0,1})/';
    }

    /**
     * 根据事件所有者id和事件类型，获取事件所属房间信息
     * @param  int $paramId   房间id或者用户id
     * @param  int $eventType 事件类型
     * @return array
     */
    public function _getRoomInfo($paramId, $eventType) {
        return $eventType == 3 ? $this->_getRoomInfoByGroup($paramId) : $this->_getRoomInfoByUser($paramId);
    }

    /**
     * 通过房间id获取房间信息
     * @param  int $roomId 房间id
     * @return array
     */
    private function _getRoomInfoByGroup($roomId) {
        $roomInfo = F::db()->query('select name from `group` where id = ? limit 1', array($roomId))->fetch_column('name');
        preg_match('/\w{1,}/', $roomInfo, $roomName);
        
        return array(
            'roomName' => $roomName[0],
            'projectName' => str_replace($roomName[0], '', $roomInfo),
        );
    }

    /**
     * 通过用户id获取房间信息
     * @param  int $userId 用户id
     * @return array
     */
    private function _getRoomInfoByUser($userId) {
        #获取用户所有的tag信息
        $userTagList = F::db()->fetch_all('select name, type from `user_tag` left join tag on user_tag.tag_id = tag.id where user_id = ?', array($userId));
        foreach ($userTagList as $value) {
            if ($value['type'] == 2 ) $projectName = $value['name'];
            if (preg_match('/\w{1,}/', $value['name'])) $roomName = $value['name'];
        }
        
        return $projectName && $roomName ? array(
            'roomName' => $roomName,
            'projectName' => $projectName,
        ) : array();
    }
}

?>