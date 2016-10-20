<?php
/**
 * @file Order_Class.php
 * @brief 订单中相关的
 * @version 0.6
 */
class Order_Class
{
	/**
	 * @brief 产生订单ID
	 * @return string 订单ID
	 */
	public static function createOrderNum()
	{
		return date('YmdHis').rand(100000,999999);
	}

	/**
	 * 添加评论商品的机会
	 * @param $order_id 订单ID
	 */
	public static function addGoodsCommentChange($order_id)
	{
		//获取订单对象
		$orderDB  = new IModel('order');
		$orderRow = $orderDB->getObj('id = '.$order_id);

		//获取此订单中的商品种类
		$orderGoodsDB        = new IQuery('order_goods');
		$orderGoodsDB->where = 'order_id = '.$order_id;
		$orderGoodsDB->group = 'goods_id';
		$orderList           = $orderGoodsDB->find();

		//可以允许进行商品评论
		$commentDB = new IModel('comment');
		$goodsDB   = new IModel('goods');

		//对每类商品进行评论开启
		foreach($orderList as $val)
		{
			$issetGoods = $goodsDB->getObj('id = '.$val['goods_id']);
			if($issetGoods)
			{
				$attr = array(
					'goods_id' => $val['goods_id'],
					'order_id' => $order_id,
					'order_no' => $orderRow['order_no'],
					'user_id'  => $orderRow['user_id'],
					'time'     => date('Y-m-d H:i:s')
				);
				$commentDB->setData($attr);
				$commentDB->add();
			}
		}
	}

