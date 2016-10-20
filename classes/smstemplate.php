<?php
/**
 * @file smstemplate.php
 * @brief 短信发送模板
 */
class smsTemplate
{
	//短信模板信息不存在
	public static function __callStatic($funcname, $arguments)
	{
		return "";
	}


	/**
	 * @brief 订单发货的信息模板
	 * @param array $data 替换的数据
	 */
	public static function bill2_notice($data = null)
	{
		$templateString = "您好，管理员（{tel}）{is}了你的结算申请，请知晓，备注：{pay_content}";
		return strtr($templateString,$data);
	}


	/**
	 * @brief 订单发货的信息模板
	 * @param array $data 替换的数据
	 */
	public static function bill_notice($data = null)
	{
		$templateString = "您好，商家{user}（{tel}）申请提现{money}元，请及时处理！";
		return strtr($templateString,$data);
	}


	/**
	 * @brief 订单发货的信息模板
	 * @param array $data 替换的数据
	 */
	public static function voucher_notice($data = null)
	{
		$templateString = "您好{seller}，用户{user}（{tel}）购买{money}元商品，请及时联系并跟进服务！";
		return strtr($templateString,$data);
	}


	/**
	 * @brief 订单发货的信息模板
	 * @param array $data 替换的数据
	 */
	public static function voucher_user_notice($data = null)
	{
		$templateString = "您好{user}，您购买{money}元商品已经付款成功，消费券{voucher_no}，请妥善保存，必要时可向商家出示以验证！";
		return strtr($templateString,$data);
	}


	/**
	 * @brief 订单发货的信息模板
	 * @param array $data 替换的数据
	 */
	public static function sendGoods($data = null)
	{
		// $templateString = "您好{user_name},订单号:{order_no},已由{sendor}发货,物流公司:{delivery_company},物流单号:{delivery_no}";
		$templateString = "【淘黄州】{user}，您的订单({order_no})已发货，快递公司：{exp}，快递单号：{exp_no}，请注意收货。";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 手机找回密码模板
	 * @param array $data 替换的数据
	 */
	public static function findPassword($data = null)
	{
		$templateString = "您的验证码为:{mobile_code},请注意保管!";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 手机短信校验码
	 * @param array $data 替换的数据
	 */
	public static function checkCode($data = null)
	{
		$templateString = "您的验证码为:{mobile_code},请注意保管!";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 自提点确认短信
	 * @param array $data 替换的数据
	 */
	public static function takeself($data = null)
	{
		$templateString = "您的订单号:{orderNo},{name}自提地址:{address},领取验证码:{mobile_code},请尽快领取";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 商户注册提示管理员
	 * @param array $data 替换的数据
	 */
	public static function sellerReg($data = null)
	{
		$templateString = "{true_name},申请加盟到平台,请尽快登录后台进行处理";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 商户处理结果
	 * @param array $data 替换的数据
	 */
	public static function sellerCheck($data = null)
	{
		$templateString = "您申请的加盟商状态已经被修改为:{result}状态,请登录您的商户后台查看具体的详情";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 订单付款通知管理员
	 */
	public static function payFinishToAdmin($data = null)
	{
		$templateString = "商城订单:{orderNo},已经付款,请尽快登录后台进行处理";
		return strtr($templateString,$data);
	}

	/**
	 * @brief 订单付款通知管理员
	 */
	public static function payFinishToUser($data = null)
	{
		$templateString = "您的订单号:{orderNo},已付款成功,稍后我们会尽快为您服务";
		return strtr($templateString,$data);
	}
}