<?php
/**
 * @file notice.php
 * @brief 用户中心api方法
 */
class APIUcenter
{

	///用户中心-账户余额
	public function getUcenterAccoutLog($userid)
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('account_log');
		$query->where="user_id = ".$userid;
		$query->order = 'id desc';
		$query->page  = $page;
		return $query;
	}
	//用户中心-我的建议
	public function getUcenterSuggestion($userid)
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('suggestion');
		$query->where="user_id = ".$userid;
		$query->page  = $page;
		$query->order = 'id desc';
		return $query;
	}
	//用户中心-商品讨论
	public function getUcenterConsult($userid)
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('refer as r');
		$query->join   = "join goods as go on r.goods_id = go.id ";
		$query->where  = "r.user_id =". $userid;
		$query->fields = "time,name,question,status,answer,admin_id,go.id as gid";
		$query->page   = $page;
		$query->order = 'r.id desc';
		return $query;
	}
	//用户中心-商品评价
	public function getUcenterEvaluation($userid,$status = '')
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('comment as c');
		$query->join   = "left join goods as go on c.goods_id = go.id ";
		$query->where  = ($status === '') ? "c.user_id = ".$userid : "c.user_id = ".$userid." and c.status = ".$status;
		$query->fields = "go.name,c.*";
		$query->page   = $page;
		$query->order = 'c.id desc';
		return $query;
	}

	//用户中心-用户信息
	public function getMemberInfo($userid){
		$tb_member = new IModel('member m, user u');
		$info = $tb_member->getObj("m.user_id = u.id and m.user_id = " . $userid, 'm.*,u.username,u.head_ico');
		return $info;
	}
	//用户中心-个人主页统计
	public function getMemberTongJi($userid){
		$query = new IQuery('order');
		$query->fields = "sum(order_amount) as amount,count(id) as num";
		$query->where  = "user_id = ".$userid." and pay_status = 1 and if_del = 0";
		$info = $query->find();
		return $info[0];
	}
	//用户中心-个人主页统计
	public function getPropTongJi($propIds){
		$query = new IQuery('prop');
		$query->fields = "count(id) as prop_num";
		$query->where  = "id in (".$propIds.") and type = 0";
		$info = $query->find();
		return $info[0];
	}
	//用户中心-积分列表
	public function getUcenterPointLog($userid,$c_datetime)
	{
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('point_log');
		$query->where  = "user_id = ".$userid." and ".$c_datetime;
		$query->page   = $page;
		$query->order= "id desc";
		return $query;
	}
	//用户中心-信息列表
	public function getUcenterMessageList($msgIds){
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('message');
		$query->where= "id in(".$msgIds.")";
		$query->order= "id desc";
		$query->page = $page;
		return $query;
	}
	//用户中心-订单列表
	public function getOrderList($userid){
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$i = IFilter::act(IReq::get('i'),'int');

		$query = new IQuery('order');

		$where = "user_id =".$userid." and if_del= 0";

		if ($i == 1) { // 代发货
			$where .= ' and status = 2 and distribution_status = 0';
		} elseif($i == 2) { // 代付款
			$where .= ' and status = 1';
		} elseif($i == 4) { // 代收货
			$where .= ' and status = 2 and distribution_status = 1';
		} elseif($i == 5) { // 代评价
			$where .= '';
		} elseif($i == 6) { // 退款
			$where .= ' and status = 6';
		}
		$query->where = $where;
		$query->order = "id desc";
		$query->page  = $page;
		return $query;
	}
	//用户中心-我的代金券
	public function getPropList($ids){
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('prop');
		$query->where  = "id in(".$ids.") and is_send = 1";
		$query->page   = $page;
		return $query;
	}
	//用户中心-退款记录
	public function getRefundmentDocList($userid){
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('refundment_doc');
		$query->where = "user_id = ".$userid;
		$query->order = "id desc";
		$query->page  = $page;
		return $query;
	}
	//用户中心-提现记录
	public function getWithdrawList($userid){
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$query = new IQuery('withdraw');
		$query->where = "user_id = ".$userid." and is_del = 0";
		$query->order = "id desc";
		$query->page  = $page;
		return $query;
	}



}