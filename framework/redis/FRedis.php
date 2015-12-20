<?php

final class FRedis
{
    /**
     * redis客户端
     * @var Redis
     */
    private $cilent;

    /**
     * 构造函数
     * 
     * @access public
     * @param string $host
     * @param number $port
     * @throws Exception
     * @return FRedis
     */
    function __construct($host = '127.0.0.1', $port = 6379)
    {
        if ($host && $port) {
            $this->cilent = new Redis();
            $connectResult = $this->cilent->connect($host, $port);
            if (!$connectResult) throw new Exception('连接redis服务器失败', 200);
        }else throw new Exception('缺少redis链接信息', 200);
        
        return $this->cilent;
    }
    
    /**
     * 析构函数
     * 
     * @access public
     * @return void
     */
    function __destruct()
    {
        $this->cilent->close();
        unset($this->cilent);
    }
    
    /**
     * 获取操作对象
     * 
     * @access public
     * @return Redis
     */
    public function getHandel() {
        return $this->cilent;
    }
}

?>