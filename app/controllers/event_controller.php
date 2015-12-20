<?php
/**
 * 事件controller
 * 逐步接替实现事件的创建，查找，删除等操作
 *
 * @author zhaoguang
 * @version 1.1
 */
final class event_controller extends \base_controller
{
    /**
     * 事件model
     * @var event_model
     */
    private $eventModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventModel = F::load_model('event', array());
    }

    public function __destruct()
    {
        unset($this->eventModel);
    }

    /**
     * 更新事件
     *
     * @param int id 事件id
     * @param string content 事件内容
     * @param string accessToken 用户授权信息
     * @throws Exception 206 if 非事件创建者尝试修改信息
     */
    public function update() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('id', 'content'));

        #获取事件信息
        $this->returnData = $this->eventModel->get($this->params['id']);
        #判断权限
        if ($this->params['createUserId'] != $GLOBALS['userId']) throw new Exception('事件创建者与事件修改者不一致, 无法修改', 205);
        #更新事件
        $this->eventModel->update($this->params['id'], $this->params['content']);

        #记录操作日志
        $this->userLog($GLOBALS['userId'],  __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        #返回操作结果
        F::rest()->show_result();
    }

    public function index() {
//         $this->returnData = $this->eventModel->getListByUser(425, 1431792000, 1432396799);
//         F::rest()->show_result($this->returnData);
        $this->returnData = $this->eventModel->sta();
    }
}

?>