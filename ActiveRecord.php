<?php
namespace database;

/**
 * This file is part of keenly from.
 * 
 * @author brain_yang<qiaopi520@qq.com>
 * (c) brain_yang
 * github: https://github.com/keenlysoft/
 * @time 2018年1月27日
 * For the full copyright and license information, please view the LICENSE
 */
use keenly\config;
use keenly\base\Singleton;

class ActiveRecord extends BaseActiveRecord implements ActiveRecordInterface
{
    
    use Singleton;

    public $_params;

    public $pagination;

    protected $dbh;

    protected $select;

    private static $initDB = FALSE;

    private static $base = 'database\base';

    private static $SetDB = null;

    private $_sqlAR = [];

    private $sqlval = [];

    private $child;

    private $arWhere;

    private $pWhere;

    protected $_pstr;

    protected static $Pre;

    protected $_pval;

    protected $isPretreatment = FALSE;

    private $isAr = FALSE;

    private $find = FALSE;

    private $sqlstr;

    public function init()
    {
        $this->select = '';
        $this->arWhere = '';
        $this->pWhere = '';
        $this->sqlstr = '';
        $this->_pstr = '';
        $this->_pval = [];
        $this->isPretreatment = false;
        $this->isAr = false;
        $this->find = false;
    }

    /**
     * model::find([])->where([])->one();
     * $select,$boole = true
     *
     * @ $boole
     */
    public static function find()
    {
        $self = self::I();
        $self->init();
        $self->child = self::lectionClass(get_called_class());
        $self->find = true;
        $self->BindSelect();
        $self->_sqlAR['select'] = count(func_get_args()) == 1 ? func_get_args()['0'] : '*';
        $self->disposeSelectSQL();
        return $self;
    }

    // 绑定 select 设置数据库
    private function BindSelect()
    {
        $this->setDB();
    }

    private function setDB()
    {
        if ($this->DbInstanceExist()) {
            return $this;
        }
        $mysql = config::reload('database')->Get('mysql', "separation");
        if ($mysql && ! self::$SetDB) {
            self::$SetDB = $this->find ? 'vice-' : 'master';
        } elseif (! self::$SetDB) {
            self::$SetDB = 'master';
        }
        if (! isset($this->dbh) && empty($this->dbh)) {
            if (self::$initDB) {
                $this->dbh = self::lectionClass(self::$base, self::$SetDB, self::$initDB);
            } else {
                $this->dbh = self::lectionClass(self::$base, self::$SetDB);
            }
        }
        return $this;
    }

    // 切换数据库
    public static function SwitchDB($database)
    {
        return self::$SetDB = $database;
    }

    // 初始化
    public static function InitDB()
    {
        self::$Switch = self::$SetDB = null;
        self::$initDB = true;
    }

    private function disposeSelectSQL()
    {
        $this->select .= 'SELECT ' . (empty($this->_sqlAR['select']) ? ' * ' : $this->_sqlAR['select']) . $this->ProcessingTable();
    }

    public function where()
    {
        if (! $args = func_get_args())
            return $this;
        $getArg = empty($args['0']) ? false : 1;
        if (empty($getArg)) {
            return $this;
        }
        return $this->disposeWhereParams(func_get_arg(0));
    }

    private function ProcessingTable()
    {
        return ' FROM ' . $this->GetTable();
    }

    /**
     *
     * @name Get Database table name;
     *      
     */
    public function GetTable()
    {
        if (! $this->dbh) {
            $this->setDB();
        }
        if (! $this->child)
            $this->child = self::lectionClass(get_called_class());
        if (isset($this->child->table))
            return $this->dbh->dh->db['prefix'] . $this->child->table;
    }

    /**
     * pwhere
     * example:pwhere(['id'=>'100','name'=>'ssc'])
     * example:pwhere('<',['id'=>'100','name'=>'ssc'])
     * example:pwhere(">",['id'=>'100','name'=>'ssc'],'and')
     * 
     * @return :
     */
    public function pwhere()
    {
        $this->find = true;
        $args = func_get_args();
        switch (func_num_args()) {
            case 1:
                $this->disposePrepare($args['0']);
                break;
            case 2:
                $this->disposePrepare($args['1'], $args['0']);
                break;
            case 3:
                $this->disposePrepare($args['1'], $args['0'], $args['2']);
                break;
        }
        return $this;
    }

