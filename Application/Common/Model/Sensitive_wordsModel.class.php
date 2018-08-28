<?php
namespace Common\Model;
use Think\Model;

class Sensitive_wordsModel extends Model{

    public    $model='';
    protected $tableName = 'sensitive_words';

    public function __construct(){
        parent::__construct();
        $this->model=M($this->tableName);
    }

    /*
     * 得到全部数据 
     */
    public function get_sensitive_words_all()
    { 
        $result = $this->model->field('id,sensitive_words')->select();
        return $result;
    }
    
    
    
    
    public function filter($content)
    {
        if ($content == "") return false;
        $data=$this->get_sensitive_words_all(); //var_dump($data);
        foreach($data as $value){               
            if(strstr($content,$value['sensitive_words'])!==false){
                return false;
            }
        }
        return true;
    }



}