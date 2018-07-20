<?php
namespace database;




use lib\config;
class ActiveRecord extends BaseActiveRecord implements ActiveRecordInterface{
    
    
    public $_params;
    
    protected  $dbh;
    
    protected $select;
    
    private  $base = 'database\base';
    
    private $_sqlAR = [];
    
    private  $sqlval = [];
    
    protected $child;
    
    private  $arWhere;
    
    private  $pWhere;
    
    protected $_pstr;
    
    protected  static $Pre;
    
    protected $_pval;
    
    protected $isPretreatment = FALSE;
    
    private $isAr = FALSE;
    
    private $find = FALSE;
    
    private $sqlstr;
    
    
    /**
     * 
     * model::find([])->where([])->one();
     * $select,$boole = true
     * 
     * @ $boole  
     */
    public static  function find(){
       $self = new self();
       $self->child  =  self::lectionClass(get_called_class());
       $self->find = true;
       $self->BindSelect();
       $self->_sqlAR['select'] = count(func_get_args()) == 1?func_get_args()['0']:'*';
       $self->disposeSelectSQL();
       return $self; 
    }
    
    
    
    
    private  function BindSelect(){
        $this->setDB();
    }
    
    
    private function setDB(){
        $mysql = config::reload('database')->Get('mysql',"separation");
        if($mysql){
          $database = $this->find?'vice-':'master';
        }else{
          $database = 'master';
        }
        $this->dbh = self::lectionClass($this->base,$database);
    }
    
    
    
    private function disposeSelectSQL(){
         $this->select .= 'SELECT '.
         (empty($this->_sqlAR['select'])?' * ':$this->_sqlAR['select'])
         .$this->ProcessingTable();
    }
    
    
    
    
    public function where(){
        if(!$args = func_get_args()) return $this;
        $getArg = empty($args['0'])?false:1;
        if(empty($getArg)){
            return $this;
        }
        return $this->disposeWhereParams(func_get_arg(0));
    }
    
    
    
    
    private function ProcessingTable(){
        return   ' FROM '.$this->GetTable();
    }
    
    
    /**
     * @name Get Database table name;
     * 
     */
    public function GetTable(){
       if(!$this->dbh){
           $this->setDB();
       }
       if(!$this->child) $this->child  =  self::lectionClass(get_called_class());
       if(isset($this->child->table))
       return  $this->dbh->dh->db['prefix'].$this->child->table;
    }
    
    /**
    * pwhere
    * example:pwhere(['id'=>'100','name'=>'ssc'])
    * example:pwhere('<',['id'=>'100','name'=>'ssc'])
    * example:pwhere(">",['id'=>'100','name'=>'ssc'],'and')
    * @return:
    */
    public function pwhere(){
        $this->find = true;
        $args = func_get_args();
        switch (func_num_args()){
            case 1 :
                $this->disposePrepare($args['0']);
                break;
            case 2 :
                $this->disposePrepare($args['1'],$args['0']);
                break;
            case 3 :
                $this->disposePrepare($args['1'],$args['0'],$args['2']);
                break;
        }
        return $this;
    }
    
    
    /**
     * @name like
     * example:like('field','name',a);
     * left = l || right = r || all = a
     */
    public function likeWhere($field,$key,$around = 'a'){
        $this->find = true;
        switch ($around){
            case 'a':
                $this->arWhere .= " `$field` lik(%$key%)"; break; 
            case 'r':
                $this->arWhere .= " `$field` lik(%$key)"; break; 
            case 'l':
                $this->arWhere .= " `$field` lik($key%)"; break; 
        }
        return $this;
    }
    
    /**
     * @name limit
     * @param 开始数字 $m
     * @param offset $n
     */
    public function limit($m , $n = 0){
        $this->arWhere .= " limit $m,$n";
        return $this;
    }
    
    /**
     * orderBy("name desc');
     * @param  $firldsort
     */
    
    public function orderBy($firldsort){
        $this->arWhere .= " ORDER BY $firldsort ";
        return $this;
    }
    
    /**
     * groupBy('id')
     * 
     */
    public function groupBy($by){
        $this->arWhere .= " GROUP BY $by";
        return $this;
    }
    
    
    /**
     * example:inwhere('filed',['q','b'])
     * 
     */
    public function inWhere(){
       $this->find = true;
       if(2 == func_num_args()){
            $change = function ($str) {
               return sprintf("'%s'", $str);
            };
            $filed = func_get_arg(0);
            $getArgs = func_get_arg(1);
            if(is_array($getArgs)){
              $indata = implode(",",array_map($change, $getArgs));
              $sql = "`$filed` in ({$indata})";
            }else{
              $sql = "`$filed` in ({$getArgs})";
            }
            $this->arWhere .= $sql;
       }
       return $this;
    }
    
    
    
    //'',[];
    //@todo 预处理语句处理 
    private function disposePrepare($params,$operator = ' = ',$bit = ' AND '){
        $this->find = true;
        if(is_array($params)){
            $Prepare  = '';
            $prepareArray = [];
            foreach ($params as $field => $pval){
                if($pval ==  end($params)){
                    $Prepare .= $field .' '. $operator .' :'.$field;
                }else{
                    $Prepare .= $field .' '. $operator .' :'.$field.$this->judgeCount($params," $bit ");
                }
                $prepareArray[":".$field] = $pval;
             }
             $this->isPretreatment = true;
             $this->_pstr .= empty($this->_pstr)?
             $Prepare:
             $bit.$Prepare;
             $this->_pval =  empty($this->_pval)?
             $prepareArray:
             array_merge($this->_pval,$prepareArray);
        }
    }
    
    
   public function inster(){
        return $this->Add($this->_ar);
    }
    
    
    /**
     * @name 添加
     * @param ['name'=>'yang']  $data
     */
    
