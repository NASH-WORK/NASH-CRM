<?php
/** 
 * 邮件类，通过phpMailer完成邮件发送任务
 * 
 * @author zhaoguang
 * @version 1.0
 */
final class email
{
    /**
     * 邮件编码
     * @var string
     */
    private $charSet = 'UTF-8';
    
    /**
     * 是否开启debug模式
     * @var boolean
     */
    private $isDebug = FALSE;
    
    /**
     * SMTP 服务器
     * @var string
     */
    private $host;
    
    /**
     * SMTP服务器的端口号
     * @var int
     */
    private $port = 25;
    
    /**
     * SMTP服务器用户名
     * @var string
     */
    private $userName;
    
    /**
     * SMTP服务器密码
     * @var string
     */
    private $password;
    
    /**
     * 发出邮箱地址
     * @var string
     */
    const SENDFROMADDRESS = 'nami@nash.work';
    
    /**
     * phpMailer类
     * @var PHPMailer
     */
    private $phpMailer;
    
    /**
     * 构造函数
     * 
     * @access public
     * @param string $config['host'] SMTP 服务器
     * @param string $config['username']
     * @param string $config['password'] 
     * @return void
     */
    public function __construct($config) {
        if (file_exists('third_party/phpMailer/class.phpmailer.php') && file_exists('third_party/phpMailer/class.smtp.php')) {
            include 'third_party/phpMailer/class.phpmailer.php';
            include 'third_party/phpMailer/class.smtp.php';
        }else throw new Exception('phpMailer lib not found.', 204);
        
        $this->host = $config['host'];
        $this->userName = $config['username'];
        $this->password = $config['password'];
        
        $this->phpMailer = new PHPMailer();
        $this->phpMailer->CharSet = $this->charSet;
        $this->phpMailer->isSMTP();
        $this->phpMailer->SMTPDebug = $this->isDebug;
        $this->phpMailer->SMTPAuth = true;
        $this->phpMailer->Host = $this->host;
        $this->phpMailer->Port = $this->port;
        $this->phpMailer->Username = $this->userName;
        $this->phpMailer->Password = $this->password;
        $this->phpMailer->SetFrom(self::SENDFROMADDRESS, '纳米服务');
        $this->phpMailer->Subject = '你有新的通知信息';
        $this->phpMailer->AltBody    = 'To view the message, please use an HTML compatible email viewer!'; // optional, comment out and test
        
    }
    
    /**
     * 析构函数
     */
    public function __destruct(){
        unset($this->phpMailer);
    }
    
    /**
     * 发送邮件
     * 
     * @access public
     * @param string $address 邮件地址
     * @param string $subject 邮件标题
     * @param string $content 邮件内容
     * @return array
     */
    public function send($address, $subject, $content) {
        $this->phpMailer->MsgHTML(eregi_replace("[\]",'',$content));
        $this->phpMailer->AddAddress($address, '');
        
        if(!$this->phpMailer->Send()) {
            #重置所有地址
            $this->phpMailer->clearAllRecipients();
            return array(
                'isSuccess' => false,
                'error' => $this->phpMailer->ErrorInfo
            );
        } else {
            #重置所有地址
            $this->phpMailer->clearAllRecipients();
            return array(
                'isSuccess' => true,
            );
        }
    }
}

?>