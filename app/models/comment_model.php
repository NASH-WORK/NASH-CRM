<?php
/**
 * 评论model
 * 
 * @author zhaoguang
 *
 */
final class comment_model
{
    /**
     * 评论所属事件id
     * @var int
     */
    private $eventId;
    
    /**
     * 默认时间格式
     * @var string
     */
    const DEFAULT_TIME_STYLE = 'Y-m-d H:i:s';
    
    function __construct(array $commentParam)
    {
        $this->eventId = isset($commentParam['eventId']) ? $commentParam['eventId'] : 0;
    }

    function __destruct()
    {}
    
    /**
     * 创建一则评论
     * 
     * @access public
     * @param int $userId
     * @param int $eventId
     * @param string $content
     * @param number $replayId
     * @return int
     */
    public function create($userId, $eventId, $content, $replayId = 0) {
        return F::db()->execute('insert into comment set event_id = ?, user_id = ?, content = ?, time = ?, status = ?, replay_id = ?', 
            array($eventId, $userId, $content, date(self::DEFAULT_TIME_STYLE), 1, $replayId))->insert_id();
    }
    
    /**
     * 获取事件的评论列表
     * 
     * @access public
     * @param int $eventId 
     * @param int $page
     * @param int $num
     * @return array
     */
    public function getListByEvent($eventId, $page = 1, $num = 20) {
        $list = F::db()->fetch_all('select id, user_profile.user_id as userId, content, time, replay_id as replayId, nickname, photo from comment 
            left join user_profile on comment.user_id = user_profile.user_id where event_id = ? and status = 1 order by id desc limit ?, ?', 
            array($eventId, ($page-1)*$num, $num ));
        
        $returnCommentList = array();
        foreach ($list as $value) {
            if ($value['replayId']) {
                $commentDetail = $this->get($value['replayId']);
                $value['replayUserInfo'] = array('nickname' => $commentDetail['nickname'], 'photo' => $commentDetail['photo']);
            }else $value['replayUserInfo'] = array();
            
            $returnCommentList[] = array(
                'commentId' => $value['id'],
                'commentContent' => $value['content'],
                'time' => strtotime($value['time']),
                'sendUserInfo' => array('nickname' => $value['nickname'], 'photo' => $value['photo']),
                'replayUserInfo' => $value['replayUserInfo']
            );
        }
        
        return is_array($returnCommentList) ? $returnCommentList : array();
    }
    
    /**
     * 获取评论信息详情
     * 
     * @access public
     * @param int $id
     * @return array
     */
    public function get($id) {
        $detail = F::db()->fetch('select id, user_profile.user_id as userId, content, time, replay_id as replayId, nickname, photo 
            from comment left join user_profile on comment.user_id = user_profile.user_id where id = ? and status = 1 limit 1', array($id));
        return is_array($detail) ? $detail : array();
    }
    
}

?>