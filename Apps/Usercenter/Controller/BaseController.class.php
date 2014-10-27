<?php

namespace Usercenter\Controller;

use Think\Controller;
use Usercenter\Model\userModel;

/**
 * 基础控制器
 *
 * @author NENER
 *        
 */
class BaseController extends Controller {
	/**
	 * 检测登录
	 * @author NENER
	 */
	public function _initialize() {
		$model = new userModel ();
		$rst = $model->islogin ( false );
		if (! $rst) {
			redirect ( U ( 'Usercenter/User/index', array (
					'isadmin' => false 
			) ) );
		}
	}
}
?>