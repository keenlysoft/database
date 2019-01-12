<?php

namespace database;

interface  ActiveRecordInterface{
    
    public static function find();
    
    public function where();
    
    public function pwhere();
    
    public function limit($m , $n);
    
}