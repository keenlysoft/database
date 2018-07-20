<?php


namespace database;


class Expression 
{
   
    public $expression;
   
    public $params = [];
    
    public function __construct($expression,$params)
    {
        $this->expression = $expression;
        $this->params = $params;
    }

    /**
     * String magic method
     * @return string the DB expression
     */
    public function __toString()
    {
        return $this->expression;
    }
}
