<?php
/**
 * @file wechat.php
 * @brief 微信API接口
 * @date 2015/3/27 21:59:41
 * @version 3.1
 */
class wechat
{
	//微信API地址
	const SERVER_URL = "https://api.weixin.qq.com/cgi-bin";

	//配置数组
	public $config = array(
		'wechat_Token'     => '',
		'wechat_AppID'     => '',
		'wechat_AppSecret' => '',
		'wechat_AutoLogin' => '',
	);

	//令牌存活时间
	private static $accessTokenTime = 3000;
	//jsapi_ticket 存活时间
	private static $jsapiTicketTime = 7000;

	//构造函数
	public function __construct()
	{
		//缺少SSL组件
		if(!extension_loaded("OpenSSL"))
		{
			throw new IException("您的环境缺少OpenSSL组件，这是调用微信API所必须的");
		}
		//获取参数配置
		$siteConfigObj = new Config("site_config");
		//var_dump($siteConfigObj->wechat_Token);
		if($siteConfigObj->wechat_Token && $siteConfigObj->wechat_AppID && $siteConfigObj->wechat_AppSecret)
		{
			$this->config['wechat_Token']     = $siteConfigObj->wechat_Token;
			$this->config['wechat_AppID']     = $siteConfigObj->wechat_AppID;
			$this->config['wechat_AppSecret'] = $siteConfigObj->wechat_AppSecret;
			$this->config['wechat_AutoLogin'] = $siteConfigObj->wechat_AutoLogin;
		}
		else
		{
			throw new IException("微信配置信息不完全，参数<TOKEN>,<AppID>,<AppSecret>必须填写完整");
		}

	}

	/**
	 * @brief 获取access_token令牌
	 * @param boolean $fresh 是否刷新令牌
	 */
	public function getAccessToken($fresh = false)
	{
		$cacheObj = new ICache();
		$accessTokenTime = $cacheObj->get('accessTokenTime');

		//延续使用
		if($accessTokenTime && time() - $accessTokenTime < self::$accessTokenTime && $fresh == false)
		{
			$accessToken = $cacheObj->get('accessToken');
			if($accessToken)
			{
				return $accessToken;
			}
			else
			{
				$cacheObj->del('accessTokenTime');
				$this->getAccessToken();
			}
		}
		//重新获取令牌
		else
		{
			$urlparam = array(
				'grant_type=client_credential',
				'appid='.$this->config['wechat_AppID'],
				'secret='.$this->config['wechat_AppSecret'],
			);
			$apiUrl = self::SERVER_URL."/token?".join("&",$urlparam);
			$json   = file_get_contents($apiUrl);
			$result = JSON::decode($json);
			if($result && isset($result['access_token']) && isset($result['expires_in']))
			{
				self::$accessTokenTime = $result['expires_in'];
				$cacheObj->set('accessTokenTime',time());
				$cacheObj->set('accessToken',$result['access_token']);
				return $result['access_token'];
			}
			else
			{
				die('令牌获取错误！');
			}
		}
	}

	//获取openid
	public function getOpenId()
	{
		return ISafe::get('wechat_openid');
	}

	//设置openid
	public function setOpenId($openid)
	{
		ISafe::set('wechat_openid',$openid);
	}

    //处理微信服务器的请求接口
    public function response()
    {
    	// exit;
    	$code  = IReq::get('code');
    	$state = IReq::get('state');

    	//oauth回调处理
    	if($code && $state)
    	{
			$result = $this->getOauthAccessToken($code);
			if($result)
			{
				//保存openid为其他wechat应用使用
				$this->setOpenId($result['openid']);

				//是否自动登录
				if($this->config['wechat_AutoLogin'] == 1)
				{
					$this->bindUser($result);
					$this->login($result);
				}
				header('location: '.urldecode($state));
			}
    	}
    	else
    	{
			//微信推送处理
	    	if($this->checkSignature())
	    	{
		    	//第一次验证
		    	if($echostr = IReq::get('echostr'))
		    	{
					die($echostr);
		    	}
		    	//相应其他的请求
		    	else
		    	{
		    		$postXML = file_get_contents("php://input");
		    		//微信推送的post数据信息
		    		if($postXML)
		    		{
						$postObj = simplexml_load_string($postXML, 'SimpleXMLElement', LIBXML_NOCDATA);
						//事件推送
						if(isset($postObj->Event))
						{
							switch($postObj->Event)
							{
								//开始订阅
								case "subscribe":
								{

								}
								break;

								//取消订阅
								case "unsubscribe":
								{

								}
								break;

								//网页跳转事件
								case "VIEW":
								{

								}
								break;
							}
						}
						//用户普通消息接收
						else if(isset($postObj->MsgId))
						{
							switch($postObj->MsgType)
							{
								//自动回复
								default:
								{
									$this->textReplay("欢迎光临我们的微商城了解更多商品信息",$postObj);
								}
							}
						}
		    		}
		    	}
		    	die('success');
	    	}
	    	else
	    	{
	    		die('本次请求非微信客户端发起');
	    	}
    	}
    }