    /**
     *
     * @name like
     *       example:like('field','name',a);
     *       left = l || right = r || all = a
     */
    public function likeWhere($field, $key, $around = 'a')
    {
        if ($this->arWhere) {
            $this->arWhere = $this->arWhere . ' and ';
        }
        $this->find = true;
        $field = $this->quoteIdentifier($field);
        $key = $this->quoteLikeValue($key);
        switch ($around) {
            case 'a':
                $this->arWhere .= " $field like('%$key%')";
                break;
            case 'r':
                $this->arWhere .= " $field like('%$key')";
                break;
            case 'l':
                $this->arWhere .= " $field like('$key%')";
                break;
        }
        return $this;
    }

    /**
     *
     * @name limit
     * @param 开始数字 $m            
     * @param offset $n            
     */
    public function limit($m, $n = 0)
    {
        $m = $this->normalizeNonNegativeInteger($m, 'limit');
        $n = $this->normalizeNonNegativeInteger($n, 'offset');
        if ($n == 0) {
            $this->arWhere .= " limit $m";
        } else {
            $this->arWhere .= " limit $m,$n";
        }
        return $this;
    }

    /**
     *
     * @name offset
     * @param 开始数字 $m            
     * @param offset $n            
     */
    public function offset($n)
    {
        $n = $this->normalizeNonNegativeInteger($n, 'offset');
        $this->arWhere .= " offset $n";
        return $this;
    }

    /**
     * orderBy("name desc');
     * 
     * @param
     *            $firldsort
     */
    public function orderBy($firldsort)
    {
        $this->arWhere .= " ORDER BY ".$this->formatOrderBy($firldsort)." ";
        return $this;
    }

    /**
     * groupBy('id')
     */
    public function groupBy($by)
    {
        $this->arWhere .= " GROUP BY ".$this->formatIdentifierList($by);
        return $this;
    }

    /**
     * example:inwhere('filed',['q','b'])
     */
    public function inWhere()
    {
        $this->find = true;
        if (2 == func_num_args()) {
            $change = function ($str) {
                return $this->quoteSqlLiteral($str);
            };
            $filed = func_get_arg(0);
            $filed = $this->quoteIdentifier($filed);
            $getArgs = func_get_arg(1);
            if (is_array($getArgs)) {
                $indata = implode(",", array_map($change, $getArgs));
                $sql = "$filed in ({$indata})";
            } else {
                $sql = "$filed in ({$getArgs})";
            }
            $this->arWhere .= $sql;
        }
        return $this;
    }

    // '',[];
    // @todo 预处理语句处理
    private function disposePrepare($params, $operator = ' = ', $bit = ' AND ')
    {
        $this->find = true;
        if (is_array($params)) {
            $Prepare = '';
            $prepareArray = [];
            foreach ($params as $field => $pval) {
                $quotedField = $this->quoteIdentifier($field);
                $placeholder = ':'.$this->placeholderName($field);
                if ($this->endkey($params) == $field) {
                    $Prepare .= $quotedField . ' ' . $operator . ' ' . $placeholder;
                } else {
                    $Prepare .= $quotedField . ' ' . $operator . ' ' . $placeholder . $this->judgeCount($params, " $bit ");
                }
                $prepareArray[$placeholder] = $pval;
            }
            $this->isPretreatment = true;
            $this->_pstr .= empty($this->_pstr) ? $Prepare : $bit . $Prepare;
            $this->_pval = empty($this->_pval) ? $prepareArray : array_merge($this->_pval, $prepareArray);
        }
        return $this;
    }

    public function inster()
    {
        return $this->Add($this->_ar);
    }

    /**
     *
     * @name 添加
     * @param ['name'=>'yang'] $data            
     */
    public function Add($data)
    {
        $sql = $this->dealInsertSQL($this->GetTable(), $data);
        $phl = $this->dbh->prepare($sql);
        $phl->execute($data);
        $this->initialize();
        return $this->ResultId();
    }

    /**
     *
     * @example
     *
     * @param ['filde'=>100,'index'=>100] $data            
     * @param ['filde'=>100,'index'=>100] $where            
     */
    public function Update($data, $where, $p = true)
    {
        if ($p) {
            return $this->preparedUpdate($data, $where);
        }
        return $this->NotPreparedUpdate($data, $where);
    }

    public function Delete($where)
    {
        return $this->DeleteExec($where);
    }

