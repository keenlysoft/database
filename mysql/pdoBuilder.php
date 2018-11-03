<?php
namespace database\mysql;

use keenly\config;
use keenly\Exception\dbException;
use keenly\common;
use keenly\exception\KeenlyException;
define('ISCLI', PHP_SAPI === 'cli');




class pdoBuilder{
    
    use common;
    
    private $test;
    
    public  $db;
    
    protected   $dbh;
    
    private static  $databaseName = 'master';
    
    private static  $instance = [];
    
    private static $tempLike;
    
    private $config;
    
    private $dns;
    
    public $mysql;
    
    private $connectStr;
    
    private $setDB;
    
    protected  function __construct($dbName = null){
   
            return $this->GetConfig()->parseConfig()->setDB($dbName)->like();
    }
    
    
    private  function __clone(){
        return ;
    }
    
    
    
    public static  function connectionSql($dbName = null,$reboot = FALSE){
        if($reboot){
           self::$instance = false;
       }
       if(ISCLI && self::$tempLike && !self::pdo_ping(self::$tempLike)){
           self::$instance = false;
       }
       if(!self::$instance instanceof self){
           self::$instance = new static($dbName); 
       }
       return  self::$instance;
    }
    
    
    
    
    private function setDB($dbName = null,$init = FALSE){
       if(isset($dbName) && !empty($dbName) && !array_key_exists($dbName, $this->connectStr)){  
            throw new KeenlyException('Database does not exist');
       }else{
           $db = $this->connectStr[empty($dbName)?self::$databaseName:$dbName];
       }
       $this->dns = strstr($db, DIRECTORY_SEPARATOR,true);
       $data = substr(strstr($db, DIRECTORY_SEPARATOR),CRYPT_EXT_DES);
       parse_str($data,$this->db);
       return $this;
    }
    
    
    
    
    private function GetConfig(){
        $database = config::reload('database');
        $this->config = $database->Get('mysql','clusters');
        $this->mysql = $database->Get('mysql');
        return $this;
    }
    
    
    private function parseConfig(){     
            foreach ($this->config as $name => $value){
                $dns[$name] =  $this->dnsStructure( $value );
            }
        $this->connectStr = $dns;
        return $this;
    }
    
    
    
    
    private function dnsStructure($array){
        
        $dns = $array['driver'].':host='.$array['host'].';dbname='.$array['dbname'].';charset='.$array['charset'];
        unset($array['driver'],$array['host'],$array['dbname']);
        
        return $dns.DIRECTORY_SEPARATOR.http_build_query($array);
    }
    /**
     * DSN解析
     * 格式： mysql://username:passwd@localhost:3306/DbName?param1=val1&param2=val2#utf8
     * @static 
     * @access private
     * @param string $dsnStr
     * @return array
     */
    private static function parseDsn($dsnStr)
    {
        $info = parse_url($dsnStr);
        if (!$info) {
            return [];
        }
        $dsn = [
            'driver'     => $info['scheme'],
            'username' => isset($info['user']) ? $info['user'] : '',
            'password' => isset($info['pass']) ? $info['pass'] : '',
            'host' => isset($info['host']) ? $info['host'] : '',
            'hostport' => isset($info['port']) ? $info['port'] : '3306',
            'dbname' => !empty($info['path']) ? ltrim($info['path'], '/') : '',
            'charset'  => isset($info['fragment']) ? $info['fragment'] : 'utf8',
        ];
    
        if (isset($info['query'])) {
            parse_str($info['query'], $dsn['params']);
        } else {
            $dsn['params'] = [];
        }
        return $dsn;
    }
    
    
    private function like(){
        if(isset($this->dbh) && !empty($this->dbh)){
            return $this->dbh;
        }
        try {
            $this->dbh = self::$tempLike = new \PDO($this->dns,$this->db['username'],$this->db['password'],[\PDO::ATTR_PERSISTENT => $this->mysql['persistenet']]);
            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            unset($this->connectStr,$this->config,$this->dns,$this->mysql['clusters']);
            
            return $this->dbh;
        }catch (\PDOException $e){
            die ("Error!:Unable to connect " . $e->getMessage() . "<br/>");
        }
    }                                                        

    private static function pdo_ping($dbh){
        try{
            $dbh->getAttribute(\PDO::ATTR_SERVER_INFO);
        }catch(\PDOException $e){
            if(strpos($e->getMessage(), 'MySQL server has gone away') !== false){
                return false; 
            }
        }
        return true; 
    }

    
    
    public function __call($func,$args){
        return call_user_func_array(array(&$this->dbh, $func), $args);
    }
    
    
    
    public static function clear() {
        self::$instance = null;
    }
    

}