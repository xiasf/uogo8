<?php
/**
 * @brief 用户中心模块
 * @class Ucenter
 * @note  前台
 */
class Ucenter extends IController
{
	public $layout = 'ucenter';

	public function init()
	{
		CheckRights::checkUserRights();
		if(!$this->user)
		{
			$this->redirect('/simple/login');
		}

	}
    public function index()
    {
        $this->initPayment();
        $this->redirect('index');
    }

	//[用户头像]上传
	function user_ico_upload()
	{
		$result = array(
			'isError' => true,
		);

		if(isset($_FILES['attach']['name']) && $_FILES['attach']['name'] != '')
		{
			$photoObj = new PhotoUpload();
			$photo    = $photoObj->run();

			if($photo['attach']['img'])
			{
				$user_id   = $this->user['user_id'];
				$user_obj  = new IModel('user');
				$dataArray = array(
					'head_ico' => $photo['attach']['img'],
				);
				$user_obj->setData($dataArray);
				$where  = 'id = '.$user_id;
				$isSuss = $user_obj->update($where);

				if($isSuss !== false)
				{
					$result['isError'] = false;
					$result['data'] = IUrl::creatUrl().$photo['attach']['img'];
					ISafe::set('head_ico',$dataArray['head_ico']);
				}
				else
				{
					$result['message'] = '上传失败';
				}
			}
			else
			{
				$result['message'] = '上传失败';
			}
		}
		else
		{
			$result['message'] = '请选择图片';
		}
		echo 'parent.callback_user_ico('.JSON::encode($result).');';
	}

    /**
     * @brief 我的订单列表
     */
    public function order()
    {
        $this->initPayment();
        $this->redirect('order');

    }
    /**
     * @brief 初始化支付方式
     */
    private function initPayment()
    {
        $payment = new IQuery('payment');
        $payment->fields = 'id,name,type';
        $payments = $payment->find();
        $items = array();
        foreach($payments as $pay)
        {
            $items[$pay['id']]['name'] = $pay['name'];
            $items[$pay['id']]['type'] = $pay['type'];
        }
        $this->payments = $items;
    }
    /**
     * @brief 订单详情
     * @return String
     */
    public function order_detail()
    {
        $id = IFilter::act(IReq::get('id'),'int');

        $orderObj = new order_class();
        $this->order_info = $orderObj->getOrderShow($id,$this->user['user_id']);

        if(!$this->order_info)
        {
        	IError::show(403,'订单信息不存在');
        }

        $this->residualTime = ITime::time1(ITime::getTime($this->order_info['create_time']) + $this->config['order_cancel_time'] * 24 * 60 *60 - time());
        $this->redirect('order_detail',false);
    }

