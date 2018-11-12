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
class BaseActiveRecord {
    
    
    public static $Switch;
    
    private   $_paramssql;
    
    private   $intStr;
    
    private   $valueStr;

    private   $arVal;
    
    private   $upSql;
    
    private   $setSql = ' SET ';
    
    public static function className()
    {
        return get_called_class();
    }
    
    private function Inseter($Table){
        $this->_paramssql = "insert into ".$Table.' ';
        return $this;
    }
   
    
    private function Updatesql($Table){
        $this->upSql = "UPDATE ".$Table.' ';
        return $this;
    }
    
    private function desoSql($ar){
        if(is_array($ar) && count($ar) >= 1){
           $key = array_keys($ar);
           $this->intStr = implode(",", array_map(function ($v){
               return "`$v`"; 
           }, $key));
           $this->valueStr = implode(",", array_map(function ($v){
                    return ':'.$v;
                    },$key
           ));
        }
    }
   
    //pupdate SQL
    private function desoUpSql($ar){
        if(is_array($ar) && count($ar) >= 1){     
            $key = array_keys($ar);
            foreach ($key as $filed){
                if($filed ==  end($key)){
                    $this->setSql .= $filed.' = :'.$filed;
                }else{
                    $this->setSql .= $filed.' = :'.$filed.$this->judgeCount($ar);
                }
            }
        }else{
            $this->setSql .= $ar;
        }
        return $this->upSql.$this->setSql;
    }
    
    
    //desoUpdateSql func is not Prepared statements
    private function desoUpdateSql($ar){
        if(is_array($ar) && count($ar) >= 1){
            foreach ($ar as $filed => $name){
                if($filed ==  end($ar)){
                    $this->setSql .= $filed.' = '.$name;
                }else{
                    $this->setSql .= $filed.' = '.$name.$this->judgeCount($ar);
                }
            }
        }else{
            $this->setSql .= $ar;
        }
        return $this->upSql.$this->setSql;
    }
    
    /**
     * @name  Prepared update deal
     * @param unknown $t
     * @param unknown $a
     * @param string $w is wherw 
     */
    public function dealUpdateSQL($t,$a,$w,$p = true){
        $Usql = $this->Updatesql($t);
        $Usql = !$p?$Usql->desoUpdateSql($a):$Usql->desoUpSql($a);
        $Usql .= $this->BindWhere($w);
        return $Usql;
    }
    
   
    
    //bind where
    private function BindWhere($w){
        if (isset($w) && is_array($w)){
            $where = '';
            foreach ($w as $f => $v){
                if($v ==  end($w)){
                    $where .= $f.' = '.$v;
                }else{
                    $where .= $f.' = '.$v.$this->judgeCount($w,' AND ');
                }
            }
            return ' WHERE '.$where;
        }
           return ' WHERE '.$w;
    }
    
    /**
     * @name deal insert sql
     * @param table $t
     * @param _ar $a
     */
    public function dealInsertSQL($t,$a){
        $this->Inseter($t)->desoSql($a);
        $this->_paramssql .= "($this->intStr)".' value '."({$this->valueStr})";
        return $this->_paramssql;
    }
   
    /**
     * Additional parameter operator 
     * @author brain_yang<qiaopi520@qq.com>
     * (c) brain_yang
     * github: https://github.com/keenlysoft/
     * @time 2018年2月27日
     * For the full copyright and license information, please view the LICENSE
     */
    public function judgeCount( $num , $seg = ' , '){
        return count($num) > 1 ? $seg : '';
    }
    
    
    
    
    
    
    
    
}