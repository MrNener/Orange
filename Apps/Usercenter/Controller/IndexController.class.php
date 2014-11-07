<?php

namespace Usercenter\Controller;

use Usercenter\Model\view_user_info_avatarModel;
use Usercenter\Model\view_goods_order_listModel;
use Usercenter\Model\user_addressModel;
use Usercenter\Model\attentionModel;
use Usercenter\Model\userModel;
use Home\Model\goods_listModel;
use Usercenter\Model\user_gradeModel;
use Home\Model\user_avatarModel;
use Usercenter\Model\user_homeModel;
use Usercenter\Controller\BaseController;
use Think\Controller;

class IndexController extends BaseController {
	public function _initialize() {
		parent::_initialize ();
	}
	
	public function index(){
		$userid = cookie('_uid');
		$model = new view_user_info_avatarModel();
		$arr = $model->getinfo();
		if ($arr ['status'] == 1) {
			$this->assign ( 'user', $arr['msg'] );
			$this->display();
		} else {
			$this->error ( $arr ['msg'] );
		}
	}
	
	/**
	 * 查询用户信息
	 * Enter description here ...
	 */
	public function edit(){
		$userid = cookie('_uid');
		$model = new view_user_info_avatarModel();
		$arr = $model->getinfo();
		if ($arr ['status'] == 1) {
			$this->assign ( 'model', $arr['msg'] );
			$this->display();
		} else {
			$this->error ( $arr ['msg'] );
		}
	}
	
	/**
	 * 修改用户信息
	 * Enter description here ...
	 */
	public function updateUser(){
		$arr = I ( 'post.' );
		$arr['_uid'] = cookie('_uid');
		$model = new userModel();
		$rst = $model->updateUser($arr);
		if (( int )$rst['status'] == 1) {
			$this->success ( $rst ['msg'] );
		} else {
			$this->error ( $rst ['msg'] );
		}
	}
	
	/**
	 * 签到
	 */
	public function sign(){
		$model = new userModel();
		$rst = $model -> sign();
		if (( int ) $rst ['status'] == 0) {
			$this->error ( $rst ['msg'] );
		} else {
			$this->success ( 1 );
		}
	}
	
	/**
	 * 订单管理
	 */
	public function order(){
		$userid = cookie('_uid');
		$model = new view_goods_order_listModel();
		$arr = $model->selectAllBuy($userid);
		if ($arr ['status'] == 1) {
			$this->assign ( 'buy', $arr['msg'] );
			$this->display();
		} else {
			$this->error ( $arr ['msg'] );
		}
	}
	
	/**
	 * 已出售的商品
	 */
	public function sell(){
		$userid = cookie('_uid');
		$model = new view_goods_order_listModel();
		$arr = $model->selectAllSell($userid);
		if ($arr ['status'] == 1) {
			$this->assign ( 'sell', $arr['msg'] );
			$this->display();
		} else {
			$this->error ( $arr ['msg'] );
		}
	}
	
	/**
	 * 我的关注
	 */
	public function attention(){
		$userid = cookie('_uid');
		$model = new attentionModel();
		$arr = $model -> selectAttention($userid);
		if (( int ) $arr ['status'] == 0) {
			$this->error ( $arr ['msg'] );
		} else {
			$this->assign('attention',$arr['attention']);	
		}
		$this->display();
	}
	
	/**
	 * 我的心愿单
	 */
	public function favorite(){
		$userid= cookie('_uid');
		$model = new user_homeModel($userid);
		$favorite = $model->selectFavorite($userid);
		$this->assign('favorite',$favorite);
		$this->display ();
	}
}
?>