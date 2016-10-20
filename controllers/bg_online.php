<?php
/**
 * @brief 在线浏览统计
 * @class BG_Online
 * @note  后台
 */
class BG_Online extends IController
{
	public $layout='admin';
	//public $checkRight = array('check' => 'all','uncheck' => array('seo_sitemaps'));

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}

}
