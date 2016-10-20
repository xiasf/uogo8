<?php
/**
 * @file yunpian.php
 * @brief **短信发送接口
 */

 /**
 * @class yunpian
 * @brief 短信发送接口 https://sms.yunpian.com/v1/sms/send.json
 */
class yunpian extends hsmsBase
{
	private $submitUrl = "https://sms.yunpian.com/v1/sms/send.json";

	/**
	 * @brief 获取config用户配置
	 * @return array
	 */
	private function getConfig()
	{
		//如果后台没有设置的话，这里手动配置也可以
		$siteConfigObj = new Config("site_config");
		return array(
			'apikey'   => $siteConfigObj->sms_userid
		);
	}

	/**
	 * @brief 发送短信
	 * @param string $mobile
	 * @param string $content
	 * @return
	 */
	public function send($mobile, $content)
	{
		$config = self::getConfig();

		$post_data = array(
			'apikey'   => $config['apikey'],
			'text'     => $content,
			'mobile'   => $mobile,
		);

		$url = $this->submitUrl;

		$post_string = http_build_query($post_data);

		$ch = curl_init();
		/* 设置验证方式 */
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
		/* 设置返回结果为流 (如果需要将结果直接返回到变量里，那加上这句) */
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		/* 设置超时时间*/
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		/* 设置通信方式 */
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, 0);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		$result = curl_exec($ch);
		return $this->response($result);
	}

	/**
	 * @brief 解析结果
	 * @param $result 发送结果
	 * @return string success or fail
	 */
	public function response($result)
	{
		$array = json_decode($result, true);
		if ($array['msg'] == 'OK') {
			return 'success';
		} else {
			return 'fail';
		}
	}

	/**
	 * @brief 配置文件
	 */
	public function getParam()
	{
		return array(
			"apikey"   => "apikey",
		);
	}
}