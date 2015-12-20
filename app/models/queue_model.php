<?php
/**
 * 队列model
 *
 * @author ruckfull <ruckfull@gmail.com>
 */
final class queue_model
{
    /**
     * 队列id
     * @var integer
     */
    private $queueId = 0;

    /**
     * 队列发送地址
     * @var string
     */
    private $sendRequestUr = '';

    /**
     * 请求参数
     * @var array
     */
    private $sendRequestParam = array();
    
    /**
     * 默认队列发送地址
     * @var string
     */
    const DEFAULT_NOTIFY_URL = 'http://capital.nashwork.com:8899/Admin/Admin/addEvent';

    /**
     * 构造函数
     * @param array $config 构造参数，包括id， url
     * @return void
     */
    public function __construct(array $config) {
        $this->queueId = isset($config['id']) ? $config['id'] : 0;
        $this->sendRequestUr = isset($config['url']) ? $config['url'] : self::DEFAULT_NOTIFY_URL;
    }

    public function __destruct(){
        unset($this->sendRequestParam);
        unset($this->sendRequestUr);
        unset($this->queueId);
    }

    /**
     * 设置队列id
     * @param int $id 队列id
     * @return queue_model
     */
    public function setQueueId($id) {
        $this->queueId = $id;
        return $this;
    }

    /**
     * 获取队列id
     * @return int
     */
    public function getQueueId() {
        return $this->queueId;
    }

    /**
     * 设置异步请求参数
     * @param array $param 异步请求参数数组
     * @return queue_model
     */
    public function setSendRequestParam(array $param) {
        $this->sendRequestParam = $param;
        return $this;
    }

    /**
     * 设置异步请求地址
     * @param string $url 异步请求地址
     * @return queue_model
     */
    public function setSendRequestUrl($url) {
        $this->sendRequestUr = $url;
        return $this;
    }

    /**
     * 生成一个异步通知
     * @return boolean
     */
    public function createSyncNotify() {
        $_createTime = isset($this->sendRequestParam['create_time']) ? $this->sendRequestParam['create_time'] : date(TIMESTYLE);
        $this->queueId = F::db()->execute('insert into queue set taskphp = ?, param = ?, create_time = ?, status = ?',
            array($this->sendRequestUr, serialize($this->sendRequestParam), $_createTime, 0))->insert_id();
        return TRUE;
    }
}

?>