    public  function Add($data){
        $sql = $this->dealInsertSQL($this->GetTable(),$data);
        $phl = $this->dbh->prepare($sql);
        $phl->execute($this->_ar);
        $this->initialize();
        return $this->ResultId();
    }
    
    /**
     * @example 
     * @param ['filde'=>100,'index'=>100]  $data
     * @param ['filde'=>100,'index'=>100]  $where
     */
    
    public  function Update($data,$where,$p = true){
       if($p){
           return $this->preparedUpdate($data,$where);
       }
       return $this->NotPreparedUpdate($data,$where);
    }
    
    
    
    //prepared Update func
    private function preparedUpdate($data,$where){
        $sqldata = empty($data)?$this->_ar:$data;
        $sql = $this->dealUpdateSQL($this->GetTable(),$sqldata,$where);
        $phl = $this->dbh->prepare($sql);
        $res = $phl->execute($sqldata);
        $this->initialize();
        return $res;
    }
    
    
    
    //not prepared Update func
    private function NotPreparedUpdate($data,$where){
        $sqldata = empty($data)?$this->_ar:$data;
        $sql = $this->dealUpdateSQL($this->GetTable(),$sqldata,$where,false);
        return $this->dbh->exec($sql);
    }
    
    
    
     //
     private function disposeWhereParams($params,$operator = ' = ',$bit = ' AND '){
         $this->find = true;
         if(!$this->dbh){
                $this->setDB();
                $this->disposeSelectSQL();
            }
            $sql = '';
            if(is_array($params)){
                foreach ($params as $name => $value){
                    if($value ==  end($params)){
                        $sql .= '`'.$name."` {$operator} ".$this->dbh->quote($value);
                    }else{
                        $sql .= '`'.$name."` {$operator} ".$this->dbh->quote($value).$this->judgeCount($params," $bit ");
                    }
                 }
                 $this->arWhere .= empty($this->arWhere)?$sql:$bit.$sql;
            }else{
                 $this->arWhere .= empty($this->arWhere)?$params:$bit.$params;
            }
            return $this;
        }
    
    
        
        
    public function one(){
        $this->isAr = true;
        if($this->isPretreatment){
            self::$Pre = $this->bindSql();
            return $this->dbh->pone($this->bindSql());
        }
        return $this->bindSql()->one();
        
    }
    
    
    
    public function all(){
        $this->isAr = true;
        if($this->isPretreatment){
            self::$Pre = $this->bindSql();
            return $this->dbh->pall(self::$Pre);
        }
        return $this->bindSql()->all();
    }
    
    
    
    
    public function ResultId(){
       return  $this->dbh->lastInsertId();
    }
    
    
    
    
    public function bindSql(){
       $sql = $this->sqlstr = $this->StringSql();
       if($this->isPretreatment){
           $phl = $this->dbh->prepare($sql);
           foreach ($this->_pval as $filed => $name){
               $phl->bindValue("$filed",$name);
           }
           $phl->execute();
           $this->initialize();
           return  $phl;
       }else{
           return $this->query($sql);
       }
      
   }
   
    
   
   private  function isPrams(){
        return get_called_class();
    }
   
    
    public static function lectionClass($class,$classargs = null){
       $class = new \ReflectionClass($class);
       $args = empty($classargs)?$class->newInstanceArgs():$class->newInstanceArgs([$classargs]);
       return $args;
    }
    
    
   private function GetChild($child){
       $this->child = $child;
   }
    
   /**
    * example:Count('id');
    * @param string defult *
    */
   
   public function Count($param = '*'){
       $this->resetSelet("count($param)")->disposeSelectSQL();
       return (int) $this->bindSql()->all()['0']["count($param)"];
   }
   
   
  public function  exist(){
      $sql = $this->StringSql();
      $existsSql = "exists (".$sql.")";
      $select = 'select '.$existsSql;
      return (bool) $this->query($select)->one()[$existsSql];
  }
   
   /**
    * example:top(50 PERCENT *) Take 50 percent
    * @param top  $param
    */
   
   public function top($param){
       $this->resetSelet("top $param")->disposeSelectSQL();
       return $this->bindSql()->all();
   }
   
   
   private function resetSelet($param){
       $this->select = '';
       $this->_sqlAR['select'] = $param;
       return $this;
   }
   
    //初始化
    private function initialize(){
        $this->sqlval= [];
        $this->arWhere = '';
        $this->pWhere = '';
        $this->_pval = '';
        $this->_pstr = '';
        $this->_ar = '';
    }
    
    
    //打印预处理sql
    public static function PrintParamsSQL(){
       return self::$Pre->debugDumpParams();
    }
    //打印sql
    public function PrintSQL(){
        return $this->sqlstr;
    }
    
    
    
    private function change_to_quotes($str) {
        return sprintf("'%s'", $str);
    }
     
    
    private function StringSql(){
       if($this->isPretreatment){
           return $this->select.' WHERE '.$this->arWhere.(!empty($this->arWhere)?' AND ':'').$this->_pstr;
       }else{
           return $this->select.(empty($this->arWhere)?'':' WHERE ').$this->arWhere;
       }
    }
    
    
    
    
    public function __call($func,$args){
        if(!$this->dbh){
            $this->setDB();
        }
        return call_user_func_array(array(&$this->dbh, $func), $args);
    }
    
    
    public function close(){
        return $this->dbh->closeCursor();
    }
    
    
}

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
