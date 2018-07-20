<?php
namespace database;


abstract class abstractPdo{
    
    
    abstract function one();
    
    
    abstract function all();
    
    
    abstract function exce($sql);
    
    
    abstract function  query($sql);
    
    
    abstract function commit();
    
    
    abstract function back();
    
    
    abstract function begin();
    

    
}