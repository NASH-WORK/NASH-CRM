<?php
/**
 * 标签controller
 *
 * @author zhaoguang
 * @version 1.0
 */
final class tag_controller extends \base_controller
{
    /**
     * 用户信息类型标签类型
     * @var int
     */
    const USER_PROFILE_TAG = 10;

    /**
     * 标签model
     * @var tag_model
     */
    private $tagModel;

    /**
     * 构造函数
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->tagModel = F::load_model('tag');
    }

    /**
     * (non-PHPdoc)
     * @see base_controller::__destruct()
     */
    public function __destruct() {
        unset($this->tagModel);
        parent::__destruct();
    }

    /**
     * 创建标签
     *
     * @access public
     * @param string name 标签名称
     * @return void
     */
    public function create() {
        #参数检查
        $this->checkAccessToken();
        $this->params = $this->require_params(array('name', 'type'));
        if ($this->params['type'] == self::USER_PROFILE_TAG ) $this->params = $this->require_params(array('keyName'));
        $this->params['color'] = F::request('color', '');
        $this->params['name'] = trim($this->params['name']);

        #创建标签
        $this->tagModel->create($this->params['name'], $this->params['type'], $this->params['color'], $GLOBALS['userId'], $this->params['keyName']);
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();
    }

    /**
     * 创建标签
     *
     * @access public
     * @param string name 标签名称
     * @return void
     */
    public function createNoAccessToken() {
        #参数检查
        $GLOBALS['userId'] = 1;
        $this->params = $this->require_params(array('name', 'type'));
        if ($this->params['type'] == self::USER_PROFILE_TAG ) $this->params = $this->require_params(array('keyName'));
        $this->params['color'] = F::request('color', '');
        $this->params['name'] = trim($this->params['name']);

        #创建标签
        $this->tagModel->create($this->params['name'], $this->params['type'], $this->params['color'], $GLOBALS['userId'], $this->params['keyName']);
        $this->userLog($GLOBALS['userId'], __CLASS__.'/'.__FUNCTION__, serialize($this->params));
        F::rest()->show_result();
    }

    /**
     * 获取全部标签列表
     *
     * @access public
     * @return array
     */
    public function getList() {
        $this->returnData = $this->tagModel->getList();
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取用户profile标签列表
     *
     * @access public
     * @return array
     */
    public function getUserProfileList() {
        $this->returnData = $this->tagModel->getUserProfileList();
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取群组profile标签列表
     *
     * @access public
     * @return array
     */
    public function getGroupProfileList() {
        $this->returnData = $this->tagModel->getGroupProfileList();
        F::rest()->show_result($this->returnData);
    }

    /**
     * 根据标签名称获取其相关样式
     *
     * @access public
     * @param string name
     * @return string
     */
    public function getStyle() {
        $this->params = $this->require_params(array('name'));
        $this->returnData = $this->tagModel->getStyle($this->params['name']);
        F::rest()->show_result($this->returnData);
    }

    /**
     * 创建系统创建的非用户信息标签
     *
     * @access public
     * @return array
     */
    public function getListBySystem() {
        $this->returnData = $this->tagModel->getListBySystem();
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取所有系统创建的和用户profile属性标签列表
     *
     * @access public
     * @param string deleteTag
     * @return array
     */
    public function getListByAllSystem() {
        $this->params['deleteTag'] = F::request('deleteTag', '');
        $this->returnData = $this->tagModel->getListByAllSystem();
        F::rest()->show_result($this->returnData);
    }

    /**
     * 获取全部项目信息列表
     *
     * @access public
     * @return array
     */
    public function getALLProjectInfo() {
        $this->checkAccessToken();
        $this->returnData = $this->tagModel->getALLProjectInfo();
        F::rest()->show_result($this->returnData);
    }

}

?>