<?php
/**
 * @file other.php
 * @brief 其他api方法
 * @version 2.8
 */
class APIOther
{
	//获取促销规则
	public function getProrule($seller_id = 0)
	{
		$proRuleObj = new ProRule(999999999,$seller_id);
		$proRuleObj->isGiftOnce = false;
		$proRuleObj->isCashOnce = false;
		return $proRuleObj->getInfo();
	}

	//获取支付方式
	public function getPaymentList()
	{
		$user_id = IWeb::$app->getController()->user['user_id'];
		// var_dump(IWeb::$app->getController()->user);if ($this === IWeb::$app->getController()) echo "控制器中相同";
		$where = 'status = 0';
// var_dump(IWeb::$app->getController()->user);
		if(!$user_id)
		{
			$where .= " and class_name != 'balance'";
		}

		switch(IClient::getDevice())
		{
			//移动支付
			case IClient::MOBILE:
			{
				//如果是微信客户端,必须用微信专用支付
				if(IClient::isWechat() == true)
				{
					$where .= " and (class_name in ( 'wap_wechat','balance' ) or ( type = 2 and client_type in(2,3) )) ";
				}
				else
				{
					$where .= " and client_type in(2,3) and class_name != 'wap_wechat' ";
				}
			}
			break;

			//pc支付
			case IClient::PC:
			{
				$where .= ' and client_type in(1,3) ';
			}
			break;
		}
		$paymentDB = new IModel('payment');
		return $paymentDB->query($where);
	}

	//线上充值的支付方式
	public function getPaymentListByOnline()
	{
		$where = " type = 1 and status = 0 and class_name not in ('balance','offline') ";
		switch(IClient::getDevice())
		{
			//移动支付
			case IClient::MOBILE:
			{
				$where .= ' and client_type in(2,3) ';

				//如果不是微信客户端,去掉微信专用支付
				if(IClient::isWechat() == false)
				{
					$where .= " and class_name != 'wap_wechat'";
				}
			}
			break;

			//pc支付
			case IClient::PC:
			{
				$where .= ' and client_type in(1,3) ';
			}
			break;
		}

		$paymentDB = new IModel('payment');
		return $paymentDB->query($where);
	}
}