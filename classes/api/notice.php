<?php
/**
 * @file notice.php 
 * @brief 公告api方法 
 */
class APINotice
{
	//公告列表
	public function getNoticeList()
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('announcement');
		$query->order = 'id desc';
		$query->page  = $page;
		return $query;
	}
}