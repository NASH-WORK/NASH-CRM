<?php
/**
 * 管理后台controller
 *
 * @author zhaoguang
 * @version 1.0.0
 */
final class admin_controller extends \base_controller
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_NUM = 20;
    const COUNT_TASK_TOKEN = 'xxxxxx';

    /**
     * 用户model
     * @var user_model
     */
    private $userModel;

    /**
     * 统计model
     * @var statistics_model
     */
    private $statisticsModel;

    public function __construct()
    {
        parent::__construct();
    }

    function __destruct()
    {
        parent::__destruct();
        unset($this->userModel);
        unset($this->statisticsModel);
    }

    /**
     * 获取内部员工信息列表
     *
     * @param int page 页书，默认1
     * @param int num 每页信息数目，默认20
     * @param string keyword 搜索关键字信息，可以使人名or手机号, 默认搜索全部信息
     * @return array
     */
    public function getAccountList() {
        #参数检查
        $this->params['page'] = F::request('page', self::DEFAULT_PAGE);
        $this->params['num'] = F::request('num', self::DEFAULT_NUM);
        $this->params['keyword'] = F::request('keyword', '');

        #获取列表信息
        $this->userModel = F::load_model('user', array());
        $returnData = $this->userModel->getUserListForAdmin($this->params['keyword'], $this->params['page'], $this->params['num']);

        #获取信息总量
        $this->returnData['count'] = $this->userModel->getTotalUserNum($this->params['keyword']);

        #组装返回信息
        foreach ($returnData as $value) {
            $_registerTime = strtotime($value['regist_time']);

            $this->returnData['list'][] = array(
                'id' => $value['id'],
                'phone_num' => $value['phone_num'],
                'user_id' => $value['user_id'],
                'regist_time' => $_registerTime > 0 ? date('Y-m-d', $_registerTime) : '2015-05-01',
                'email' => $value['email'],
                'name' => $value['nickname'],
            );
        }

        unset($returnData);
        F::rest()->show_result($this->returnData);
    }

    /**
     * 定时脚本，用于完成统计任务
     * 脚本每次执行只统计一组数据
     * @return void
     * @throws 100 If 无效的授权信息
     */
    public function countTask() {
        #参数检查
        $this->params = $this->require_params(array('token'));
        if ($this->params['token'] != self::COUNT_TASK_TOKEN ) throw new Exception('无效的授权信息', 100);

        #获取待统计数据
        $this->statisticsModel = F::load_model('statistics', array());
        $countDataAll = $this->statisticsModel->getCountTaskData();
        if (empty($countDataAll)) F::rest()->show_result();

        foreach ($countDataAll as $countData) {
            #执行统计
            $this->returnData = $this->statisticsModel->count($countData);

            #通知脚本执行完毕
            $this->userModel = F::load_model('user', array());
            $this->userModel->sendCountTaskEmail($countData['email']);
        }

        F::rest()->show_result();
    }
}

?>