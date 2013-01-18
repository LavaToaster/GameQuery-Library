<?php
interface gameserverInterface
{
    public function getStatusInfo();
}
abstract class gameserver implements gameserverInterface
{
    /**
    * Server Info
    * 
    * @example array(
    *   'ip' => '127.0.0.1',
    *   'port' => '25565',
    *   'rconport' => '25566',
    *   'rconpassword' => 'password'
    *   'statusport' => '25565'
    * )
    * 
    * @var array
    */
    protected $serverInfo = array();
    
    public $serverOnline = false;
    
    public $serverStatus = array();
    
    public $error = array();
    
    public $registry;
        
    public function setInfo(array $settings)
    {
        $this->serverInfo = $settings;
        return $this;
    }
    
    /**
    * Pings the server to see if it's online!
    * 
    * return boolean
    */
    public function pingServer()
    {        
        $fsock = @fsockopen($this->serverInfo['ip'], $this->serverInfo['port'], $errno, $errstr, 2) ? 1 : 0;
        return $fsock; 
    }
}
?>