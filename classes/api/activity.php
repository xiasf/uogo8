<?php
/**
 * @file Activity.php 
 * @brief 活动api方法 
 */
class APIActivity
{
	//公告列表
	public function getActivityList()
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$catid = IFilter::act(IReq::get('id'),'int');
		// $where = 'is_open = 0 and NOW() between start_time and end_time';
		$where = 'is_open = 0'; 
		if($catid>0){$where .= ' and cat_id = '.$catid;}
		$query = new IQuery('activity');
		$query->where = $where;
		$query->order = 'sort asc,id desc';
		$query->page  = $page;
		return $query;
	}
}