<?php
/**
 * @brief 公共方法集合
 * @class Common
 * @note  公开方法集合适用于整个系统
 */
class Common
{
	/**
	 * @brief 获取语言包,主题,皮肤的方案
	 * @param string $type  方案类型: theme:主题; skin:皮肤; lang:语言包;
	 * @param string $theme 此参数只有$type为skin时才有用，获取任意theme下的skin方案;
	 * @return string 方案的路径
	 */
	public static function getSitePlan($type,$theme = null)
	{
		$except      = array('.','..','.svn','.htaccess');
		$defaultConf = 'config.php';
		$planPath    = null;    //资源方案的路径
		$planList    = array(); //方案列表
		$configKey   = array('name','version','author','time','thumb','info','type');

		//根据不同的类型设置方案路径
		switch($type)
		{
			case "theme":
			{
				$planPath = IWeb::$app->getViewPath();
				$webPath  = IWeb::$app->getWebViewPath();
			}
			break;

			case "skin":
			{
				$planPath = IWeb::$app->getViewPath().$theme."/".IWeb::$app->defaultSkinDir."/";
				$webPath  = IWeb::$app->getWebViewPath().$theme."/".IWeb::$app->defaultSkinDir."/";
			}
			break;

			case "lang":
			{
				$planPath = IWeb::$app->getLanguagePath();
			}
			break;
		}

		if($planPath && is_dir($planPath))
		{
			$planList = array();
			$dirRes   = opendir($planPath);

			//遍历目录读取配置文件
			while($dir = readdir($dirRes))
			{
				if(!in_array($dir,$except))
				{
					$fileName = $planPath.'/'.$dir.'/'.$defaultConf;
					$tempData = file_exists($fileName) ? include($fileName) : array();
					if($tempData)
					{
						//拼接系统所需数据
						foreach($configKey as $val)
						{
							if(!isset($tempData[$val]))
							{
								$tempData[$val] = '';
							}
						}

						//缩略图拼接路径
						if(isset($tempData['thumb']) && isset($webPath))
						{
							$tempData['thumb'] = $webPath.$dir.'/'.$tempData['thumb'];
						}
						$planList[$dir] = $tempData;
					}
				}
			}
		}
		return $planList;
	}

	/**
	 * @brief 检查主题方案是否被应用
	 * @param string $plan 方案名称
	 * @return boolean
	 */
	public static function isThemeUsed($plan)
	{
		if(isset(IWeb::$app->config['theme']))
		{
			if(is_array(IWeb::$app->config['theme']))
			{
				foreach(IWeb::$app->config['theme'] as $client => $themeList)
				{
					if(array_key_exists($plan,$themeList))
					{
						return $themeList;
					}
				}
			}
		}
		return false;
	}

	/**
	 * @brief 检查皮肤方案是否被应用
	 * @param string $theme 主题名称
	 * @param string $plan 方案名称
	 * @return boolean
	 */
	public static function isSkinUsed($theme,$plan)
	{
		$themeList = self::isThemeUsed($theme);
		if($themeList)
		{
			if(in_array($plan,$themeList))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * @brief 获取评价分数
	 * @param $grade float 分数
	 * @param $comments int 评论次数
	 * @return float
	 */
	public static function gradeWidth($grade,$comments = 1)
	{
		return $comments == 0 ? 0 : 14*($grade/$comments);
	}

	/**
	 * @brief 获取用户状态
	 * @param $status int 状态代码
	 * @return string
	 */
	public static function userStatusText($status)
	{
		$mapping = array('1' => '正常','2' => '删除','3' => '锁定');
		return isset($mapping[$status]) ? $mapping[$status] : '';
	}

    //[账户余额] 提现状态判定
    public static function getWithdrawStatus($status)
    {
    	$data = array(
    		'0'  => '未处理',
    		'-1' => '提现失败',
    		'1'  => '处理中',
    		'2'  => '提现成功',
    	);
    	return isset($data[$status]) ? $data[$status] : '未知状态';
    }

	/**
	 * @brief 获取主题方案的类型
	 * @param string $theme 主题方案
	 * @return 主题类型编号; 网站前台:site;后台管理:system;商家管理:seller;
	 */
    public static function themeType($theme)
    {
    	$list = array("site","system","seller");
    	foreach($list as $key => $checkController)
    	{
    		$dirname = IWeb::$app->getViewPath().$theme."/".$checkController;
    		if(is_dir($dirname))
    		{
				return $checkController;
    		}
    	}
    	return "";
    }

	/**
	 * @brief 根据主题类型编号返回名字字符串
	 * @param string $type 主题类型名字
	 * @return string 主题名称名字字符串
	 */
    public static function themeTypeTxt($type)
    {
    	$data = array("site" => "网站前台","system" => "后台管理","seller" => "商家管理");
    	return isset($data[$type]) ? $data[$type] : "";
    }
}