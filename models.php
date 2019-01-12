<?php
namespace database;
/**
 * This file is part of keenly from.
 * @brain_yang<qiaopi520@qq.com>
 * (c) brain_yang
 * github: https://github.com/keenlysoft/
 * @time 2018年8月27日
 * For the full copyright and license information, please view the LICENSE
 */
class models extends ActiveRecord implements \ArrayAccess{
    
    protected $_ar = [];
    
    private $arEdit = FALSE;
    
    private $arPri;
    
    const  PRI = 'id';
    
    public function __construct($pkg = null){
        if(!empty($pkg)){
            $this->getArData($pkg);
        }
    }
    
    
    public function __set($key,$value){
           $this->_ar[$key] = $value;
    }
    
    
    
    
    public function __get($key){
        if(isset($this->_ar[$key]))
        {
            return $this->_ar[$key];
        }
    }
    
    
    public function __isset($key)
    {
        return isset($this->_ar[$key]);
    }
    
    /**
     * @param offset
     */
    public function offsetExists ($offset) {
        return isset($this->$offset);
    }
    
    /**
     * @param offset
     */
    public function offsetGet ($offset) {
        return $offset;
    }
    
    /**
     * @param offset
     * @param value
     */
    public function offsetSet ($offset, $value) {
       $this->$offset = $value;
    }
    
    /**
     * @param offset
     */
    public function offsetUnset ($offset) {
        $this->$offset = null;
    }
    
    
    public function save(){
        if($this->arEdit){
            return $this->Update('',[self::PRI =>$this->arPri]);
        }
            return $this->inster();
    }
    
    /**
     * @NAME 计数器
     * @example user::UpdateCounter(['name'=>2],['id'=>2]);
     * @param $cont is datas
     * @param $condition where
     */
    public static function UpdateCounter($cont,$condition = ''){
        $command = self::lectionClass(get_called_class());
        foreach ($cont as $name => $value){
            $cont[$name] = "`$name`+$value";
        }
        return $command->Update($cont,$condition,false);
        
    }
    
    
    
    /**
     * @todo 
     * @name isTable user:find();new user();
     * 
     */
    private function isTable(){
        try {
            if($table = $this->GetTable()){
                $sql = 'SHOW COLUMNS FROM '.$table;
                return $res = $this->query($sql)->all();
            }
        }catch(\Exception $e){
            echo $e->getMessage();
        }
        return false;
    }
    
    
    public function getArData($id){
       $this->arPri = $id;
       $this->_ar = $this->where([self::PRI=>$id])->one();
       $this->arEdit = true;
       return $this->_ar;
    }
    

    
    
    
}