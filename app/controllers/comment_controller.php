<?php
/**
 * 评论controller
 *
 * @author zhaoguang
 * @version 1.0
 */
final class comment_controller extends \base_controller
{
    /**
     * 评论model
     * @var comment_model
     */
    private $commentModel;

    /**
     * 评论内容最大长度
     * @var int
     */
    const MAX_COMMENT_LENGTH = 255;

    public function __construct()
    {
        parent::__construct();
        $commentParam['eventId'] = F::request('eventId', 0);
        $this->commentModel = F::load_model('comment', $commentParam);
    }

    function __destruct()
    {
        parent::__destruct();
        unset($this->commentModel);
    }

    /**
     * 创建一则评论
     *
     * @access public
     * @param int eventId
     * @param string content 评论内容,最多255
     * @param int replayId
     * @return void
     */
    public function create() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('eventId', 'content'));
        $this->params['replayId'] = F::request('replayId', 0);

        if (strlen($this->params['content']) > self::MAX_COMMENT_LENGTH ) throw new Exception('评论内容过长，不可超过255个字节', 107);

        $this->commentModel->create($GLOBALS['userId'], $this->params['eventId'], $this->params['content'], $this->params['replayId']);
        F::rest()->show_result();
    }

    /**
     * 获取评论列表
     *
     * @access public
     * @param string type
     * @param int $page
     * @param int $num
     * @return array
     */
    public function getList() {
        $this->checkAccessToken();
        $this->params = $this->require_params(array('type'));
        if ($this->params['type'] == 'event') $this->require_params(array('eventId'));

        $this->params['page'] = F::request('page', 1);
        $this->params['num'] = F::request('num', 20);

        switch ($this->params['type']) {
            case 'event':
                $this->returnData = $this->commentModel->getListByEvent($this->params['eventId'], $this->params['page'], $this->params['num']);
            break;

            default:
                throw new Exception('未知的type类型', 100);
            break;
        }
        F::rest()->show_result($this->returnData);
    }
}

?>