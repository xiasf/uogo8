<?php
/**
 * @brief 商家控制器
 * @class SellerMain
 */
class SellerMain extends IController
{
	public $layout = '';


	/**
	 * @brief 初始化检查
	 */
	public function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}
	public function index(){
		
		$this->redirect('index');
	}

}