	/**
	 * 支付成功后修改订单状态
	 * @param $orderNo  string 订单编号
	 * @param $admin_id int    管理员ID
	 * @param $note     string 收款的备注
	 * @return false or int order_id
	 */
	public static function updateOrderStatus($orderNo,$admin_id = '',$note = '')
	{
		//获取订单信息
		$orderObj  = new IModel('order');
		$orderRow  = $orderObj->getObj('order_no = "'.$orderNo.'"');

		if(empty($orderRow))
		{
			return false;
		}
		// 已支付退出，防止多次回调
		if($orderRow['pay_status'] == 1)
		{
			return $orderRow['id'];
		}
		else if($orderRow['pay_status'] == 0)
		{
			$dataArray = array(
				'status'     => ($orderRow['status'] == 5) ? 5 : 2,
				'pay_time'   => ITime::getDateTime(),
				'pay_status' => 1,
			);

			$orderObj->setData($dataArray);
			$is_success = $orderObj->update('order_no = "'.$orderNo.'"');
			if($is_success == '')
			{
				return false;
			}

			//删除订单中使用的道具
			$ticket_id = trim($orderRow['prop']);
			if($ticket_id != '')
			{
				$propObj  = new IModel('prop');
				$propData = array('is_userd' => 1);
				$propObj->setData($propData);
				$propObj->update('id = '.$ticket_id);
			}

			//注册用户进行奖励
			if($orderRow['user_id'])
			{
				$user_id = $orderRow['user_id'];

				//获取用户信息
				$memberObj  = new IModel('member');
				$memberRow  = $memberObj->getObj('user_id = '.$user_id,'prop,group_id');

				//(1)删除订单中使用的道具
				if($ticket_id != '')
				{
					$finnalTicket = str_replace(','.$ticket_id.',',',',','.trim($memberRow['prop'],',').',');
					$memberData   = array('prop' => $finnalTicket);
					$memberObj->setData($memberData);
					$memberObj->update('user_id = '.$user_id);
				}

				if($memberRow)
				{
					//(2)进行促销活动奖励
			    	$proObj = new ProRule($orderRow['real_amount'],$orderRow['seller_id']);
			    	$proObj->setUserGroup($memberRow['group_id']);
			    	$proObj->setAward($user_id);

			    	//(3)增加经验值
			    	$memberData = array(
			    		'exp'   => 'exp + '.$orderRow['exp'],
			    	);
					$memberObj->setData($memberData);
					$memberObj->update('user_id = '.$user_id,'exp');

					//(4)增加积分
					$pointConfig = array(
						'user_id' => $user_id,
						'point'   => $orderRow['point'],
						'log'     => '成功购买了订单号：'.$orderRow['order_no'].'中的商品,奖励积分'.$orderRow['point'],
					);
					$pointObj = new Point();
					$pointObj->update($pointConfig);
				}
			}

			//插入收款单
			$collectionDocObj = new IModel('collection_doc');
			$collectionData   = array(
				'order_id'   => $orderRow['id'],
				'user_id'    => $orderRow['user_id'],
				'amount'     => $orderRow['order_amount'],
				'time'       => ITime::getDateTime(),
				'payment_id' => $orderRow['pay_type'],
				'pay_status' => 1,
				'if_del'     => 0,
				'note'       => $note,
				'admin_id'   => $admin_id ? $admin_id : 0
			);

			$collectionDocObj->setData($collectionData);
			$collectionDocObj->add();

			//促销活动订单
			if($orderRow['type'] != 0)
			{
				Active::payCallback($orderNo,$orderRow['type']);
			}

			//非货到付款的支付方式
			if($orderRow['pay_type'] != 0)
			{
				//减少库存量
				$orderGoodsDB = new IModel('order_goods');
				$orderGoodsList = $orderGoodsDB->query('order_id = '.$orderRow['id']);
				$orderGoodsListId = array();
				foreach($orderGoodsList as $key => $val)
				{
					$orderGoodsListId[] = $val['id'];
				}
				self::updateStore($orderGoodsListId,'reduce');
			}

			//自提点短信发送
			self::sendTakeself($orderNo);
			$mobile = "";

			//订单付款后短信通知商家或者管理员进行订单处理
			if($orderRow['seller_id'] > 0)
			{
				$sellerObj = new IModel('seller');
				$sellerRow = $sellerObj->getObj("id = ".$orderRow['seller_id']);
				$mobile    = $sellerRow['mobile'] ? $sellerRow['mobile'] : "";
			}
			else
			{
				$config = new Config('site_config');
				$mobile = $config->mobile ? $config->mobile : "";
			}
			$smsContent = smsTemplate::payFinishToAdmin(array('{orderNo}' => $orderNo));
			Hsms::send($mobile,$smsContent,0);

			return $orderRow['id'];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @brief 自提点短信发送
	 * @param string $orderNo 订单编号
	 */
	public static function sendTakeself($orderNo)
	{
		//获取订单信息
		$orderObj  = new IModel('order');
		$orderRow  = $orderObj->getObj('order_no = "'.$orderNo.'"');

		if(empty($orderRow))
		{
			return false;
		}

		//自提方式短信验证提醒
		if($orderRow['takeself'] > 0)
		{
			$takeselfObj = new IModel('takeself');
			$takeselfRow = $takeselfObj->getObj('id = '.$orderRow['takeself']);
			if($takeselfRow)
			{
				$mobile_code = rand(100000,999999);
				$orderObj->setData(array('checkcode' => $mobile_code));
				$checkResult = $orderObj->update('id = '.$orderRow['id']);
				if($checkResult)
				{
					$smsContent = smsTemplate::takeself(array('{orderNo}' => $orderRow['order_no'],'{address}' => $takeselfRow['address'],'{mobile_code}' => $mobile_code,'{phone}' => $takeselfRow['phone'],'{name}' => $takeselfRow['name']));
					Hsms::send($orderRow['mobile'],$smsContent,0);
				}
			}
		}
		//普通付款通知
		else
		{
			$smsContent = smsTemplate::payFinishToUser(array('{orderNo}' => $orderNo));
			Hsms::send($orderRow['mobile'],$smsContent,0);
		}
	}

	/**
	 * 订单商品数量更新操作[公共]
	 * @param array $orderGoodsId ID数据
	 * @param string $type 增加或者减少 add 或者 reduce
	 */
	public static function updateStore($orderGoodsId,$type = 'add')
	{
		if(!is_array($orderGoodsId))
		{
			$orderGoodsId = array($orderGoodsId);
		}

		$newStoreNums  = 0;
		$updateGoodsId = array();
		$orderGoodsObj = new IModel('order_goods');
		$goodsObj      = new IModel('goods');
		$productObj    = new IModel('products');
		$goodsList     = $orderGoodsObj->query('id in('.join(",",$orderGoodsId).') and is_send = 0','goods_id,product_id,goods_nums');

		foreach($goodsList as $key => $val)
		{
			//货品库存更新
			if($val['product_id'] != 0)
			{
				$productsRow = $productObj->getObj('id = '.$val['product_id'],'store_nums');
				if(!$productsRow)
				{
					continue;
				}
				$localStoreNums = $productsRow['store_nums'];

				//同步更新所属商品的库存量
				if(in_array($val['goods_id'],$updateGoodsId) == false)
				{
					$updateGoodsId[] = $val['goods_id'];
				}

				$newStoreNums = ($type == 'add') ? $localStoreNums + $val['goods_nums'] : $localStoreNums - $val['goods_nums'];
				$newStoreNums = $newStoreNums > 0 ? $newStoreNums : 0;

				$productObj->setData(array('store_nums' => $newStoreNums));
				$productObj->update('id = '.$val['product_id'],'store_nums');
			}
			//商品库存更新
			else
			{
				$goodsRow = $goodsObj->getObj('id = '.$val['goods_id'],'store_nums');
				if(!$goodsRow)
				{
					continue;
				}
				$localStoreNums = $goodsRow['store_nums'];

				$newStoreNums = ($type == 'add') ? $localStoreNums + $val['goods_nums'] : $localStoreNums - $val['goods_nums'];
				$newStoreNums = $newStoreNums > 0 ? $newStoreNums : 0;

				$goodsObj->setData(array('store_nums' => $newStoreNums));
				$goodsObj->update('id = '.$val['goods_id'],'store_nums');
			}

			//更新销售量sale字段，库存减少销售量增加，两者成反比
			$saleData = ($type == 'add') ? -$val['goods_nums'] : $val['goods_nums'];
			$goodsObj->setData(array('sale' => 'sale + '.$saleData));
			$goodsObj->update('id = '.$val['goods_id'],'sale');
		}

		//更新统计goods的库存
		if($updateGoodsId)
		{
			foreach($updateGoodsId as $val)
			{
				$totalRow = $productObj->getObj('goods_id = '.$val,'SUM(store_nums) as store');
				$goodsObj->setData(array('store_nums' => $totalRow['store']));
				$goodsObj->update('id = '.$val);
			}
		}
	}

	/**
	 * @brief 获取订单扩展数据资料
	 * @param $order_id int 订单的id
	 * @param $user_id int 用户id
	 * @return array()
	 */
	public function getOrderShow($order_id,$user_id = 0,$seller_id = null)
	{
		$where = 'id = '.$order_id;
		if($user_id !== 0)
		{
			$where .= ' and user_id = '.$user_id;
		}

		if(!$seller_id)
		{
			// $where .= ' and seller_id = '.$seller_id;
		}

		$data = array();

		//获得对象
		$tb_order = new IModel('order');
 		$data = $tb_order->getObj($where);
 		if($data)
 		{
	 		$data['order_id'] = $order_id;

	 		//获取配送方式
	 		$tb_delivery = new IModel('delivery');
	 		$delivery_info = $tb_delivery->getObj('id='.$data['distribution']);
	 		if($delivery_info)
	 		{
	 			$data['delivery'] = $delivery_info['name'];

	 			//自提点读取
	 			if($data['takeself'])
	 			{
	 				$data['takeself'] = self::getTakeselfInfo($data['takeself']);
	 			}
	 		}

	 		$areaData = area::name($data['province'],$data['city'],$data['area']);
	 		if(isset($areaData[$data['province']]) && isset($areaData[$data['city']]) && isset($areaData[$data['area']]))
	 		{
		 		$data['province_str'] = $areaData[$data['province']];
		 		$data['city_str']     = $areaData[$data['city']];
		 		$data['area_str']     = $areaData[$data['area']];
	 		}

	        //物流单号
	    	$tb_delivery_doc = new IQuery('delivery_doc as dd');
	    	$tb_delivery_doc->join   = 'left join freight_company as fc on dd.freight_id = fc.id';
	    	$tb_delivery_doc->fields = 'dd.delivery_code,fc.freight_name';
	    	$tb_delivery_doc->where  = 'order_id = '.$order_id;
	    	$delivery_info = $tb_delivery_doc->find();
	    	if($delivery_info)
	    	{
	    		$temp = array('freight_name' => array(),'delivery_code' => array());
	    		foreach($delivery_info as $key => $val)
	    		{
	    			$temp['freight_name'][]  = $val['freight_name'];
	    			$temp['delivery_code'][] = $val['delivery_code'];
	    		}
    			$data['freight']['freight_name']  = join(",",$temp['freight_name']);
    			$data['freight']['delivery_code'] = join(",",$temp['delivery_code']);
	    	}

	 		//获取支付方式
	 		$tb_payment = new IModel('payment');
	 		$payment_info = $tb_payment->getObj('id='.$data['pay_type']);
	 		if($payment_info)
	 		{
	 			$data['payment'] = $payment_info['name'];
	 			$data['paynote'] = $payment_info['note'];
	 		}

	 		//获取商品总重量和总金额
	 		$tb_order_goods = new IModel('order_goods');
	 		$order_goods_info = $tb_order_goods->query('order_id='.$order_id);
	 		$data['goods_amount'] = 0;
	 		$data['goods_weight'] = 0;

	 		if($order_goods_info)
	 		{
	 			foreach ($order_goods_info as $value)
	 			{
	 				$data['goods_amount'] += $value['real_price']   * $value['goods_nums'];
	 				$data['goods_weight'] += $value['goods_weight'] * $value['goods_nums'];
	 			}
	 		}

	 		//获取用户信息
	 		$query = new IQuery('user as u');
	 		$query->join = ' left join member as m on u.id=m.user_id ';
	 		$query->fields = 'u.username,u.email,m.mobile,m.contact_addr,m.true_name';
	 		$query->where = 'u.id='.$data['user_id'];
	 		$user_info = $query->find();
	 		if($user_info)
	 		{
	 			$user_info = current($user_info);
	 			$data['username']     = $user_info['username'];
	 			$data['email']        = $user_info['email'];
	 			$data['u_mobile']     = $user_info['mobile'];
	 			$data['contact_addr'] = $user_info['contact_addr'];
	 			$data['true_name']    = $user_info['true_name'];
	 		}
 		}
 		return $data;
	}

	/**
	 * 获取自提点基本信息
	 * @param $id int 自提点id
	 */
	public static function getTakeselfInfo($id)
	{
		$takeselfObj = new IModel('takeself');
		$takeselfRow = $takeselfObj->getObj('id = '.$id);

		$temp = area::name($takeselfRow['province'],$takeselfRow['city'],$takeselfRow['area']);
		$takeselfRow['province_str'] = $temp[$takeselfRow['province']];
		$takeselfRow['city_str']     = $temp[$takeselfRow['city']];
		$takeselfRow['area_str']     = $temp[$takeselfRow['area']];
		return $takeselfRow;
	}

	/**
	 * 获取订单基本信息
	 * @param $orderIdString string 订单ID序列
	 * @param $seller_id int 商家ID
	 */
	public function getOrderInfo($orderIdString,$seller_id = 0)
	{
		$orderObj    = new IModel('order');
		$areaIdArray = array();
		// $orderList   = $orderObj->query('id in ('.$orderIdString.') and seller_id = '.$seller_id);
		$orderList   = $orderObj->query('id in ('.$orderIdString.')');
		if(!$orderList)
		{
			IError::show(403,"无查阅订单权限");
		}

		foreach($orderList as $key => $val)
		{
			$temp = area::name($val['province'],$val['city'],$val['area']);
			$orderList[$key]['province_str'] = $temp[$val['province']];
			$orderList[$key]['city_str']     = $temp[$val['city']];
			$orderList[$key]['area_str']     = $temp[$val['area']];
		}

		return $orderList;
	}

	/**
	 * @brief 把订单商品同步到order_goods表中
	 * @param $order_id 订单ID
	 * @param $goodsInfo 商品和货品信息（购物车数据结构,countSum 最终生成的格式）
	 */
	public function insertOrderGoods($order_id,$goodsResult = array())
	{
		$orderGoodsObj = new IModel('order_goods');

		//清理旧的关联数据
		$orderGoodsObj->del('order_id = '.$order_id);

		$goodsArray = array(
			'order_id' => $order_id
		);

		if(isset($goodsResult['goodsList']))
		{
			foreach($goodsResult['goodsList'] as $key => $val)
			{
				//拼接商品名称和规格数据
				$specArray = array('name' => $val['name'],'goodsno' => $val['goods_no'],'value' => '');

				if(isset($val['spec_array']))
				{
					$spec = block::show_spec($val['spec_array']);
					foreach($spec as $skey => $svalue)
					{
						$specArray['value'] .= $skey.':'.$svalue.',';
					}
					$specArray['value'] = trim($specArray['value'],',');
				}

				$goodsArray['product_id']  = $val['product_id'];
				$goodsArray['goods_id']    = $val['goods_id'];
				$goodsArray['img']         = $val['img'];
				$goodsArray['goods_price'] = $val['sell_price'];
				$goodsArray['real_price']  = $val['sell_price'] - $val['reduce'];
				$goodsArray['goods_nums']  = $val['count'];
				$goodsArray['goods_weight']= $val['weight'];
				$goodsArray['goods_array'] = IFilter::addSlash(JSON::encode($specArray));
				$goodsArray['seller_id']   = $val['seller_id'];
				$orderGoodsObj->setData($goodsArray);
				$orderGoodsObj->add();
			}
		}
	}
	/**
	 * 获取订单状态
	 * @param $orderRow array('status' => '订单状态','pay_type' => '支付方式ID','distribution_status' => '配送状态','pay_status' => '支付状态')
	 * @return int 订单状态值 0:未知; 1:未付款等待发货(货到付款); 2:等待付款(线上支付); 3:已发货(已付款); 4:已付款等待发货; 5:已取消; 6:已完成(已付款,已收货); 7:已退款; 8:部分发货(不需要付款); 9:部分退款(未发货+部分发货); 10:部分退款(已发货); 11:已发货(未付款);
	 */
	public static function getOrderStatus($orderRow)
	{
		//1,刚生成订单,未付款
		if($orderRow['status'] == 1)
		{
			//选择货到付款
			if($orderRow['pay_type'] == 0)
			{
				if($orderRow['distribution_status'] == 0)
				{
					return 1;
				}
				else if($orderRow['distribution_status'] == 1)
				{
					return 11;
				}
				else if($orderRow['distribution_status'] == 2)
				{
					return 8;
				}
			}
			//选择在线支付
			else
			{
				return 2;
			}
		}
		//2,已经付款
		else if($orderRow['status'] == 2)
		{
			$refundDB  = new IModel('refundment_doc');
			$refundRow = $refundDB->getObj('order_no = "'.$orderRow['order_no'].'" and if_del = 0 and pay_status = 0');
			if($refundRow)
			{
				return 12;
			}

			if($orderRow['distribution_status'] == 0)
			{
				return 4;
			}
			else if($orderRow['distribution_status'] == 1)
			{
				return 3;
			}
			else if($orderRow['distribution_status'] == 2)
			{
				return 8;
			}
		}
		//3,取消或者作废订单
		else if($orderRow['status'] == 3 || $orderRow['status'] == 4)
		{
			return 5;
		}
		//4,完成订单
		else if($orderRow['status'] == 5)
		{
			return 6;
		}
		//5,退款
		else if($orderRow['status'] == 6)
		{
			return 7;
		}
		//6,部分退款
		else if($orderRow['status'] == 7)
		{
			//发货
			if($orderRow['distribution_status'] == 1)
			{
				return 10;
			}
			//未发货
			else
			{
				return 9;
			}
		}
		return 0;
	}

	//获取订单支付状态
	public static function getOrderPayStatusText($orderRow)
	{
		if($orderRow['status'] == '6')
		{
			return '全部退款';
		}

		if($orderRow['status'] == '7')
		{
			return '<span class="label label-warning">部分退款</span>';
		}

		if($orderRow['pay_status'] == 0)
		{
			return '<span class="label label-danger">未付款</span>';
		}

		if($orderRow['pay_status'] == 1)
		{
			return '<span class="label label-primary">已付款</span>';
		}
		return '未知';
	}

	//获取订单类型
	public static function getOrderTypeText($orderRow)
	{
		switch($orderRow['type'])
		{
			case "1":
			{
				return '团购订单';
			}
			break;

			case "2":
			{
				return '抢购订单';
			}
			break;

			default:
			{
				return '普通订单';
			}
		}
	}

	//获取订单配送状态
	public static function getOrderDistributionStatusText($orderRow)
	{
		if($orderRow['status'] == 5)
		{
			return '<span class="label label-primary red">已收货</span>';
		}
		else if($orderRow['distribution_status'] == 1)
		{
			return '<span class="label label-info">已发货</span>';
		}
		else if($orderRow['distribution_status'] == 0)
		{
			return '<span class="label label-danger">未发货</span>';
		}
		else if($orderRow['distribution_status'] == 2)
		{
			return '部分发货';
		}
	}

	/**
	 * 获取订单状态问题说明
	 * @param $statusCode int 订单的状态码
	 * @return string 订单状态说明
	 */
	public static function orderStatusText($statusCode)
	{
		$result = array(
			0 => '未知',
			1 => '等待发货',
			2 => '等待付款',
			3 => '已发货',
			4 => '等待发货',
			5 => '已取消',
			6 => '已完成',
			7 => '已退款',
			8 => '部分发货',
			9 => '部分发货',
			10=> '部分退款',
			11=> '已发货',
			12=> '申请退款',
		);
		return isset($result[$statusCode]) ? $result[$statusCode] : '';
	}

	/**
	 * @breif 订单的流向
	 * @param $orderRow array 订单数据
	 * @return array('时间' => '事件')
	 */
	public static function orderStep($orderRow)
	{
		$result = array();

		//1,创建订单
		$result[$orderRow['create_time']] = '订单创建';

		//2,订单支付
		if($orderRow['pay_status'] > 0)
		{
			$result[$orderRow['pay_time']] = '订单付款  '.$orderRow['order_amount'];
		}

		//3,订单配送
        if($orderRow['distribution_status'] > 0)
        {
        	$result[$orderRow['send_time']] = '订单发货完成';
    	}

		//4,订单完成
        if($orderRow['status'] == 5)
        {
        	$result[$orderRow['completion_time']] = '订单完成';
        }
        ksort($result);
        return $result;
	}

	/**
	 * @brief 商品发货接口
	 * @param string $order_id 订单id
	 * @param array $order_goods_relation 订单与商品关联id
	 * @param int $sendor_id 操作者id
	 * @param string $sendor 操作者所属 admin,seller
	 */
	public static function sendDeliveryGoods($order_id,$order_goods_relation,$sendor_id,$sendor = 'admin')
	{
		$order_no = IFilter::act(IReq::get('order_no'));

		//检查此订单是否存在未处理的退款申请
		$refundDB = new IModel('refundment_doc');
		$refundRow= $refundDB->getObj('order_no = "'.$order_no.'" and pay_status = 0 and if_del = 0');
		if($refundRow)
		{
			return "此订单有未处理的退款申请";
		}

	 	$paramArray = array(
	 		'order_id'      => $order_id,
	 		'user_id'       => IFilter::act(IReq::get('user_id'),'int'),
	 		'name'          => IFilter::act(IReq::get('name')),
	 		'postcode'      => IFilter::act(IReq::get('postcode'),'int'),
	 		'telphone'      => IFilter::act(IReq::get('telphone')),
	 		'province'      => IFilter::act(IReq::get('province'),'int'),
	 		'city'          => IFilter::act(IReq::get('city'),'int'),
	 		'area'          => IFilter::act(IReq::get('area'),'int'),
	 		'address'       => IFilter::act(IReq::get('address')),
	 		'mobile'        => IFilter::act(IReq::get('mobile')),
	 		'freight'       => IFilter::act(IReq::get('freight'),'float'),
	 		'delivery_code' => IFilter::act(IReq::get('delivery_code')),
	 		'delivery_type' => IFilter::act(IReq::get('delivery_type')),
	 		'note'          => IFilter::act(IReq::get('note'),'text'),
	 		'time'          => ITime::getDateTime(),
	 		'freight_id'    => IFilter::act(IReq::get('freight_id'),'int'),
	 	);

	 	switch($sendor)
	 	{
	 		case "admin":
	 		{
	 			$paramArray['admin_id'] = $sendor_id;

	 			$adminDB = new IModel('admin');
	 			$sendorData = $adminDB->getObj('id = '.$sendor_id);
	 			$sendorName = $sendorData['admin_name'];
	 			$sendorSort = '管理员';
	 		}
	 		break;

	 		case "seller":
	 		{
	 			$paramArray['seller_id'] = $sendor_id;

	 			$sellerDB = new IModel('seller_user');
	 			$sendorData = $sellerDB->getObj('id = '.$sendor_id);
	 			$sendorName = $sendorData['seller_name'];
	 			$sendorSort = '商户';
	 		}
	 		break;
	 	}

	 	//获得delivery_doc表的对象
	 	$tb_delivery_doc = new IModel('delivery_doc');
	 	$tb_delivery_doc->setData($paramArray);
	 	$deliveryId = $tb_delivery_doc->add();

		//订单对象
		$tb_order   = new IModel('order');
		$tbOrderRow = $tb_order->getObj('id = '.$order_id);

		//如果支付方式为货到付款，则减少库存
		if($tbOrderRow['pay_type'] == 0)
		{
		 	//减少库存量
		 	self::updateStore($order_goods_relation,'reduce');
		}

		//更新发货状态
	 	$orderGoodsDB = new IModel('order_goods');
	 	$orderGoodsRow = $orderGoodsDB->getObj('is_send = 0 and order_id = '.$order_id,'count(*) as num');
		$sendStatus = 2;//部分发货
	 	if(count($order_goods_relation) >= $orderGoodsRow['num'])
	 	{
	 		$sendStatus = 1;//全部发货
	 	}
	 	foreach($order_goods_relation as $key => $val)
	 	{
	 		//商家发货检查商品所有权
	 		if(isset($paramArray['seller_id']))
	 		{
	 			$orderGoodsData = $orderGoodsDB->getObj("id = ".$val);
	 			$goodsDB = new IModel('goods');
	 			// $sellerResult = $goodsDB->getObj("id = ".$orderGoodsData['goods_id']." and seller_id = ".$paramArray['seller_id']);
	 			$sellerResult = $goodsDB->getObj("id = ".$orderGoodsData['goods_id']);
	 			if(!$sellerResult)
	 			{
	 				$goodsDB->rollback();
	 				die('发货的商品信息与商家不符合');
	 			}
	 		}

	 		$orderGoodsDB->setData(array(
	 			"is_send"     => 1,
	 			"delivery_id" => $deliveryId,
	 		));
	 		$orderGoodsDB->update(" id = {$val} ");
	 	}

	 	//更新发货状态
	 	$tb_order->setData(array
	 	(
	 		'distribution_status' => $sendStatus,
	 		'send_time'           => ITime::getDateTime(),
	 	));
	 	$tb_order->update('id='.$order_id);

	 	//生成订单日志
    	$tb_order_log = new IModel('order_log');
    	$tb_order_log->setData(array(
    		'order_id' => $order_id,
    		'user'     => $sendorName,
    		'action'   => '发货',
    		'result'   => '成功',
    		'note'     => '订单【'.$order_no.'】由【'.$sendorSort.'】'.$sendorName.'发货',
    		'addtime'  => date('Y-m-d H:i:s')
    	));
    	$sendResult = $tb_order_log->add();

		//获取货运公司
    	$freightDB  = new IModel('freight_company');
    	$freightRow = $freightDB->getObj('id = '.$paramArray['freight_id']);

    	//发送短信
    	$replaceData = array(
    		'{user}'        => $paramArray['name'],
    		'{order_no}'         => $order_no,
    		// '{sendor}'           => '['.$sendorSort.']'.$sendorName,
    		'{exp}' => $freightRow['freight_name'],
    		'{exp_no}'      => $paramArray['delivery_code'],
    	);
    	// print_r($replaceData);
    	$mobileMsg = smsTemplate::sendGoods($replaceData);
    	Hsms::send($paramArray['mobile'],$mobileMsg,0);

    	//同步发货接口，如支付宝担保交易等
    	if($sendResult && $sendStatus == 1)
    	{
    		sendgoods::run($order_id);
    	}
    	return true;
	}

	/**
	 * @biref 是否可以发货操作
	 * @param array $orderRow 订单对象
	 */
	public static function isGoDelivery($orderRow)
	{
		/* 1,已经完全发货
		 * 2,非货到付款，并且没有支付*/
		if($orderRow['distribution_status'] == 1 || ($orderRow['pay_type'] != 0 && $orderRow['pay_status'] == 0))
		{
			return false;
		}
		return true;
	}

	/**
	 * @brief 获取商品发送状态
	 */
	public static function goodsSendStatus($is_send)
	{
		$data = array(0 => '未发货',1 => '已发货',2 => '已退货');
		return isset($data[$is_send]) ? $data[$is_send] : '';
	}

	//获取订单商品信息
	public static function getOrderGoods($order_id)
	{
		$orderGoodsObj        = new IQuery('order_goods');
		$orderGoodsObj->where = "order_id = ".$order_id;
		$orderGoodsObj->fields = 'id,goods_array,goods_id,product_id';
		$orderGoodsList = $orderGoodsObj->find();
		$goodList = array();
		foreach($orderGoodsList as $good)
		{
			$goodList[] = json_decode($good['goods_array']);
		}
		return $goodList;
	}

	/**
	 * @brief 返回检索条件相关信息
	 * @param int $search 条件数组
	 * @return array 查询条件（$join,$where）数据组
	 */
	public static function getSearchCondition($search)
	{
		$join  = "left join delivery as d on o.distribution = d.id left join payment as p on o.pay_type = p.id left join user as u on u.id = o.user_id";
		$where = "if_del = 0";
		//查询检索过滤
		if($search)
		{
			if(isset($search['name']) && isset($search['keywords']) && $search['name'] && $search['keywords'])
			{
				switch($search['name'])
				{
					case "shop_name":
					{
						$join .= ' left join seller as s on o.seller_id = s.id';
						$where .= " and s.shopname like '%".$search['keywords']."%'";
						// $sellerObj = new IModel('seller');
						// $sellerRow = $sellerObj->getObj('true_name = "'.$search['keywords'].'"');
						// $orderId = array(0);
						// if($sellerRow)
						// {
						// 	$orderGoodsObj        = new IQuery('order_goods as og');
						// 	$orderGoodsObj->join  = "left join goods as go on og.goods_id = go.id";
						// 	$orderGoodsObj->where = "go.seller_id = ".$sellerRow['id'];
						// 	$orderGoodsObj->distinct= "og.order_id";
						// 	$orderGoodsList = $orderGoodsObj->find();
						// 	foreach($orderGoodsList as $key => $val)
						// 	{
						// 		$orderId[] = $val['order_id'];
						// 	}
						// 	array_shift($orderId);
						// }
						// $where .= " and o.id in (".join(',',$orderId).")";
					}
					break;
					case "goods_name":
					{
						$join .= ' left join order_goods as og on o.id = og.order_id';
						$where .= " and og.goods_array like '%".$search['keywords']."%'";
					} 
					break;
					case "voucher":
					{
						$join .= ' left join order_voucher as v on o.id = v.order_id';
						$where .= ' and v.voucher = \'' . $search['keywords'] .'\'';
					}
					break;
					default:
					{
						$where .= " and o.".$search['name']." like '%".$search['keywords']."%'";
					}
					break;
				}
			}


			if(isset($search['start_time']) && isset($search['end_time']) && $search['start_time'] && $search['end_time']) {
				$where .= " and o.create_time >= '".$search['start_time'] . "' and o.create_time <= '" . $search['end_time'] . '\''; 
			}
			unset($search['start_time'], $search['end_time']);

			foreach($search as $key => $val)
			{
				if(!in_array($key,array('keywords','name')) && $val!='')
				{
					$where .= " and o.".$key." = ".$val;
				}
			}

		}
		$results = array($join,$where);
		unset($join,$where);
		return $results;
	}

	/**
	 * @brief 是否允许退款申请
	 * @param array $orderRow 订单表的数据结构
	 * @param array $orderGoodsIds 订单与商品关系表ID数组
	 * @return boolean true or false
	 */
	public static function isRefundmentApply($orderRow,$orderGoodsIds = array())
	{
		//要退款的orderGoodsId关联信息
		if($orderGoodsIds)
		{
			$order_id     = $orderRow['id'];
			$goodsOrderDB = new IModel('order_goods');
			$refundsDB    = new IModel('refundment_doc');

			foreach($orderGoodsIds as $key => $val)
			{
				$goodsOrderRow = $goodsOrderDB->getObj('id = '.$val.' and order_id = '.$order_id);
				if($goodsOrderRow && $goodsOrderRow['is_send'] == 2)
				{
					return "有商品已经做了退款处理";
				}

				if( $refundsDB->getObj('if_del = 0 and pay_status = 0 and FIND_IN_SET('.$val.',order_goods_id)') )
				{
					return "您已经对此商品提交了退款申请，请耐心等待";
				}
			}

    		//判断是否已经生成了结算申请或者已经结算了
    		$billObj = new IModel('bill');
    		$billRow = $billObj->getObj('FIND_IN_SET('.$order_id.',order_ids)');
    		if($billRow)
    		{
    			return '此订单金额已被商家结算完毕，请直接与商家联系退款';
    		}
    		return true;
		}
		else
		{
			//已经付款
			if($orderRow['pay_status'] == 1 && $orderRow['status'] != 6)
			{
				return true;
			}
			return false;
		}
	}

	/**
	 * @brief 退款状态
	 * @param int $pay_status 退款单状态数值
	 * @return string 状态描述
	 */
	public static function refundmentText($pay_status)
	{
		$result = array('0' => '申请退款', '1' => '退款失败', '2' => '退款成功');
		return isset($result[$pay_status]) ? $result[$pay_status] : '';
	}

	/**
	 * @brief 还原重置订单所使用的道具
	 * @param int $order 订单ID
	 */
	public static function resetOrderProp($order_id)
	{
		$orderDB   = new IModel('order');
		$orderList = $orderDB->query('id in ( '.$order_id.' ) and pay_status = 0 and prop is not null');
		foreach($orderList as $key => $orderRow)
		{
			if(isset($orderRow['prop']) && $orderRow['prop'])
			{
				$propDB = new IModel('prop');
				$propDB->setData(array('is_close' => 0));
				$propDB->update('id = '.$orderRow['prop']);
			}
		}
	}

	/**
	 * @brief 商家对退款申请的处理权限
	 * @param int $refundId 退款单ID
	 * @param int $seller_id 商家ID
	 * @return int 退款权限状态, 0:无权查看；1:只读；2：可读可写
	 */
	public static function isSellerRefund($refundId,$seller_id)
	{
		$refundDB = new IModel('refundment_doc');
		$refundRow= $refundDB->getObj('id = '.$refundId.' and seller_id = '.$seller_id);

		if($refundRow)
		{
			$orderDB = new IModel('order');
			$orderRow= $orderDB->getObj('id = '.$refundRow['order_id']);
			if($orderRow['is_checkout'] == 1)
			{
				return 1;
			}
			else
			{
				return 2;
			}
		}
		return 0;
	}

	/**
	 * @brief 订单退款操作
	 * @param int $refundId 退款单ID
	 * @param int $authorId 操作人ID
	 * @param string $type admin:管理员;seller:商家
	 * @param int $isNotBalance 是否充值到余额， 0:退款余额; 1:不退款到余额
	 * @return
	 */
	public static function refund($refundId,$authorId,$type = 'admin',$isNotBalance = 0)
	{
		$orderGoodsDB= new IModel('order_goods');
		$refundDB    = new IModel('refundment_doc');
		$goodsDB     = new IModel('goods');

		//获取goods_id和product_id用于给用户减积分，经验
		$refundsRow = $refundDB->getObj('id = '.$refundId);
		$order_id   = $refundsRow['order_id'];
		$order_no   = $refundsRow['order_no'];
		$user_id    = $refundsRow['user_id'];

		$orderGoodsList = $orderGoodsDB->query('id in ('.$refundsRow['order_goods_id'].') and is_send != 2');
		if(!$orderGoodsList)
		{
			return false;
		}

		//退款的商品关联信息
		$autoMount = 0;
		$orderRow  = array(
			'exp'      => 0,
			'point'    => 0,
			'order_no' => $order_no,
		);
		foreach($orderGoodsList as $key => $val)
		{
			$autoMount += $val['goods_nums'] * $val['real_price'];

			//库存增加
			self::updateStore($val['id'],'add');

			//更新退款状态
			$orderGoodsDB->setData(array('is_send' => 2));
			$orderGoodsDB->update('id = '.$val['id']);

			//退款积分,经验
			$goodsRow = $goodsDB->getObj('id = '.$val['goods_id']);
			$orderRow['exp']   += $goodsRow['exp']  * $val['goods_nums'];
			$orderRow['point'] += $goodsRow['point']* $val['goods_nums'];
		}

		//如果管理员（商家）自定义了退款金额。否则就使用默认的付款商品金额
		$amount = $refundsRow['amount'] > 0 ? $refundsRow['amount'] : $autoMount;

		//更新order表状态,查询是否订单中还有未退款的商品，判断是订单退款状态：全部退款或部分退款
		$isSendData = $orderGoodsDB->getObj('order_id = '.$order_id.' and is_send != 2');
		$orderStatus = 6;//全部退款
		if($isSendData)
		{
			$orderStatus = 7;//部分退款
		}
		$tb_order = new IModel('order');
		$tb_order->setData(array('status' => $orderStatus));
		$tb_order->update('id='.$order_id);

		/**
		 * 进行用户的余额增加操作,积分，经验的减少操作,
		 * 1,当全部退款时候,减少订单中记录的积分和经验;且如果没有发货的商品直接退回订单中的运费，报价，税金等
		 * 2,当部分退款时候,查询商品表中积分和经验
		 */
		if($orderStatus == 6)
		{
			Order_class::resetOrderProp($order_id);
			$orderRow = $tb_order->getObj('id = '.$order_id);

			//在订单商品没有发货情况下，返还运费，报价，税金
			$isDeliveryData = $orderGoodsDB->getObj('order_id = '.$order_id.' and delivery_id > 0');
			if(!$isDeliveryData)
			{
				$amount += $orderRow['real_freight'] + $orderRow['insured'] + $orderRow['taxes'];
			}
		}

		//更新退款表
		$updateData = array(
			'amount'       => $amount,
			'pay_status'   => 2,
			'dispose_time' => ITime::getDateTime(),
		);
		$refundDB->setData($updateData);
		$refundDB->update('id = '.$refundId);

		//更新用户的信息
		$obj = new IModel('member');
		$memberObj = $obj->getObj('user_id = '.$user_id,'exp,point');

		$exp = $memberObj['exp'] - $orderRow['exp'];
		$obj->setData(array('exp' => $exp   <= 0 ? 0 : $exp));
		$obj->update('user_id = '.$user_id);

		//积分记录日志
		$pointConfig = array(
			'user_id' => $user_id,
			'point'   => '-'.$orderRow['point'],
			'log'     => '退款订单号：'.$orderRow['order_no'].'中的商品,减掉积分 -'.$orderRow['point'],
		);
		$pointObj = new Point();
		$pointObj->update($pointConfig);

		//用户余额进行的操作记入account_log表
		$log = new AccountLog();
		$config = array(
			'user_id'  => $user_id,
			'event'    => 'drawback', //withdraw:提现,pay:余额支付,recharge:充值,drawback:退款到余额
			'num'      => $amount, //整形或者浮点，正为增加，负为减少
			'order_no' => $order_no // drawback类型的log需要这个值
		);

		if($type == 'admin')
		{
			$config['admin_id'] = $authorId;
			$adminObj  = new IModel('admin');
			$adminRow  = $adminObj->getObj('id = '.$authorId);
			$authorName= $adminRow['admin_name'];
		}
		else if($type == 'seller')
		{
			$config['seller_id'] = $authorId;
			$sellerObj = new IModel('seller');
			$sellerRow = $sellerObj->getObj('id = '.$authorId);
			$authorName= $sellerRow['seller_name'];
		}

		//生成订单日志
		$tb_order_log = new IModel('order_log');
		$tb_order_log->setData(array(
			'order_id' => $order_id,
			'user'     => $authorName,
			'action'   => '退款',
			'result'   => '成功',
			'note'     => '订单【'.$order_no.'】退款，退款金额：￥'.$amount,
			'addtime'  => ITime::getDateTime(),
		));
		$tb_order_log->add();
		return $isNotBalance == 0 ? $log->write($config) : true;
	}

	/**
	 * @brief 检查订单是否重复
	 * @param array $checkData 检查的订单数据
	 * @param array $goodsList 购买的商品数据信息
	 */
	public static function checkRepeat($checkData,$goodsList)
	{return true;	// 暂时去掉 2016-3-17 14:53:36
    	$checkWhere = array();
    	foreach($checkData as $key => $val)
    	{
    		if(!$val)
    		{
				return "请仔细填写订单所需内容";
    		}
    		$checkWhere[] = "`".$key."` = '".$val."'";
    	}
    	$checkWhere[] = " NOW() < date_add(create_time,INTERVAL 2 MINUTE) "; //在有限时间段内生成的订单
		$where = join(" and ",$checkWhere);

		//查询订单数据库
		$orderObj  = new IModel('order');
    	$orderList = $orderObj->query($where);

    	//有重复下单的嫌疑
    	if($orderList)
    	{
    		//当前购买的
    		$nowBuy = "";
    		foreach($goodsList as $key => $val)
    		{
    			$nowBuy .= $val['goods_id']."@".$val['product_id'];
    		}

			//已经购买的
			$orderGoodsDB = new IModel('order_goods');
			foreach($orderList as $key => $val)
			{
	    		$isBuyed = "";
	    		$orderGoodsList = $orderGoodsDB->query("order_id = ".$val['id']);
	    		foreach($orderGoodsList as $k => $item)
	    		{
	    			$isBuyed .= $item['goods_id']."@".$item['product_id'];
	    		}

	    		if($nowBuy == $isBuyed)
	    		{
					return "您所提交的订单重复，请稍候再试...";
	    		}
			}
    	}
    	return true;
	}

	/**
	 * @brief  设置批量子订单
	 * @param  array $orderKey   批量订单KEY
	 * @param  array $orderArray 订单号数组
	 * @return boolean
	 */
	public static function setBatch($orderKey,$orderArray)
	{
		$cacheObj = new ICache('file');
		return $cacheObj->set($orderKey,$orderArray);
	}

	/**
	 * @brief  获取批量子订单
	 * @param  array $orderKey 批量订单KEY
	 * @return array 订单号数组
	 */
	public static function getBatch($orderKey)
	{
		$cacheObj = new ICache('file');
		return $cacheObj->get($orderKey);
	}
}