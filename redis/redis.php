<?php
namespace database\redis;
use keenly\config;
use keenly\base\Singleton;

/**
 * This file is part of keenly from.
 * @author brain_yang<qiaopi520@qq.com>
 * (c) brain_yang
 * github: https://github.com/keenlysoft/
 * @time 2018年10月27日
 * For the full copyright and license information, please view the LICENSE
 */

class redis extends  \Redis{
    
    use Singleton;
    
    public $redis;
    
    private $config = [];
    
    protected $connect;
    
    const PLINK = 'pconnect';
    
    const CLINK = 'connect';
    
    
    public function __construct(){
        $this->redis = new \Redis();
        $this->GetConfig()->link();
    }
    
    
   private function link(){
        if($this->config['driver'] == self::PLINK){
            $this->pcontent($this->config['host'],$this->config['port'],$this->config['timeout']);
        }else{
            $this->content($this->config['host'],$this->config['port'],$this->config['timeout'],null,$this->config['rebinding']);
        }
        $this->redis->auth($this->config['password']);
        return $this->redis;
    }
    
    
    public function use($db = 0){
       $this->redis->select($db);
       return $this->redis;
    }
    
    
    /**
     * @name pconnect
     * @param host $host
     * @param port $port
     * @param timeout $timeout
     * @param x $xyz
     * @return object
     */
    public function pcontent(...$args):self
    {
        $this->connect = $this->redis->pconnect(...$args);
        return $this;
    }
    
    
    public function content(...$args):self
    {
        $this->connect = $this->redis->connect(...$args);
        return $this;
    }
    
    
    /**
     * @name 获取redis配置文件
     * @author: youming
     * @time 2018年11月9日 下午7:11:19
     */
    private function GetConfig():self
    {
        $this->config = config::reload('database')->Get('redis');
        return $this;
    }
    
    
    
    
}