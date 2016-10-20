<?php
/**
 * @file ad.php
 * @brief 关于广告管理
 */
class Ad
{
	//是否加载过js
	private static $isLoadJs = false;

	/**
	 * @brief 广告类型展示解析
	 * @param $type int 类型
	 * @return string 类型字符串
	 */
	public static function showType($type)
	{
		switch($type)
		{
			case "1":
			$str = '图片';
			break;

			case "2":
			$str = 'flash';
			break;

			case "3":
			$str = '文字';
			break;

			case "4":
			$str = '代码';
			break;

			case "5":
			$str = '幻灯片';
			break;
		}
		return $str;
	}

	/**
	 * @brief 展示制定广告位的广告内容
	 * @param $position mixed 广告位ID 或者 广告位名称
	 * @param $goods_cat_id 商品分类ID
	 * @return string
	 */
	public static function show($position,$goods_cat_id = 0)
	{
		$positionObject = array();
		$adArray        = array();

		$positionObject = self::getPositionInfo($position);
		if($positionObject)
		{
			$adList = self::getAdList($positionObject['id'],$goods_cat_id);
			foreach($adList as $key => $val)
			{
				$val['width']  = $positionObject['width'];
				$val['height'] = $positionObject['height'];
				$adArray[] = self::display($val);
			}
		}

		//有广告内容数据
		if($adArray)
		{
			$positionJson = JSON::encode($positionObject);
			$adJson       = JSON::encode($adArray);
			$adList       = implode("",$adArray);

			//引入 adloader js类库
/*			$loadJs = '';
			if(self::$isLoadJs == false)
			{
				$loadJs = IJSPackage::load('admanage');
				self::$isLoadJs = true;
			}
*/
			$adPositionJsId = md5("AD_{$position}_{$goods_cat_id}");
			//生成HTML代码{$loadJs}    (new adLoader()).load({$positionJson},{$adJson},'{$adPositionJsId}');
			echo "<ul id='{$adPositionJsId}' class='slidesbox'>{$adList}</ul><script type='text/javascript'>$('#{$adPositionJsId}').ads({auto: true,speed: 1000});</script>";
		}

	}

	/**
	 * @brief 展示广告位数据
	 * @param $adData array 广告数据
	 * @return string
	 */
	private static function display($adData)
	{
		$result = array();
		$link = $adData['link'] ? IUrl::creatUrl($adData['link']) : "";

		switch($adData['type'])
		{
			//图片
			case 1:
			{
				$size = ($adData['width'] > 0 && $adData['height'] > 0) ? 'width:'.$adData['width'].'px;height:'.$adData['height'].'px' : '';
				$result = array
				(
					'type' => 1,
					'data' => '<img src="'.IUrl::creatUrl("").$adData['content'].'" style="'.$size.'" />'
				);
			}
			break;

			//flash
			case 2:
			{
				$size = ($adData['width'] > 0 && $adData['height'] > 0) ? 'width='.$adData['width'].' height='.$adData['height'] : '';
				$result = array
				(
					'type' => 2,
					'data' => '<object style="cursor:pointer;" classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0" '.$size.' hspace="0" vspace="0" border="0" align="left"><param name="movie" value="'.IUrl::creatUrl("").$adData['content'].'"><param name="quality" value="high"><param name="wmode" value="transparent"><param name="scale" value="noborder"><embed src="'.IUrl::creatUrl("").$adData['content'].'" quality="high" wmode="transparent" scale="noborder" '.$size.' hspace="0" vspace="0" border="0" align="left" type="application/x-shockwave-flash" luginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>'
				);
			}
			break;

			//文字
			case 3:
			{
				$result = array
				(
					'type' => 3,
					'data' => '<label>'.$adData['content'].'</label>'
				);
			}
			break;

			//代码
			case 4:
			{
				$result = array
				(
					'type' => 4,
					'data' => '<label>'.$adData['content'].'</label>'
				);
			}
			break;
		}
		$result = '<li><a href="'. $link .'#" target="_blank">'.$result["data"].'</a></li>';
		return $result;
	}

	/**
	 * @brief 获取广告位置的信息
	 * @param $position mixed 广告位ID 或者 广告位名称
	 * @return array
	 */
	public static function getPositionInfo($position)
	{
		$adPositionDB = new IModel("ad_position");
		if(is_int($position))
		{
			return $adPositionDB->getObj("id={$position} AND `status`=1");
		}
		else
		{
			return $adPositionDB->getObj("name='{$position}' AND `status`=1");
		}
	}

	/**
	 * @brief 获取当前时间段正在使用的广告数据
	 * @param $position int 广告位ID
	 * @param $goods_cat_id 商品分类ID
	 * @return array
	 */
	public static function getAdList($position,$goods_cat_id = 0)
	{
		$now    = date("Y-m-d H:i:s",ITime::getNow());
		$adDB   = new IModel("ad_manage");
		return $adDB->query("position_id={$position} and goods_cat_id = {$goods_cat_id} and start_time < '{$now}' AND end_time > '{$now}' ORDER BY `sort` ASC ");
	}
}