    // prepared Update func
    private function preparedUpdate($data, $where)
    {
        $sqldata = empty($data) ? $this->_ar : $data;
        $sql = $this->dealUpdateSQL($this->GetTable(), $sqldata, $where);
        $phl = $this->dbh->prepare($sql);
        $res = $phl->execute($sqldata);
        $this->initialize();
        return $res;
    }

    // not prepared Update func
    private function NotPreparedUpdate($data, $where)
    {
        $sqldata = empty($data) ? $this->_ar : $data;
        $sql = $this->dealUpdateSQL($this->GetTable(), $sqldata, $where, false);
        return $this->exec($sql);
    }

    // EXEC DELETE
    private function DeleteExec($where)
    {
        $sql = $this->dealDeleteSQL($this->GetTable(), $where);
        return $this->exec($sql);
    }

    private function disposeWhereParams($params, $operator = ' = ', $bit = ' AND ')
    {
        $this->find = true;
        if (! $this->dbh) {
            $this->setDB();
            $this->disposeSelectSQL();
        }
        $sql = '';
        if (is_array($params)) {
            foreach ($params as $name => $value) {
                if ($this->endkey($params) == $name) {
                    $sql .= $this->quoteIdentifier($name) . " {$operator} " . $this->dbh->quote($value);
                } else {
                    $sql .= $this->quoteIdentifier($name) . " {$operator} " . $this->dbh->quote($value) . $this->judgeCount($params, " $bit ");
                }
            }
            $this->arWhere .= empty($this->arWhere) ? $sql : $bit . $sql;
        } else {
            $this->arWhere .= empty($this->arWhere) ? $params : $bit . $params;
        }
        return $this;
    }

    /**
     * Grabbing all the data
     * 
     * @author brain_yang<qiaopi520@qq.com>
     *         (c) brain_yang
     *         github: https://github.com/keenlysoft/
     * @param
     *            true is defult || false is return sqlstr;
     *            @time 2018年2月27日
     *            For the full copyright and license information, please view the LICENSE
     */
    public function all($param = true)
    {
        $this->isAr = true;
        if ($this->isPretreatment) {
            self::$Pre = $this->bindSql();
            if (! $param)
                return self::$Pre->queryString;
            return $this->dbh->pall(self::$Pre);
        }
        $bind = $this->bindSql();
        if (! $param)
            return $this->PrintSQL();
        $this->initialize();
        return $bind->all();
    }

    /**
     * Grabbing One the Data
     * 
     * @author brain_yang <qiaopi520@qq.com>
     *         (c) brain_yang
     *         github: https://github.com/keenlysoft/
     * @param
     *            true is defult || false is return sqlstr;
     *            @time 2018年2月27日
     *            For the full copyright and license information, please view the LICENSE
     */
    public function one($param = true)
    {
        $this->isAr = true;
        if ($this->isPretreatment) {
            self::$Pre = $this->bindSql();
            if (! $param)
                return self::$Pre->queryString;
            return $this->dbh->pone($this->bindSql());
        }
        $bind = $this->bindSql();
        if (! $param)
            return $this->PrintSQL();
        $this->initialize();
        return $bind->one();
    }

    public function ResultId()
    {
        return $this->dbh->lastInsertId();
    }

    public function bindSql()
    {
        $sql = $this->sqlstr = $this->StringSql();
        if ($this->isPretreatment) {
            $phl = $this->dbh->prepare($sql);
            foreach ($this->_pval as $filed => $name) {
                $phl->bindValue("$filed", $name);
            }
            $phl->execute();
            return $phl;
        } else {
            $this->initialize();
            return $this->query($sql);
        }
    }

    private function isPrams()
    {
        return get_called_class();
    }

    public static function lectionClass($class, $classargs = null, $initdb = false)
    {
        if (empty($class))
            return false;
        $class = new \ReflectionClass($class);
        $args = empty($classargs) ? $class->newInstanceArgs() : $class->newInstanceArgs([
            $classargs,
            $initdb
        ]);
        return $args;
    }

    private function GetChild($child)
    {
        $this->child = $child;
    }

    /**
     * example:Count('id');
     * 
     * @param
     *            string defult *
     */
    public function Count($param = '*')
    {
        $select = "select count($param)" . $this->ProcessingTable();
        $sql = $this->StringSql($select);
        return (int) $this->query($sql)->one()["count($param)"];
    }

