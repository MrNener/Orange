<?php

namespace Home\Controller;

use Home\Model\goodsModel;
use Usercenter\Model\user_addressModel;
use Usercenter\Model\userModel;
use Home\Model\goods_orderModel;

/**
 * 订单处理控制器
 *
 * @author NENER
 *        
 */
class OrderController extends LoginController {
	public function _initialize() {
		parent::_initialize ();
		if (! isactivated ()) {
			redirect ( U ( '/u/act' ) );
			die ();
		}
	}
	/**
	 * 渲染一个商品订单
	 */
	public function index() {
		$Id = I ( 'Id' );
		if (! IS_POST || ! $Id) {
			$this->error ( '页面不存在', U ( '/' ) );
			die ();
		}
		$m = new goodsModel ();
		
		/* 获得商品模型 */
		$goods = $m->findone ( $Id );
		if (! $goods) {
			$this->error ( '商品不存在或已下架', U ( 'Home/Index/index' ) );
			die ();
		}
		if((int)$goods['UserId']==(int)cookie('_uid')){
			$this->error ( '你不能购买自己出售的商品', U ( 'Home/Index/index' ) );
			return false;
		}
		/* 订单code */
		$arrcode = array (
				'code' => date ( 'YmdHis' ) . $goods ['Id'],
				'time' => time () 
		);
		$m = new user_addressModel ();
		$okey = createonekey ( time () . 'Oranger_Order' );
		if (cookie ( '_okey' )) {
			session ( cookie ( '_okey' ), NULL );
		}
		cookie ( '_okey', $okey );
		session ( $okey, C ( 'GOODS_ORDER_SESSION_VALUE' ) );
		/* 获得地址 */
		$alist = $m->getall ( cookie ( '_uid' ) );
		/* 获得可用的交易方式 */
		$waylist = createtradeway ( $goods ['TradeWay'] );
		$this->assign ( 'ordermodel', $arrcode );
		$this->assign ( 'goods', $goods );
		$this->assign ( 'tradewaylist', $waylist );
		$this->assign ( 'useraddress', $alist );
		$this->display ();
	}
	/**
	 * 确认订单
	 */
	public function createorder() {
		if (! IS_POST) {
			$this->error ( '页面不存在', U ( '/' ) );
			die ();
		}
		$okey = cookie ( '_okey' );
		if (! $okey || ! session ( $okey ) || ! (session ( $okey ) == C ( 'GOODS_ORDER_SESSION_VALUE' ))) {
			$this->error ( '订单已过期', U ( '/' ) );
			die ();
		} else {
			session ( $okey, null );
			cookie ( '_okey', null );
		}
		$arr = I ( 'post.' );
		$m = new goods_orderModel ();
		$rst = $m->createone ( $arr );
		if (( int ) $rst ['status'] == 0) {
			$this->error ( $rst ['msg'], U ( '/' ) );
			die ();
		} else {
			$this->assign ( 'omodel', $rst );
			logs ( '购买成功 ID' . $arr ['GoodsId'], 3 );
			$this->display ();
		}
	}
	
	/**
	 * ajax获得余额
	 */
	public function getbalance() {
		if (! IS_POST) {
			$this->error ( '页面不存在' );
			die ();
		}
		$m = new userModel ();
		$b = $m->getbalance ( cookie ( '_uid' ), 2 );
		$this->success ( $b );
	}
	/**
	 * 校验支付密码
	 */
	public function checkpaypwd(){
		if(!IS_POST){
			$this->error('不要瞎搞',U('/'));
			return ;
		}
		$p=I('paypassword');
		$m=new userModel();
		$r=$m->checkpaypwd($p);
		if((int)$r['status']==1){
			$this->success(1);
		}else{
			$this->error($r['msg']);
		}
	}
}