	/**
	 * @brief 微信文字类型回复
	 * @param object 微信推送过来的数据信息
	 */
    public function textReplay($content,$postObj)
    {
		$replyContent = "<xml><ToUserName><![CDATA[{$postObj->FromUserName}]]></ToUserName><FromUserName><![CDATA[{$postObj->ToUserName}]]></FromUserName><CreateTime>".time()."</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA[{$content}]]></Content></xml>";
		die($replyContent);
    }

	/**
	 * @brief 提交信息
	 * @param string $submitUrl 提交的URL
	 * @param array $postData 提交数据
	 * @return string 返回的结果字符串
	 */
    public function submit($submitUrl,$postData = null)
    {
		//提交菜单
		$curl = curl_init($submitUrl);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
		curl_setopt($curl, CURLOPT_HEADER, 0); // 过滤HTTP头
		curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
		curl_setopt($curl,CURLOPT_POST,true); // post传输数据
		curl_setopt($curl,CURLOPT_POSTFIELDS,$postData);// post传输数据
		$responseText = curl_exec($curl);
		curl_close($curl);
		return $responseText;
    }

	/**
	 * 验证推送消息真实性
	 * @return boolean true or false
	 */
	public function checkSignature()
	{
        $signature = IReq::get('signature');
        $timestamp = IReq::get('timestamp');
        $nonce     = IReq::get('nonce');

		$tmpArr = array($this->config['wechat_Token'],$timestamp,$nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = sha1(join($tmpArr));

		if($tmpStr == $signature)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @brief 根据code获取oauth登录令牌和openid
	 * @param string $code
	 */
	private function getOauthAccessToken($code)
	{
		$urlparam = array(
			'appid='.$this->config['wechat_AppID'],
			'secret='.$this->config['wechat_AppSecret'],
			'code='.$code,
			'grant_type=authorization_code',
		);
		$apiUrl = "https://api.weixin.qq.com/sns/oauth2/access_token?".join("&",$urlparam);
		$json   = file_get_contents($apiUrl);
		$result = JSON::decode($json);
		if(!isset($result['openid']))
		{
			throw new IException("根据code值未获取到open_id码_{$json}");
		}
		return $result;
	}

	/**
	 * @brief 更新菜单
	 * @param json $menuData 菜单数据 {"button":[{"name":"名称","sub_button":[{"type":"view","name":"子菜单名称","url":"http://www.com"}]}]}
	 * @return array("errcode" => 0,"errmsg" => "ok")
	 */
	public function setMenu($menuData)
	{
		//URL静默登录替换处理
		$menuData = $this->urlCallback($menuData);
		$accessToken = $this->getAccessToken();
		$urlparam = array(
			'access_token='.$accessToken,
		);
		$apiUrl = self::SERVER_URL."/menu/create?".join("&",$urlparam);
		$json   = $this->submit($apiUrl,$menuData);
		$result = JSON::decode($json);
		return $result;
	}

	//URL静默登录替换处理
	private function urlCallback($menuData)
	{
		return preg_replace_callback('|(?<="url":").*?(?=")|',array($this,"converUrl"),$menuData);
	}

	/**
	 * @brief 获取自定义菜单
	 * @return array
	 */
	public function getMenu()
	{
		$accessToken = $this->getAccessToken();
		$urlparam = array(
			'access_token='.$accessToken,
		);
		$apiUrl = self::SERVER_URL."/menu/get?".join("&",$urlparam);
		$json   = file_get_contents($apiUrl);
		$result = JSON::decode($json);
		return isset($result['menu']) ? $result['menu'] : null;
	}

	/**
	 * @brief 转换URL
	 * @param string $url 跳转的URL参数
	 */
	public function converUrl($url)
	{
		//preg_replace_callback 回调的是数组参数
		if(is_array($url))
		{
			foreach($url as $key => $val)
			{
				return $this->converUrl($val);
			}
		}
		else
		{
			//伪静态路径
			if(strpos($url,"/") === 0)
			{
				return IUrl::getHost().IUrl::creatUrl($url);
			}
			return $url;
		}
	}

	//获取oauth登录的回调
	public function getOauthCallback()
	{
		return IUrl::getHost().IUrl::creatUrl("/block/wechat");
	}

	/**
	 * @brief 绑定微信账号到用户系统
	 * @param array $userData array(headimgurl,nickname,openid)
	 */
	public function bindUser($userData)
	{
		if(!isset($userData['openid']))
		{
			throw new IException("未获取到用户的OPENID数据");
		}

		$tempDB   = new IModel('oauth_user as ou,user as u');
		$oauthRow = $tempDB->getObj("ou.oauth_user_id = '".$userData['openid']."' and ou.oauth_id = 5 and ou.user_id = u.id");

		//用户数据不存在
		if(!$oauthRow)
		{
			
			$userDB    = new IModel('user');
			$username  = "wx".strtolower($userData['openid']);//substr($userData['openid'],-8);
	    	$userCount = $userDB->getObj("username = '{$username}' ",'count(*) as num');

	    	//没有重复的用户名
	    	if($userCount['num'] == 0)
	    	{

	    	}
	    	else
	    	{
	    		//随即分配一个用户名
	    		$username = "wx".$username.rand(1000,9999);
	    	}

			//插入user表
			$userDB->setData(array(
				'username' => $username,
				'password' => md5($username)//md5(time()),
			));
			$user_id = $userDB->add();

			//插入member表
			$memberDB = new IModel('member');
			$memberDB->setData(array(
				'user_id' => $user_id,
				'true_name' => '微信用户',
				'time'    => ITime::getDateTime(),
			));
			$memberDB->add();

			//插入oauth_user关系表
			$oauthUserDB = new IModel('oauth_user');

			// 如有其它绑定，那就直接删除绑定解绑了
			$oauthUserDB->del("oauth_user_id = '".$userData['openid']."'");

			$oauthUserData = array(
				'oauth_user_id' => $userData['openid'],
				'oauth_id'      => 5,
				'user_id'       => $user_id,
				'datetime'      => ITime::getDateTime(),
			);
			$oauthUserDB->setData($oauthUserData);
			$oauthUserDB->add();
		}
	}

	/**
	 * @brief 登录用户系统
	 * @param array $userData array(headimgurl,nickname,openid)
	 */
	public function login($userData)
	{
		$oauthUserDB = new IModel('oauth_user');
		$oauthRow = $oauthUserDB->getObj("oauth_user_id = '".$userData['openid']."' and oauth_id = 5");
		$userRow  = array();
		if($oauthRow)
		{
			$userDB = new IModel('user');
			$userRow = $userDB->getObj('id = '.$oauthRow['user_id']);
		}

		if(!$userRow)
		{
			$oauthUserDB->del("oauth_user_id = '".$userData['openid']."' and oauth_id = 5");
			die('无法获取微信用户与商城的绑定信息，请重新关注公众账号');
		}

		$user = CheckRights::isValidUser($userRow['username'],$userRow['password']);
		if($user)
		{
			CheckRights::loginAfter($user);
		}
		else
		{
			die('<h1>该用户'.$userRow['username'].'被锁定无法进行登录，请联系管理员开通</h1>');
		}
	}

	/**
	 * @breif oauth路径处理
	 * @param string $url 网址路径
	 * @return string 处理后oauth的URL
	 */
	public function oauthUrl($url)
	{
		$url = $this->converUrl($url);
		$urlparam = array(
			'appid='.$this->config['wechat_AppID'],
			'redirect_uri='.urlencode($this->getOauthCallback()),
			'response_type=code',
			'scope=snsapi_base',
			'state='.urlencode($url),
		);
		$apiUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?".join("&",$urlparam)."#wechat_redirect";
		return $apiUrl;
	}
	/**
	 * @breif 微信JSSDK 配置信息
	 * @param string $url 网址路径
	 * @return string 处理后oauth的URL
	 */
	public function jweixin($debug){
		$wxpara = array(
			"jsapi_ticket" => $this->getJsapiTicket(),
			"nonceStr" => "".rand(100000,999999)."",
			"timestamp" => time(),
			"url" => IUrl::getUrl()
		);
		//var_dump($wxpara);
		//生成签名结果
		$mysign = $this->buildMysign($wxpara);
		//输出JSSDKAPI
		$wxconfig = array(
			"debug" => $debug, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
			"appId" => $this->config['wechat_AppID'], // 必填，公众号的唯一标识
			"timestamp" => $wxpara['timestamp'], // 必填，生成签名的时间戳
			"nonceStr" => $wxpara['nonceStr'], // 必填，生成签名的随机串
			"signature" => $mysign, // 必填，签名，见附录1
			"jsApiList" => array(
					"onMenuShareAppMessage",
					"onMenuShareTimeline",
					"onMenuShareQQ",
					"onMenuShareWeibo",
					"onMenuShareQZone"
			)  // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
		);
		$wxMenuShareAPI = array(
			"ap",//指定人
			"tl",//朋友圈
			"qq",//QQ
			"wb",//微博
			"qz"//Q空间
		);
		//$jsApiList = "if(typeof wxshare !='undefined'){wxshare.title += ' - 淘黄州'};";
		foreach($wxconfig["jsApiList"] as $key => $val)
		{
			//$jsApiList.=strtolower($key)."=".$val."&";
			$jsApiList.= "wx." . $val."(wxshare_" . $wxMenuShareAPI[$key] . ");";
			$return .= "if(typeof wxshare_" . $wxMenuShareAPI[$key] . " =='undefined'){var wxshare_" . $wxMenuShareAPI[$key] . " = wxshare};";
		}

		$return .= "wx.config(". JSON::encode($wxconfig) .");";
		$return .= "wx.ready(function(){" .$jsApiList .";});";
		return $return;
	}
	//获取$jsapi_Ticket
	public function getJsapiTicket($fresh = false){
		$cacheObj = new ICache();
		$jsapiTicketTime = $cacheObj->get('jsapiTicketTime');

		//延续使用 jsapiTicketTime
		if($jsapiTicketTime && time() - $jsapiTicketTime < self::$jsapiTicketTime && $fresh == false)
		{
			$jsapiTicket = $cacheObj->get('ticket');
			if($jsapiTicket)
			{
				return $jsapiTicket;
			}
			else
			{
				$cacheObj->del('jsapiTicketTime');
				$this->getJsapiTicket();
			}
		}
		//重新获取
		else
		{
			$urlparam = array(
				'access_token='.$this->getAccessToken(),
				'type=jsapi',
			);
			$apiUrl = self::SERVER_URL."/ticket/getticket?".join("&",$urlparam);
			$json   = file_get_contents($apiUrl);
			$result = JSON::decode($json);
			if($result && isset($result['ticket']) && isset($result['expires_in']))
			{
				self::$jsapiTicketTime = $result['expires_in'];
				$cacheObj->set('jsapiTicketTime',time());
				$cacheObj->set('ticket',$result['ticket']);
				return $result['ticket'];
			}
			else
			{
				die('JS_ticket获取错误！');
			}
		}
		
	}

	/**
	 * 生成签名结果
	 * @param $sort_para 要签名的数组
	 * return 签名结果字符串
	 */
	private function buildMysign($sort_para)
	{
		ksort($sort_para);
		reset($sort_para);
		//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
		$prestr = $this->createLinkstring($sort_para);
		//echo $prestr;
		//把最终的字符串签名，获得签名结果
		$mysgin = sha1($prestr);
		return $mysgin;
	}

	/**
	 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
	 * @param $para 需要拼接的数组
	 * return 拼接完成以后的字符串
	 */
	private function createLinkstring($para)
	{
		$arg  = "";
		foreach($para as $key => $val)
		{
			$arg.=strtolower($key)."=".$val."&";
		}

		//去掉最后一个&字符
		$arg = trim($arg,'&');

		//如果存在转义字符，那么去掉转义
		if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc())
		{
			$arg = stripslashes($arg);
		}

		return $arg;
	}

}