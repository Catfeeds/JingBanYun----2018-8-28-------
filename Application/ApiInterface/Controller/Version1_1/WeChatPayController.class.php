<?php
namespace ApiInterface\Controller\Version1_1;
use Think\Controller;
use Common\Common\SMS;

class WeChatPayController extends PublicController{
	public function retrieval(){
		$this->display();
	}
	public function orderConfirm(){
		$this->display();
	}
	public function orderPay(){
		$this->display();
	}
	public function orderPaySuccess(){
		$this->display();
	}
}
