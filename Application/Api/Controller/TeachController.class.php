<?php
namespace Api\Controller;
use Think\Controller;

class TeachController extends Controller
{ 
    public $model='';
    public $page_size=20;
    public $firstRow=0;
    public $listRow=0;
            
    public function __construct() {
        parent::__construct(); 
        $this->model=D('Biz_blackboard');
        header("Content-type: text/html; charset=utf-8");
    }
 
   
}