    //操作订单状态
	public function order_status()
	{
		$op    = IFilter::act(IReq::get('op'));
		$id    = IFilter::act( IReq::get('order_id'),'int' );
		$model = new IModel('order');

		switch($op)
		{
			case "cancel":
			{
				$model->setData(array('status' => 3));
				if($model->update("id = ".$id." and distribution_status = 0 and status = 1 and user_id = ".$this->user['user_id']))
				{
					//修改红包状态
					$prop_obj = $model->getObj('id='.$id,'prop');
					$prop_id = isset($prop_obj['prop'])?$prop_obj['prop']:'';
					if($prop_id != '')
					{
						$prop = new IModel('prop');
						$prop->setData(array('is_close'=>0));
						$prop->update('id='.$prop_id);
					}
				}
			}
			break;

			case "confirm":
			{
				$model->setData(array('status' => 5,'distribution_status' => 1,'completion_time' => date('Y-m-d h:i:s')));
                if($model->update("id = ".$id." and user_id = ".$this->user['user_id']))
				// if($model->update("id = ".$id." and distribution_status = 1 and user_id = ".$this->user['user_id']))
				{
					$orderRow = $model->getObj('id = '.$id);

					//确认收货后进行支付
					Order_Class::updateOrderStatus($orderRow['order_no']);

		    		//增加用户评论商品机会
		    		Order_Class::addGoodsCommentChange($id);
                    
                    //验证消费券
                    $order_voucher = new IModel('order_voucher');
                    $order_voucher->setData(array('isused' => 1, 'usetime' => date('Y-m-d H:i:s')));
                    $order_voucher->update('order_id = ' . $id);

		    		//确认收货以后直接跳转到评论页面 (暂时不这样，手机端没有评论模板 2016-4-5 11:11:17)
		    		$this->redirect('evaluation');
				}
			}
			break;
		}
		$this->redirect("order_detail/id/$id");
	}
    /**
     * @brief 我的地址
     */
    public function address()
    {
		//取得自己的地址
		$query = new IQuery('address');
        $query->where = 'user_id = '.$this->user['user_id'];
		$address = $query->find();
		$areas   = array();

		if($address)
		{
			foreach($address as $ad)
			{
				$temp = area::name($ad['province'],$ad['city'],$ad['area']);
				if(isset($temp[$ad['province']]) && isset($temp[$ad['city']]) && isset($temp[$ad['area']]))
				{
					$areas[$ad['province']] = $temp[$ad['province']];
					$areas[$ad['city']]     = $temp[$ad['city']];
					$areas[$ad['area']]     = $temp[$ad['area']];
				}
			}
		}

		$this->areas = $areas;
		$this->address = $address;
        $this->redirect('address');
    }
    /**
     * @brief 收货地址管理
     */
	public function address_edit()
	{
		$id = intval(IReq::get('id'));
		$accept_name = IFilter::act(IReq::get('accept_name'));
		$province = intval(IReq::get('province'));
		$city = intval(IReq::get('city'));
		$area = intval(IReq::get('area'));
		$address = IFilter::act(IReq::get('address'));
		$zip = IFilter::act(IReq::get('zip'));
		$telphone = IFilter::act(IReq::get('telphone'));
		$mobile = IFilter::act(IReq::get('mobile'));
		$default = IReq::get('is_default')!= 1 ? 0 : 1;
        $user_id = $this->user['user_id'];

		$model = new IModel('address');
		$data = array('user_id'=>$user_id,'accept_name'=>$accept_name,'province'=>$province,'city'=>$city,'area'=>$area,'address'=>$address,'zip'=>$zip,'telphone'=>$telphone,'mobile'=>$mobile,'is_default'=>$default);

        //如果设置为首选地址则把其余的都取消首选
        if($default==1)
        {
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
        }

		$model->setData($data);

		if($id == '')
		{
			$model->add();
		}
		else
		{
			$model->update('id = '.$id);
		}
		$this->redirect('address');
	}
    /**
     * @brief 收货地址删除处理
     */
	public function address_del()
	{
		$id = IFilter::act( IReq::get('id'),'int' );
		$model = new IModel('address');
		$model->del('id = '.$id.' and user_id = '.$this->user['user_id']);
		$this->redirect('address');
	}
    /**
     * @brief 设置默认的收货地址
     */
    public function address_default()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $default = IFilter::act(IReq::get('is_default'));
        $model = new IModel('address');
        if($default == 1)
        {
            $model->setData(array('is_default' => 0));
            $model->update("user_id = ".$this->user['user_id']);
        }
        $model->setData(array('is_default' => $default));
        $model->update("id = ".$id." and user_id = ".$this->user['user_id']);
        $this->redirect('address');
    }
    /**
     * @brief 退款申请页面
     */
    public function refunds_update()
    {
        $order_goods_id = IFilter::act( IReq::get('order_goods_id'),'int' );
        $order_id       = IFilter::act( IReq::get('order_id'),'int' );
        $user_id        = $this->user['user_id'];
        $content        = IFilter::act(IReq::get('content'),'text');
        $message        = '';

        if(!$content || !$order_goods_id)
        {
        	$message = "请填写退款理由和选择要退款的商品";
	        $this->redirect('refunds',false);
	        Util::showMessage($message);
        }

        $orderDB      = new IModel('order');
        $orderRow     = $orderDB->getObj("id = ".$order_id." and user_id = ".$user_id);
        $refundResult = Order_Class::isRefundmentApply($orderRow,$order_goods_id);

        //判断退款申请是否有效
        if($refundResult === true)
        {
			//退款单数据
    		$updateData = array(
				'order_no'       => $orderRow['order_no'],
				'order_id'       => $order_id,
				'user_id'        => $user_id,
				'time'           => ITime::getDateTime(),
				'content'        => $content,
				'seller_id'      => $orderRow['seller_id'],
				'order_goods_id' => join(",",$order_goods_id),
			);

    		//写入数据库
    		$refundsDB = new IModel('refundment_doc');
    		$refundsDB->setData($updateData);
    		$refundsDB->add();

    		$this->redirect('refunds');
        }
        else
        {
        	$message = $refundResult;
	        $this->redirect('refunds',false);
	        Util::showMessage($message);
        }
    }
    /**
     * @brief 退款申请删除
     */
    public function refunds_del()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $model = new IModel("refundment_doc");
        $model->del("id = ".$id." and user_id = ".$this->user['user_id']);
        $this->redirect('refunds');
    }
    /**
     * @brief 查看退款申请详情
     */
    public function refunds_detail()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $refundDB = new IModel("refundment_doc");
        $refundRow = $refundDB->getObj("id = ".$id." and user_id = ".$this->user['user_id']);
        if($refundRow)
        {
        	//获取商品信息
        	$orderGoodsDB   = new IModel('order_goods');
        	$orderGoodsList = $orderGoodsDB->query("id in (".$refundRow['order_goods_id'].")");
        	if($orderGoodsList)
        	{
        		$refundRow['goods'] = $orderGoodsList;
        		$this->data = $refundRow;
        	}
        	else
        	{
	        	$this->redirect('refunds',false);
	        	Util::showMessage("没有找到要退款的商品");
        	}
        	$this->redirect('refunds_detail');
        }
        else
        {
        	$this->redirect('refunds',false);
        	Util::showMessage("退款信息不存在");
        }
    }
    /**
     * @brief 查看退款申请详情
     */
	public function refunds_edit()
	{
		$order_id = IFilter::act(IReq::get('order_id'),'int');
		if($order_id)
		{
			$orderDB  = new IModel('order');
			$orderRow = $orderDB->getObj('id = '.$order_id.' and user_id = '.$this->user['user_id']);
			if($orderRow)
			{
				$this->orderRow = $orderRow;
				$this->redirect('refunds_edit');
				exit;
			}
		}
		$this->redirect('refunds');
	}

    /**
     * @brief 建议中心
     */
    public function complain_edit()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $title = IFilter::act(IReq::get('title'),'string');
        $content = IFilter::act(IReq::get('content'),'string' );
        $user_id = $this->user['user_id'];
        $model = new IModel('suggestion');
        $model->setData(array('user_id'=>$user_id,'title'=>$title,'content'=>$content,'time'=>date('Y-m-d H:i:s')));
        if($id =='')
        {
            $model->add();
        }
        else
        {
            $model->update('id = '.$id.' and user_id = '.$this->user['user_id']);
        }
        $this->redirect('complain');
    }
    /**
     * @brief 删除消息
     * @param int $id 消息ID
     */
    public function message_del()
    {
        $id = IFilter::act( IReq::get('id') ,'int' );
        $msg = new Mess($this->user['user_id']);
        $msg->delMessage($id);
        $this->redirect('message');
    }
    public function message_read()
    {
        $id = IFilter::act( IReq::get('id'),'int' );
        $msg = new Mess($this->user['user_id']);
        echo $msg->writeMessage($id,1);
    }

    //[修改密码]修改动作
    function password_edit()
    {
    	$user_id    = $this->user['user_id'];

    	$fpassword  = IReq::get('fpassword');
    	$password   = IReq::get('password');
    	$repassword = IReq::get('repassword');

    	$userObj    = new IModel('user');
    	$where      = 'id = '.$user_id;
    	$userRow    = $userObj->getObj($where);

		if(!preg_match('|\w{6,32}|',$password))
		{
			$message = '密码格式不正确，请重新输入';
		}
    	else if($password != $repassword)
    	{
    		$message  = '二次密码输入的不一致，请重新输入';
    	}
    	else if(md5($fpassword) != $userRow['password'])
    	{
    		$message  = '原始密码输入错误';
    	}
    	else
    	{
    		$passwordMd5 = md5($password);
	    	$dataArray = array(
	    		'password' => $passwordMd5,
	    	);

	    	$userObj->setData($dataArray);
	    	$result  = $userObj->update($where);
	    	if($result)
	    	{
	    		ISafe::set('user_pwd',$passwordMd5);
	    		$message = '密码修改成功';
	    	}
	    	else
	    	{
	    		$message = '密码修改失败';
	    	}
		}

    	$this->redirect('password',false);
    	Util::showMessage($message);
    }

    //[个人资料]展示 单页
    function info()
    {
    	$user_id = $this->user['user_id'];

    	$userObj       = new IModel('user');
    	$where         = 'id = '.$user_id;
    	$this->userRow = $userObj->getObj($where);

    	$memberObj       = new IModel('member');
    	$where           = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('info');
    }

    //[个人资料] 修改 [动作]
    function info_edit_act()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member');
    	$where     = 'user_id = '.$user_id;

    	$dataArray       = array(
    		'true_name'    => IFilter::act( IReq::get('true_name') ,'string'),
    		'sex'          => IFilter::act( IReq::get('sex'),'int' ),
    		'birthday'     => IFilter::act( IReq::get('birthday') ),
    		'qq'           => IFilter::act( IReq::get('qq') , 'string' ),
    		'mobile'       => IFilter::act( IReq::get('mobile'), 'string'),
    	);

    	$memberObj->setData($dataArray);
    	$memberObj->update($where);
    	$this->info();
    }

    //[账户余额] 展示[单页]
    function withdraw()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member','balance');
    	$where     = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('withdraw');
    }

	//[账户余额] 提现动作
    function withdraw_act()
    {
    	$user_id = $this->user['user_id'];
    	$amount  = IFilter::act( IReq::get('amount','post') ,'float' );
    	$message = '';

    	$dataArray = array(
    		'name'   => IFilter::act( IReq::get('name','post') ,'string'),
    		'note'   => IFilter::act( IReq::get('note','post'), 'string'),
			'amount' => $amount,
			'user_id'=> $user_id,
			'time'   => ITime::getDateTime(),
    	);

		$mixAmount = 0;
		$memberObj = new IModel('member');
		$where     = 'user_id = '.$user_id;
		$memberRow = $memberObj->getObj($where,'balance');

		//提现金额范围
		if($amount <= $mixAmount)
		{
			$message = '提现的金额必须大于'.$mixAmount.'元';
		}
		else if($amount > $memberRow['balance'])
		{
			$message = '提现的金额不能大于您的帐户余额';
		}
		else
		{
	    	$obj = new IModel('withdraw');
	    	$obj->setData($dataArray);
	    	$obj->add();
	    	$this->redirect('withdraw');
		}

		if($message != '')
		{
			$this->memberRow = array('balance' => $memberRow['balance']);
			$this->withdrawRow = $dataArray;
			$this->redirect('withdraw',false);
			Util::showMessage($message);
		}
    }

    //[账户余额] 提现详情
    function withdraw_detail()
    {
    	$user_id = $this->user['user_id'];

    	$id  = IFilter::act( IReq::get('id'),'int' );
    	$obj = new IModel('withdraw');
    	$where = 'id = '.$id.' and user_id = '.$user_id;
    	$this->withdrawRow = $obj->getObj($where);
    	$this->redirect('withdraw_detail');
    }

    //[提现申请] 取消
    function withdraw_del()
    {
    	$id = IFilter::act( IReq::get('id'),'int');
    	if($id)
    	{
    		$dataArray   = array('is_del' => 1);
    		$withdrawObj = new IModel('withdraw');
    		$where = 'id = '.$id.' and user_id = '.$this->user['user_id'];
    		$withdrawObj->setData($dataArray);
    		$withdrawObj->update($where);
    	}
    	$this->redirect('withdraw');
    }

    //[余额交易记录]
    function account_log()
    {
    	$user_id   = $this->user['user_id'];

    	$memberObj = new IModel('member','balance');
    	$where     = 'user_id = '.$user_id;
    	$this->memberRow = $memberObj->getObj($where);
    	$this->redirect('account_log');
    }

    //[收藏夹]备注信息
    function edit_summary()
    {
    	$user_id = $this->user['user_id'];

    	$id      = IFilter::act( IReq::get('id'),'int' );
    	$summary = IFilter::act( IReq::get('summary'),'string' );

    	//ajax返回结果
    	$result  = array(
    		'isError' => true,
    	);

    	if(!$id)
    	{
    		$result['message'] = '收藏夹ID值丢失';
    	}
    	else if(!$summary)
    	{
    		$result['message'] = '请填写正确的备注信息';
    	}
    	else
    	{
	    	$favoriteObj = new IModel('favorite');
	    	$where       = 'id = '.$id.' and user_id = '.$user_id;

	    	$dataArray   = array(
	    		'summary' => $summary,
	    	);

	    	$favoriteObj->setData($dataArray);
	    	$is_success = $favoriteObj->update($where);

	    	if($is_success === false)
	    	{
	    		$result['message'] = '更新信息错误';
	    	}
	    	else
	    	{
	    		$result['isError'] = false;
	    	}
    	}
    	echo JSON::encode($result);
    }

        //[收藏夹]获取收藏夹数据
    function get_seller_favorite()
    {

        //获取收藏夹信息
        $page = (isset($_GET['page'])&&(intval($_GET['page'])>0))?intval($_GET['page']):1;
        $favorite_sellerObj = new IQuery("favorite_seller");
        $favorite_sellerObj->where = "user_id = ".$this->user['user_id'];
        $favorite_sellerObj->page  = $page;
        $items = $favorite_sellerObj->find();

        $goodsIdArray   = array();
        foreach($items as $val)
        {
            $goodsIdArray[] = $val['sid'];
        }
        //商品数据
        if(!empty($goodsIdArray))
        {
            $goodsIdStr = join(',',$goodsIdArray);
            $goodsObj   = new IModel('seller');
            $goodsList  = $goodsObj->query('id in ('.$goodsIdStr.')');
        }

        foreach($items as $key => $val)
        {
            foreach($goodsList as $gkey => $seller)
            {
                if($seller['id'] == $val['sid'])
                {
                    $items[$key]['data'] = $seller;

                    //效率考虑,让goodsList循环次数减少
                    unset($goodsList[$gkey]);
                }
            }

            //如果相应的商品或者货品已经被删除了，
            if(!isset($items[$key]['data']))
            {
                $favoriteModel = new IModel('favorite_seller');
                $favoriteModel->del("id={$val['id']}");
                unset($items[$key]);
            }
        }
        return $items;
    }

    //[收藏夹]获取收藏夹数据
	function get_favorite(&$favoriteObj)
    {
		//获取收藏夹信息
	    $page = (isset($_GET['page'])&&(intval($_GET['page'])>0))?intval($_GET['page']):1;

		$favoriteObj = new IQuery("favorite");
		$cat_id = intval(IReq::get('cat_id'));
		$where = '';
		if($cat_id != 0)
		{
			$where = ' and cat_id = '.$cat_id;
		}
		$favoriteObj->where = "user_id = ".$this->user['user_id'].$where;
		$favoriteObj->page  = $page;
		$items = $favoriteObj->find();

		$goodsIdArray   = array();
		foreach($items as $val)
		{
			$goodsIdArray[] = $val['rid'];
		}

		//商品数据
		if(!empty($goodsIdArray))
		{
			$goodsIdStr = join(',',$goodsIdArray);
			$goodsObj   = new IModel('goods');
			$goodsList  = $goodsObj->query('id in ('.$goodsIdStr.')');
		}

		foreach($items as $key => $val)
		{
			foreach($goodsList as $gkey => $goods)
			{
				if($goods['id'] == $val['rid'])
				{
					$items[$key]['data'] = $goods;

					//效率考虑,让goodsList循环次数减少
					unset($goodsList[$gkey]);
				}
			}

			//如果相应的商品或者货品已经被删除了，
			if(!isset($items[$key]['data']))
			{
				$favoriteModel = new IModel('favorite');
				$favoriteModel->del("id={$val['id']}");
				unset($items[$key]);
			}
		}
		return $items;
    }

            //[收藏夹]删除
    function favorite_seller_del()
    {
        $user_id = $this->user['user_id'];
        $id      = IReq::get('id');

        if(!empty($id))
        {
            $id = IFilter::act($id,'int');

            $favoriteObj = new IModel('favorite_seller');

            if(is_array($id))
            {
                $idStr = join(',',$id);
                $where = 'user_id = '.$user_id.' and id in ('.$idStr.')';
            }
            else
            {
                $where = 'user_id = '.$user_id.' and id = '.$id;
            }

            $favoriteObj->del($where);
            $this->redirect('favorite_seller');
        }
        else
        {
            $this->redirect('favorite_seller',false);
            Util::showMessage('请选择要删除的数据');
        }
    }

    //[收藏夹]删除
    function favorite_del()
    {
    	$user_id = $this->user['user_id'];
    	$id      = IReq::get('id');

		if(!empty($id))
		{
			$id = IFilter::act($id,'int');

			$favoriteObj = new IModel('favorite');

			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = 'user_id = '.$user_id.' and id in ('.$idStr.')';
			}
			else
			{
				$where = 'user_id = '.$user_id.' and id = '.$id;
			}

			$favoriteObj->del($where);
			$this->redirect('favorite');
		}
		else
		{
			$this->redirect('favorite',false);
			Util::showMessage('请选择要删除的数据');
		}
    }

    //[我的积分] 单页展示
    function integral()
    {
    	/*获取积分增减的记录日期时间段*/
    	$this->historyTime = IFilter::string( IReq::get('history_time','post') );
    	$defaultMonth = 3;//默认查找最近3个月内的记录

		$lastStamp    = ITime::getTime(ITime::getNow('Y-m-d')) - (3600*24*30*$defaultMonth);
		$lastTime     = ITime::getDateTime('Y-m-d',$lastStamp);

		if($this->historyTime != null && $this->historyTime != 'default')
		{
			$historyStamp = ITime::getDateTime('Y-m-d',($lastStamp - (3600*24*30*$this->historyTime)));
			$this->c_datetime = 'datetime >= "'.$historyStamp.'" and datetime < "'.$lastTime.'"';
		}
		else
		{
			$this->c_datetime = 'datetime >= "'.$lastTime.'"';
		}

    	$memberObj         = new IModel('member');
    	$where             = 'user_id = '.$this->user['user_id'];
    	$this->memberRow   = $memberObj->getObj($where,'point');
    	$this->redirect('integral',false);
    }

    //[我的积分]积分兑换代金券 动作
    function trade_ticket()
    {
    	$ticketId = IFilter::act( IReq::get('ticket_id','post'),'int' );
    	$message  = '';
    	if(intval($ticketId) == 0)
    	{
    		$message = '请选择要兑换的代金券';
    	}
    	else
    	{
    		$nowTime   = ITime::getDateTime();
    		$ticketObj = new IModel('ticket');
    		$ticketRow = $ticketObj->getObj('id = '.$ticketId.' and point > 0 and start_time <= "'.$nowTime.'" and end_time > "'.$nowTime.'"');
    		if(empty($ticketRow))
    		{
    			$message = '对不起，此代金券不能兑换';
    		}
    		else
    		{
	    		$memberObj = new IModel('member');
	    		$where     = 'user_id = '.$this->user['user_id'];
	    		$memberRow = $memberObj->getObj($where,'point');

	    		if($ticketRow['point'] > $memberRow['point'])
	    		{
	    			$message = '对不起，您的积分不足，不能兑换此类代金券';
	    		}
	    		else
	    		{
	    			//生成红包
					$dataArray = array(
						'condition' => $ticketRow['id'],
						'name'      => $ticketRow['name'],
						'card_name' => 'T'.IHash::random(8),
						'card_pwd'  => IHash::random(8),
						'value'     => $ticketRow['value'],
						'start_time'=> $ticketRow['start_time'],
						'end_time'  => $ticketRow['end_time'],
						'is_send'   => 1,
					);
					$propObj = new IModel('prop');
					$propObj->setData($dataArray);
					$insert_id = $propObj->add();

					//用户prop字段值null时
					$memberArray = array('prop' => ','.$insert_id.',');
					$memberObj->setData($memberArray);
					$result      = $memberObj->update('user_id = '.$this->user["user_id"].' and ( prop is NULL or prop = "" )');

					//用户prop字段值非null时
					if(!$result)
					{
						$memberArray = array(
							'prop' => 'concat(prop,"'.$insert_id.',")',
						);
						$memberObj->setData($memberArray);
						$result = $memberObj->update('user_id = '.$this->user["user_id"],'prop');
					}

					//代金券成功
					if($result)
					{
						$pointConfig = array(
							'user_id' => $this->user['user_id'],
							'point'   => '-'.$ticketRow['point'],
							'log'     => '积分兑换代金券，扣除了 -'.$ticketRow['point'].'积分',
						);
						$pointObj = new Point;
						$pointObj->update($pointConfig);
					}
	    		}
    		}
    	}

    	//展示
    	if($message != '')
    	{
    		$this->integral();
    		Util::showMessage($message);
    	}
    	else
    	{
    		$this->redirect('redpacket');
    	}
    }

    /**
     * 余额付款
     * T:支付失败;
     * F:支付成功;
     */
    function payment_balance()
    {
    	$urlStr  = '';
    	$user_id = intval($this->user['user_id']);

    	$return['attach']     = IReq::get('attach');
    	$return['total_fee']  = IReq::get('total_fee');
    	$return['order_no']   = IReq::get('order_no');
    	$return['return_url'] = IReq::get('return_url');
    	$sign                 = IReq::get('sign');
    	if(stripos($return['order_no'],'recharge') !== false)
    	{
    		IError::show(403,'余额支付方式不能用于在线充值');
    	}

    	if(floatval($return['total_fee']) <= 0 || $return['order_no'] == '' || $return['return_url'] == '')
    	{
    		IError::show(403,'支付参数不正确');
    	}

		$paymentDB  = new IModel('payment');
		$paymentRow = $paymentDB->getObj('class_name = "balance" ');
		$pkey       = Payment::getConfigParam($paymentRow['id'],'M_PartnerKey');

    	//md5校验
    	ksort($return);
		foreach($return as $key => $val)
		{
			$urlStr .= $key.'='.urlencode($val).'&';
		}

		$encryptKey = isset(IWeb::$app->config['encryptKey']) ? IWeb::$app->config['encryptKey'] : 'iwebshop';
		$urlStr .= $user_id.$pkey.$encryptKey;
		if($sign != md5($urlStr))
		{
			IError::show(403,'数据校验不正确');
		}

    	$memberObj = new IModel('member');
    	$memberRow = $memberObj->getObj('user_id = '.$user_id);

    	if(empty($memberRow))
    	{
    		IError::show(403,'用户信息不存在');
    	}

    	if($memberRow['balance'] < $return['total_fee'])
    	{
    		IError::show(403,'账户余额不足');
    	}

		$orderNo   = IFilter::act($return['order_no']);
		$moreOrder = Order_Class::getBatch($orderNo);
		if(!$moreOrder)
		{
			IError::show("订单信息不存在",403);
		}

		$orderObj = new IModel('order');
		foreach($moreOrder as $key => $val)
		{
    		$orderRow = $orderObj->getObj('order_no  = "'.$val.'" and pay_status = 0 and status = 1 and user_id = '.$user_id);
    		if(!$orderRow)
    		{
    			IError::show(403,'订单号【'.$val.'】已经被处理过，请查看订单状态');
    		}
		}

		//扣除余额并且记录日志
		$logObj = new AccountLog();
		$config = array(
			'user_id'  => $user_id,
			'event'    => 'pay',
			'num'      => $return['total_fee'],
			'order_no' => join(",",$moreOrder),
		);
		$is_success = $logObj->write($config);
		if(!$is_success)
		{
			IError::show(403,'用户余额更新失败');
		}

		//更新订单状态
		foreach($moreOrder as $key => $val)
		{
			$order_id = Order_Class::updateOrderStatus($val);
    		if(!$order_id)
    		{
    			IError::show(403,'订单号【'.$val.'】更新失败');
    		}
		}
		$return['is_success'] = $is_success ? 'T' : 'F';
    	ksort($return);

    	//返还的URL地址
		$responseUrl = '';
		foreach($return as $key => $val)
		{
			$responseUrl .= $key.'='.urlencode($val).'&';
		}
		$nextUrl = urldecode($return['return_url']);
		if(stripos($nextUrl,'?') === false)
		{
			$return_url = $nextUrl.'?'.$responseUrl;
		}
		else
		{
			$return_url = $nextUrl.'&'.$responseUrl;
		}

		//计算要发送的md5校验
		$encryptKey = isset(IWeb::$app->config['encryptKey']) ? IWeb::$app->config['encryptKey'] : 'iwebshop';
		$urlStrMD5  = md5($responseUrl.$user_id.$pkey.$encryptKey);

		//拼接进返还的URL中
		$return_url.= 'sign='.$urlStrMD5;
    	header('location:'.$return_url);
    }

    // 我的招标列表
    public function zhaobiao_list() {
        $this->redirect('zhaobiao_list');
    }


    // 投标给他
    public function zhaobiao_toubiao() {
        $result = array('status' => 1, 'info'    => '竞标成功');
        $id      = IFilter::act( IReq::get('id'),'int');
        $company_id      = IFilter::act( IReq::get('company_id'),'int');

        $zhaobiao = new IModel('zhaobiao');
        if (!$zhaobiao_info = $zhaobiao->getObj('id = ' . $id)) {
            die(JSON::encode((array('status' => 0, 'info'    => '招标不存在'))));
        }

        if ($zhaobiao_info['status'] != 1) {
            die(JSON::encode((array('status' => 0, 'info'    => '招标状态不合法'))));
        }

        $company = new IModel('jiaju_company');
        if (!$company->getObj('id = ' . $company_id)) {
            die(JSON::encode((array('status' => 0, 'info'    => '公司不存在'))));
        }
        
        $arr = array(
            'status' => 2,
            'company_id' => $company_id,
            'completion_time' => ITime::getDateTime(),
        );
        $zhaobiao->setData($arr);
        if (!$zhaobiao->update('id = ' . $id)) {
            $result = array('status' => 0, 'info'    => '竞标失败');
        }
        echo JSON::encode($result);
    }

    // ……
    public function zhaobiao_toubiao_() {

    }

}