<?php
/**
 * 报表controller
 *
 * @author zhaoguang
 * @version 1.0
 */
final class reportForm_controller extends \base_controller
{
    /**
     * 报表model
     * @var reportForm_model
     */
    private $reportModel;

    public function __construct()
    {
        parent::__construct();
        $this->reportModel = F::load_model('reportForm');
    }

    function __destruct()
    {
        parent::__destruct();
        unset($this->reportModel);
    }

    /**
     * 生成报表数据
     *
     * @access public
     * @param string type 报表类型
     * @param int startTime 报表开始时间
     * @param int endTime 报表结束时间
     * @return array
     */
    public function create() {
        $this->params = $this->require_params(array('type', 'startTime', 'endTime'));

        if (method_exists($this->reportModel, $this->params['type'])) {
            $callFunction = $this->params['type'];
            $this->returnData = $this->reportModel->$callFunction($this->params['startTime'], $this->params['endTime']);
        }else throw new Exception('报表类型'.$this->params['type'].'生成方法不存在', 104);

        F::rest()->show_result($this->returnData);
    }
}

?>