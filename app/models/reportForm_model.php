<?php
/**
 * 报表model
 *
 * @author zhaoguang
 * @version 1.0
 */
final class reportForm_model
{
    /**
     * 候选人标签id
     */
    const CANdDIDATE_TAG_ID = 59;

    /**
     * 通过面试标签id
     */
    const INTERVIEW_SUCCESS = 467;
    
    /**
     * 入职标签id
     */
    const ENTRY = 685;
    
    /**
     * 标签model
     * @var tag_model
     */
    private $tagModel;
    
    /**
     * 面试者model
     * @var audition_model
     */
    private $auditionModel;
    
    public function __construct() {
        $this->tagModel = F::load_model('tag');
        $this->auditionModel = F::load_model('audition');
    }
    
    public function __destruct() {
        unset($this->tagModel);
        unset($this->auditionModel);
    }

    /**
     * 生成招聘信息报表
     *
     * @access public
     * @param int $startTime
     * @param int $endTime
     * @return array
     */
    public function recruitment($startTime, $endTime) {
        #获取时间段内产生的招聘信息
        $userList = F::db()->query('select user_id from user_tag where tag_id = ? and bind_time between ? and ?', array(self::CANdDIDATE_TAG_ID, date('Y-m-d H:i:s', $startTime), date('Y-m-d H:i:s', $endTime)))->fetch_column_all('user_id');

        #计算参加初试人数/参加复试人数/通过招聘人数/入职人数
        $returnData = array(
            'first' => 0, 'second' => 0, 'success' => 0, 'entry' => 0
        );
        $returnDataDetail = array(
            'first' => array(), 'second' => array(), 'success' => array(), 'entry' => array()
        );
        foreach ($userList as $key => $value) {
            $eventList = F::db()->fetch_all('select * from event where user_id = ? and status = 1', array($value));
            $auditionInfo = $this->auditionModel->seachById($value);
            
            $returnData['first'] ++;
            $returnDataDetail['first'][$value] = $auditionInfo;
            foreach ($eventList as $eventIndex => $eventValue) {
                if ($eventValue['content'] == '#复试 @王则琼') {
                    $returnData['second']++;
                    $returnDataDetail['second'][$value] = $auditionInfo;
                }
            }
            if ($this->tagModel->userHadTagById($value, self::INTERVIEW_SUCCESS)) {
                $returnData['success']++;
                $returnDataDetail['success'][$value] = $auditionInfo;
            }
            if ($this->tagModel->userHadTagById($value, self::ENTRY)) {
                $returnData['entry']++;
                $returnDataDetail['entry'][$value] = $auditionInfo;
            }
            
            unset($eventList);
            unset($auditionInfo);
        }
        
        return array(
            'rate' => $returnData,
            'detail' => $returnDataDetail, 
        );
    }

    /**
     * 生成业务报表
     *
     * @access public
     * @param string $projectName
     * @param int $startTime
     * @param int $endTime
     * @return array
     */
    public function business($projectName, $startTime, $endTime) {
        return array();
    }

    /**
     * 生成用户日志报表
     *
     * @access public
     * @param int $userId
     * @param string $startTime
     * @param string $endTime
     * @return array
     */
    public function userLog($userId, $startTime, $endTime) {
        return array();
    }
}

?>