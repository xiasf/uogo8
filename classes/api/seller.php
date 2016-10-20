<?php
/**
 * @file seller.php
 * @brief 商家API
 */
class APISeller
{
	//商户信息
	public function getSellerInfo($id)
	{
		$query = new IModel('seller');
		$info  = $query->getObj("id=".$id);
		return $info;
	}

	//获取商户列表
	public function getSellerList($district_id)
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('seller');
		$query->where = "district_id = '$district_id'" . ' and is_del = 0 and is_lock = 0 and is_vis = 1';
		$query->order = 'sort asc,id desc';
		$query->page  = $page;
		return $query;
	}
}