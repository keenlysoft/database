<?php
namespace database;


use keenly\Exception\dbException;
use database\mysql\pdoBuilder;

class base  extends abstractPdo {
    
    
    public   $sqlStr;
    
    private  $connection = 'connectionSql';
    
    private  $database =  'database\mysql\pdoBuilder';
   
    private  $adodb = false;
    
    public   $dh;
    
    protected    $ilink = false;
    
    
    public function __construct($adodb = null,$initdb = FALSE){
        $this->adodb = is_string($adodb)?$adodb:false;
        $this->dh = $this->init($initdb);
    }
    
    
    
    public function init($initdb = FALSE){
        return method_exists($this->database,$this->connection)?
        call_user_func_array(array($this->database,$this->connection),[$this->adodb,$initdb]):
        '';
    }
    
    
    
    public function exce($sql){  
       $this->sqlStr = $sql;
       return $this->dh->exec($sql); 
    }
    
    
    
    public function query($sql){
        try {
            $this->sqlStr = $sql;
            $this->ilink = $this->dh->query($sql);
            return $this;
        }catch (\PDOException $e){
            var_export($sql);
            throw new dbException($e,$e->getCode());
        }
    }
    
    public function quote($string){
        return $this->dh->quote($string);
    }
    
    
    
    public function execute(){
        
        return $this->dh->execute();
    }
    
    
    public function begin(){
        return $this->dh->beginTransaction();
    }
    
    //检查是否在一个事务内
    public function InTransaction(){
        return $this->dh->InTransaction();
    } 
   
    
    
    public function commit(){
        return $this->dh->commit();
    }
    
    
    
    
    public function back(){
        return $this->dh->rollBack();
    }
    
    //关闭游标
    public function closeCursor(){
        return $this->ilink->closeCursor();
    }
    

    //单条结果
    public function one()
    { 
        if ($this->ilink) {
            $res = $this->ilink->fetch(\PDO::FETCH_ASSOC);
            $this->closeCursor();
            return $res;
        }
        
    }
    
    //多条结果
    public function all(){
        if ($this->ilink) {
            $res = $this->ilink->fetchAll(\PDO::FETCH_ASSOC);
            $this->closeCursor();
            return $res;
        }
    }
    
    
    //
    public function _isStrType($var){
        if(is_int($var))
            return 1;
        return 2;
    }
    
    
    
    public function Pall($class){
        $class->setFetchMode(\PDO::FETCH_ASSOC);
        return $class->fetchAll();
    }
    
    
    
    public function Pone($class){
        $class->setFetchMode(\PDO::FETCH_ASSOC);
        return $class->fetch();
    }
    
    public function __call($func,$args){
        return call_user_func_array(array(&$this->dh, $func), $args);
    }
    
    
    
    
    
    
    
}