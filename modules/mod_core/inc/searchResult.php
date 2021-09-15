<?php 
namespace fox;

class searchResult {
    public $page=0;
    public $pages=0;
    public $result=[];
    protected $idx=1;
    
    public function setIndex($idx) {
        $this->idx = $idx;
    }
    
    public function push($val) {
        $this->result[$this->idx] = $val;
        $this->idx++;
    }
    
    public function __construct($idx=null) {
        if (isset($idx)) {
            $this->idx = $idx;
        }
    }
    
    public function setIndexByPage($page, $pagesize) {
        $this->idx = (($page-1)*$pagesize)+1;
    }
}

?>