    /**
     * example:exist();
     * return bool
     */
    public function exist()
    {
        $sql = $this->StringSql();
        $existsSql = "exists (" . $sql . ")";
        $select = 'select ' . $existsSql;
        $this->initialize();
        return (bool) $this->query($select)->one()[$existsSql];
    }

    /**
     * example:top(50 PERCENT *) Take 50 percent
     * 
     * @param top $param            
     */
    public function top($param)
    {
        $this->resetSelet("top $param")->disposeSelectSQL();
        $this->initialize();
        return $this->bindSql()->all();
    }

    private function resetSelet($param)
    {
        $this->select = '';
        $this->_sqlAR['select'] = $param;
        return $this;
    }

    // 初始化
    private function initialize()
    {
        $this->_sqlAR['select'] = [];
        $this->sqlval = [];
        $this->arWhere = '';
        $this->pWhere = '';
        $this->_pval = '';
        $this->_pstr = '';
        $this->_ar = '';
        $this->select = '';
        $this->isPretreatment = false;
        $this->isAr = false;
        $this->find = false;
    }

    // 打印预处理sql
    public static function PrintParamsSQL()
    {
        return self::$Pre->debugDumpParams();
    }

    // 打印sql
    private function PrintSQL()
    {
        return $this->sqlstr;
    }

    private function change_to_quotes($str)
    {
        return sprintf("'%s'", $str);
    }

    private function StringSql($select = null)
    {
        if ($this->isPretreatment) {
            return (isset($select) ? $select : $this->select) . ' WHERE ' . $this->arWhere . (! empty($this->arWhere) ? ' AND ' : '') . $this->_pstr;
        } else {
            if (isset($select)) {
                return $select . (empty($this->arWhere) ? '' : ' WHERE ') . $this->arWhere;
            } else {
                return $this->select . (empty($this->arWhere) ? '' : ' WHERE ') . $this->arWhere;
            }
        }
    }

    private function quoteLikeValue($value)
    {
        return str_replace(["\\", "'", "%", "_"], ["\\\\", "''", "\\%", "\\_"], (string) $value);
    }

    private function normalizeNonNegativeInteger($value, $name)
    {
        if (!is_int($value) && !ctype_digit((string) $value)) {
            throw new \InvalidArgumentException("Invalid SQL $name value.");
        }
        $value = (int) $value;
        if ($value < 0) {
            throw new \InvalidArgumentException("Invalid SQL $name value.");
        }
        return $value;
    }

    private function formatOrderBy($orderBy)
    {
        $parts = array_map('trim', explode(',', (string) $orderBy));
        $formatted = [];
        foreach ($parts as $part) {
            if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)(?:\s+(ASC|DESC))?$/i', $part, $matches)) {
                throw new \InvalidArgumentException('Invalid SQL order by clause.');
            }
            $direction = isset($matches[2]) ? ' '.strtoupper($matches[2]) : '';
            $formatted[] = $this->quoteIdentifier($matches[1]).$direction;
        }
        return implode(', ', $formatted);
    }

    private function formatIdentifierList($fields)
    {
        $parts = array_map('trim', explode(',', (string) $fields));
        return implode(', ', array_map(function ($field) {
            return $this->quoteIdentifier($field);
        }, $parts));
    }

    public function __call($func, $args)
    {
        if (! $this->DbInstanceExist()) {
            $this->setDB();
        }
        return call_user_func_array(array(
            &$this->dbh,
            $func
        ), $args);
    }

    public function close()
    {
        return $this->dbh->closeCursor();
    }

    /**
     *
     * @name 判断实例是否存在
     * @return boolean
     */
    protected function DbInstanceExist()
    {
        if (self::$initDB) {
            $this->dbh = null;
        }
        return isset($this->dbh) && ! empty($this->dbh) ? true : false;
    }

    /**
     *
     * @name 执行 update delete
     * @param
     *            sql
     * @author : brain
     *         @time 2019年1月8日 下午2:27:06
     */
    public function exec($sql)
    {
        if (! $this->dbh) {
            $this->setDB();
        }
        $this->sqlstr = $sql;
        return $this->dbh->exec($sql);
    }

    /**
     *
     * @name 执行 select
     * @param
     *            sql
     * @author : brain
     *         @time 2019年1月8日 下午2:27:06
     */
    public function query($sql)
    {
        if (! $this->dbh) {
            $this->setDB();
        }
        $this->sqlstr = $sql;
        return $this->dbh->query($sql);
    }
}

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
