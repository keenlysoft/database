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
abstract class abstractPdo{
    
    
    abstract function one();
    
    
    abstract function all();
    
    
    abstract function exce($sql);
    
    
    abstract function query($sql);
    
    
    abstract function commit();
    
    
    abstract function back();
    
    
    abstract function begin();
    
    
    
}