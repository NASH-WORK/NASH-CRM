<?php
/**
 * 同步controller
 * 负责对外的同步接口
 */
final class sync_controller extends \base_controller
{
    /**
     * 同步秘钥
     * @var string
     */
    const TOKEN = 'xxx';

    /**
     * 同步model
     * @var sync_model
     */
    private $syncModel;

    #构造函数
    public function __construct()
    {
        parent::__construct();
        $this->syncModel = F::load_model('sync', array());

        #请求权限检查
        $this->params = $this->require_params(array(
            'token'
        ));
        if ($this->params['token'] != self::TOKEN) throw new Exception('无效的授权信息', 100);
    }

    function __destruct()
    {
        parent::__destruct();
        unset($this->syncModel);
    }
}

?>