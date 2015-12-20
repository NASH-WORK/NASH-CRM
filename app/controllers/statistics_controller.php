<?php
/**
 * 统计controller
 *
 * @author zhaoguang
 * @version 1.0
 */
final class statistics_controller extends \base_controller
{
    /**
     * 统计model
     * @var statistics_model
     */
    private $statisticsModel;

    public function __construct()
    {
        parent::__construct();
        $this->statisticsModel = F::load_model('statistics');
    }

    function __destruct()
    {
        parent::__destruct();
        unset($this->statisticsModel);
    }

    /**
     * 获取使用CRM系统产生信息概况
     *
     * @param string $type 统计信息类型
     * @return array
     */
    public function overview() {
        $this->params = $this->require_params(array('type'));
        $callFunctionName = $this->params['type'];

        if (method_exists($this->statisticsModel, $callFunctionName)) {
            $this->returnData = $this->statisticsModel->$callFunctionName();
        }else throw new Exception('统计信息类型'.$this->params['type'].'无法识别', 110);

        F::rest()->show_result($this->returnData);
    }

    /**
     * 每日凌晨统计使用非法维修记录信息
     * @return void
     */
    public function illegalAccountCount() {
        #获取起至时间
        $date = date('Y-m-d');
        $startTime = strtotime($date);
        $endTime = strtotime('+1day', $startTime);

        #获取违规数据
        $list = F::db()->fetch_all('select count(id) as num, create_user_id from event where content like "%#维修%" and type != 3 and create_time between ? and ? and status = 1 group by create_user_id',
            array(date(TIMESTYLE, $startTime), date(TIMESTYLE, $endTime-1)));
        foreach ($list as $value) {
            $username = F::db()->query('select nickname from user_profile where user_id = ? limit 1', array($value['create_user_id']))->fetch_column('nickname');
            #写入错误日志文件
            file_put_contents('/home/wwwlogs/errorData/'.date('Y-m-d'), $username.' 违规书写维修记录'.$value['num'].'个'."\r\n", FILE_APPEND );
        }

        unset($list);
        F::rest()->show_result();
    }
}

?>