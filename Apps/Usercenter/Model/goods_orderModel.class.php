<?php
namespace Usercenter\Model;
use Think\Model;
use Usercenter\Model\userModel;
class goods_orderModel extends Model {
	/**
	 * 修改账单状态
	 * 
	 * @param $goodsId 账单Id
	 * @param $Type 类型
	 * 			 1.发货 2. 收货
	 * @return array
	 * @author LongG
	 */
	public function update( $orderId, $Type=1 ){
		$Type= (int)$Type;
		switch ($Type){
			case 1:
				$wherearr = array(
						'Id' => $orderId,
						'SellerId' => cookie('_uid'),
						'Status' => 10
				);
				$where = array( 'Status' => 21 );
				break;
			case 2:
				$wherearr = array(
						'Id' => $orderId,
						'BuyerId' => cookie('_uid'),
						'Status' => 21
				);
				$where = array( 'Status' => 22 );
				break;
			default:
				return "";
				break;
		}
		$c = $this -> where(array('Id' => $orderId)) -> find();
		handleEXP($c['SellerId'],4);
		handleEXP($c['BuyerId'],5);
		$m = M();
		$m->startTrans();
		$rst = $this->where( $wherearr )->save( $where );
		if ($Type == 2) {
			if ($c) {
				if ($c['TradeWay'] == 1) {
					$model = new userModel();
					// 卖家的积分加
					$sellmsg = $model->where(array('Id' => $c['SellerId']))->setInc('E-Money',$c['Price']); 
				}else{
					$sellmsg=true;
				}
			}
		}else{
			$sellmsg = true;
		}
		if ($rst && $sellmsg) {
			$m -> commit();
			return array (
					'status' => 1,
					'msg' => "操作成功" 
			);
		}else{
			$m -> rollback();
			return array (
					'status' => 0,
					'msg' => "操作失败" 
			);
		}
	}

	/**
	 * 评价 操作
	 * @param $oid 账单Id,
	 * @param $Star star 数
	 * @return array
	 * @author LongG
	 */
	public function savestar( $oid, $Star ){
		$msg = $this->where( array( 'Id' =>$oid ) )-> find();
		$uid = cookie('_uid');
		if (!$msg ) {
			return array (
					'status' => 0,
					'msg' => "评价失败" 
			);
		}
		if ($msg['BuyerId']==$uid) {
			$wherearr = array( 'SellerStar' => $Star, 'IsBuyEvaluate' => 1 );
		} else {
			$wherearr = array( 'BuyerStar' => $Star, 'IsSellEvaluate' => 1 );
		}
		$rst = $this->where( array( 'Id' => $oid ) )->data( $wherearr ) -> save();
		if (!$rst) {
			return array (
					'status' => 0,
					'msg' => "评价失败" 
			);
		} else {
			return array (
					'status' => 1,
					'msg' => "评价成功" 
			);
		}
	}

	/**
	 * 取消交易 操作
	 * @param $OId 账单Id,
	 * @param $Star star 数
	 * @return array
	 * @author LongG
	 */
	public function cancelorder( $oid ){
		$uid = cookie('_uid');
		$msg = $this->where( array( 'Id' =>$oid, 'BuyerId' =>$uid, 'Status' => 10 ) )-> find();
		if (!$msg ) {
			return array (
					'status' => 0,
					'msg' => "取消失败" 
			);
		}
		$m = M();
		$m->startTrans();
		$model = new userModel();
		/* 线上交易 还回买家E-money */
		if ($msg['TradeWay'] == 1) {
			$saveMoney = $model->where(array('Id'=>$msg['BuyerId']))->setInc('E-Money',$msg['Price']);
		}else{
			$savestar = true;
		}
		/* 减少信誉度 */
		$credit = $model -> updatecredit($uid, 1, 2);
		$rst = $this -> where( array( 'Id' => $oid ) ) -> delete();
		if ($savestar && $credit && $rst) {
			$m -> commit();
			return array (
					'status' => 1,
					'msg' => "操作成功" 
			);
		}else{
			$m -> rollback();
			return array (
					'status' => 0,
					'msg' => "操作失败" 
			);
		}
	}
}