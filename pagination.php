<?php
namespace database;
/**
 * This file is part of keenly from.
 * @author brain_yang<qiaopi520@qq.com>
 * (c) brain_yang
 * github: https://github.com/keenlysoft/
 * @time 2018年10月27日
 * For the full copyright and license information, please view the LICENSE
 */
use keenly;
class pagination{
    
    
    private $count;
    
    private $total;
    
    private $page;
    
    private $number;
    
    private $totalPage;
    
    public  $limit;
    
    public  $offset;
    
    public function __construct($total,$page = 1,$number = 10){
        
        $this->total = $total;
        $this->page  = isset($page)?$page:1;
        $this->number = $number;
        $this->calculate();
    }
    
    
    
    function page(){
        $html = "<div>";
        $html .= "<a class='prev' href='{$this->previous()}'>&lt;&lt;</a>";
        $toPage = $this->toPage();
        for ($i = $toPage['min'];$i <= $toPage['max'];$i++)
        {
            $url = $this->preg_url($i);
            if($this->page == $i){
                $html .='<span class="current">'.$i.'</span>';
            }else{
                $html .="<a class='num' href='$url'>$i</a>";
            }
        }
        if(($i-1) != $this->totalPage){
            $html .="<a class='num' title= '总页码'  href='{$this->totalPage()}'>{$this->totalPage}</a>";
        }
        $html .= "<a class='next' href='{$this->next()}' >&gt;&gt;</a> ";
        $html .= "</div>";
        return $html;
    }
    
    
    
    function calculate(){
        $this->totalPage = ceil(bcdiv($this->total, $this->number,1));
        if($this->totalPage <= 0 || $this->page <= 1){
            $this->limit = $this->number;
            $this->offset = 0;
        }else{
            $this->limit = $this->number;
            $this->offset = bcmul(($this->page>1?($this->page-1):$this->page), $this->number);
        }
    }
    
    
    private function preg_url($i){
        $replace = preg_replace("/page=([0-9]+)/","page={$i}",\keenly::$box->url->web);
        if(!strpos($replace,'?')){
           return $replace.'?page='.$i;
        }elseif (!strpos($replace,'page')){
            return $replace.'&page='.$i;
        }else{
            return $replace;
        }
    }
    
    
    private function previous(){
        $previous = ($this->page-1)<1?1:($this->page-1);
        return $this->preg_url($previous);
    }
    
    
    
    private function next(){
        $next = ($this->page+1) >= $this->totalPage?$this->totalPage:($this->page+1);
        return $this->preg_url($next);
    }
    
    
    
    private function totalPage(){
        return $this->preg_url($this->totalPage);
    }
    
    
    
    private function toPage(){
        $minPage = bcsub($this->page,3)<1?1:bcsub($this->page,3);
        $maxPage = bcadd($this->page,6) >= $this->totalPage?$this->totalPage:bcadd($this->page,6);
        return ['min'=>$minPage,'max'=>$maxPage];
    }
    
}