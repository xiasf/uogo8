<?php
/**
 * @file browse.php
 * @brief 关于在线统计
 */
class Browse{
	
	
	//获取浏览器
	function getBrowse()
	{
		global $_SERVER;
		$Agent = $_SERVER['HTTP_USER_AGENT'];
		$browseinfo='';
		if(ereg('Mozilla', $Agent) && !ereg('MSIE', $Agent)){
			$browseinfo = 'Netscape Navigator';
		}
		if(ereg('Opera', $Agent)) {
			$browseinfo = 'Opera';
		}
		if(ereg('Mozilla', $Agent) && ereg('MSIE', $Agent)){
	
			$browseinfo = 'Internet Explorer';
		}
		if(ereg('Chrome', $Agent)){
			$browseinfo="Chrome";
		}
		if(ereg('Safari', $Agent)){
			$browseinfo="Safari";
		}
		if(ereg('Firefox', $Agent)){
			$browseinfo="Firefox";
		}
	
		return $browseinfo;
	}
	//获取用户系统
	function getOS ()
	{
		global $_SERVER;
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$os = false;
		if (eregi('win', $agent) && strpos($agent, '95')){
			$os = 'Windows 95';
		}elseif (eregi('win 9x', $agent) && strpos($agent, '4.90')){
			$os = 'Windows ME';
		}elseif (eregi('win', $agent) && ereg('98', $agent)){
			$os = 'Windows 98';
		}elseif (eregi('win', $agent) && eregi('nt 5.1', $agent)){
			$os = 'Windows XP';
		}elseif (eregi('win', $agent) && eregi('nt 5.2', $agent)){    
			$os = 'Windows 2003';
		}elseif (eregi('win', $agent) && eregi('nt 5', $agent)){
			$os = 'Windows 2000';
		}elseif (eregi('win', $agent) && eregi('nt', $agent)){
			$os = 'Windows NT';
		}elseif (eregi('win', $agent) && ereg('32', $agent)){
			$os = 'Windows 32';
		}elseif (eregi('linux', $agent)){
			$os = 'Linux';
		}elseif (eregi('unix', $agent)){
			$os = 'Unix';
		}elseif (eregi('sun', $agent) && eregi('os', $agent)){
			$os = 'SunOS';
		}elseif (eregi('ibm', $agent) && eregi('os', $agent)){
			$os = 'IBM OS/2';
		}elseif (eregi('Mac', $agent) && eregi('PC', $agent)){
			$os = 'Macintosh';
		}elseif (eregi('PowerPC', $agent)){
			$os = 'PowerPC';
		}elseif (eregi('AIX', $agent)){
			$os = 'AIX';
		}elseif (eregi('HPUX', $agent)){
			$os = 'HPUX';
		}elseif (eregi('NetBSD', $agent)){
			$os = 'NetBSD';
		}elseif (eregi('BSD', $agent)){
			$os = 'BSD';
		}elseif (ereg('OSF1', $agent)){
			$os = 'OSF1';
		}elseif (ereg('IRIX', $agent)){
			$os = 'IRIX';
		}elseif (eregi('FreeBSD', $agent)){
			$os = 'FreeBSD';
		}elseif (eregi('teleport', $agent)){
			$os = 'teleport';
		}elseif (eregi('flashget', $agent)){
			$os = 'flashget';
		}elseif (eregi('webzip', $agent)){
			$os = 'webzip';
		}elseif (eregi('offline', $agent)){
			$os = 'offline';
		}else{
			$os = '未知';
		}
		return $os;
	}
	function getMobileOS(){
		global $_SERVER;
		$Agent = $_SERVER['HTTP_USER_AGENT'];//返回手机系统、型号信息
		$os = '模拟';
		if(stristr($Agent,'Android')) {//返回值中是否有Android这个关键字
			$os = 'Android';
		}else if(stristr($Agent,'iPhone')){
			$os = 'iPhone';
		}else if(stristr($Agent,'Windows Phone')){
			$os = 'Windows Phone';
		};
		return $os;
	}
	function getMobileBrowse(){
		$browseinfo = "浏览器";
		if($this->config["iswechat"])$browseinfo = "微信";
		return $browseinfo;
	}
	function getUserKey(){
		$userKey = ICookie::get("userkey");
		if(empty($userKey)){
			$chars='abcdefghijklmnopqrstuvwxyz0123456789'; 
			mt_srand((double)microtime()*1000000*getmypid()); 
			$rndStr=''; 
			while(strlen($rndStr)<11)$rndStr.=substr($chars,(mt_rand()%strlen($chars)),1); 
			$userKey = time() .$rndStr;
			ICookie::set("userkey",$userKey,3650);
		};
		return $userKey;
	}
	function isRobots(){
	//定义搜索引擎蜘蛛
		$robots = array("baiduspider","googlebot","sosospider","360spider","slurp","yodaobot","sogou","msnbot","bingbot","ahrefsbot","yisouspider");
		$is_spider=false;//访问是否来自搜索引擎蜘蛛
		 
		$agent= strtolower($_SERVER["HTTP_USER_AGENT"]);//获取访问者浏览器相关参数
		 
		//搜索引擎蜘蛛爬虫判断
		foreach($robots as $user_agent){
			if(ereg($user_agent,$agent))//蜘蛛
			{
				$is_spider=true;
			}  
		}
		return $is_spider;
	
	}
	function getIpToAddress($ip){
		$content = file_get_contents("http://api.map.baidu.com/location/ip?ak=C93b5178d7a8ebdb830b9b557abce78b&ip={$ip}&coor=bd09ll");
		$json = json_decode($content);
		$address = array(
			"log" => $json->{'content'}->{'point'}->{'x'},
			"lat" => $json->{'content'}->{'point'}->{'y'},
			"address" => $json->{'content'}->{'address'}
		);
		return $address;
		//echo 'log:',$json->{'content'}->{'point'}->{'x'};//按层级关系提取经度数据
		//echo '<br/>';
		//echo 'lat:',$json->{'content'}->{'point'}->{'y'};//按层级关系提取纬度数据
		//echo '<br/>';
		//print $json->{'content'}->{'address'};//按层级关系提取address数据*/
		//var_dump($Client);
	
	}
	//在线统计
	function online(){
		if(self::isRobots())return false;
		$userID = empty($this->user["user_id"])?0:$this->user["user_id"];
		$Client = array(
			"userKey" => self::getUserKey(),
			"userID" => $userID,
			"userPreUrl"=> IClient::getPreUrl(),
			"userUrl"=> IUrl::getUrl(),
			"userIP" => IClient::getIp(),
			"ClientUseragent"=>$_SERVER['HTTP_USER_AGENT'],
			"ClientType"=>"电脑",
			"ClientOS"=>self::getOS(),
			"ClientBrowse"=>self::getBrowse(),
			"datetime" => ITime::getDateTime()
		);
		$this->user["user_key"] = $Client["userKey"];
		if(IClient::getDevice() == "mobile"){
			$Client["ClientType"] = "手机";
			$Client["ClientOS"]   = self::getMobileOS();
			$Client["ClientBrowse"] = self::getMobileBrowse();
		}
		$addBrowse = new IModel('browse');
		$addBrowse->setData($Client);
		$addBrowse->add();
		$bro_online = new IModel('browse_online');
		$where = " userKey ='". $Client["userKey"] ."' ";
		$hasbro = $bro_online->getObj($where);
		$Client["lastdate"] = ITime::getDateTime();
		if($hasbro)
		{
			unset($Client["datetime"]); 
			$bro_online->setData($Client);
			$bro_online->update($where);
		}
		else
		{
			$bro_online->setData($Client);
			$bro_online->add();
		}
		//删除超过一个小时没活动的用户
		$bro_online->del(" now()-lastdate>3600 ");

	}
	
	
	
	
}