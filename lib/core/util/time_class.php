<?php
/**
 * @file time_class.php
 * @brief 时间处理
 */

/**
 * @class ITime
 * @brief ITime 时间处理类
 * @note
 */
class ITime
{

	/**
	 * @brief 获取当前时间
	 * @param String  $format  返回的时间格式，默认返回当前时间的时间戳
	 * @return String $time    时间
	 */
	public static function getNow($format='')
	{
		if($format)
		{
			return self::getDateTime($format);
		}
		return self::getTime();
	}


	public static function time1($time) {
			$y = $y = 0;			//初始化			
			$m = $m = 0;			//初始化			
			$d = $d = 0;			//初始化			
			$h = $h = 0;			//初始化			
			$i = $i = 0;			//初始化			
			$s = $s = 0;			//初始化		

			$y1 = $time % 31536000;		//余月不足年(1~11月)
			$y2 = $y1 % 2592000;		//余日不足月(1~29天)	
			$y3 = $y2 % 86400;			//余时不足日(1~23小时)
			$y4 = $y3 % 3600;			//余分不足时(1~59分)
			$y5 = $y4 % 60;				//余秒不足分(0~59秒)

		if($time >= 31536000)
			$y = (int)($time / 31536000); 	//整除年

		if($time >= 2592000)
			$m = (int)($y1 / 2592000); 		//整除月

		if($time >= 86400)
			$d = (int)($y2 / 86400);			//整除日

		if($time >= 3600)
			$h = (int)($y3 / 3600);			//整除时

		if($y4 >= 60)
			$i = (int)($y4 / 60);				//整除分

		$s = $y5;								//秒

		if($y == 0){			//无年
			if($m == 0) {		//无月
				$str = $d.'天 '.$h.'小时 '.$i.'分 '.$s.'秒';		//1~29天1~23小时1~59分0~59秒
			} else {	// 有月
				return $m.'月 '.$d.'天 '.$h.'小时 '.$i.'分 '.$s.'秒';		//1~11月1~29天1~23小时1~59分0~59秒
			} 

			if($d == 0)		//无天
				$str = $h.'小时 '.$i.'分 '.$s.'秒';		//1~23小时1~59分0~59秒
			if($h == 0)		//无时
				$str = $i.'分 '.$s.'秒';		//1~59分 . 0~59秒
			if($i == 0 && $s >= 0)		//无分有秒
				$str = $s.'秒';	 	//0~59秒

		}else 		//*年//1~11月1~29天1~23小时1~59分0~59秒
				$str = $y.'年 '.$m.'月 '.$d.'天 '.$h.'小时 '.$i.'分 '.$s.'秒';

		return $str;	//返回格式化输出
	}


	/**
	 * @brief  根据指定的格式输出时间
	 * @param  String  $format 格式为年-月-日 时:分：秒,如‘Y-m-d H:i:s’
	 * @param  String  $time   输入的时间
	 * @return String  $time   时间
	 */
	public static function getDateTime($format='',$time='')
	{
		$time   = $time   ? $time   : time();
		$format = $format ? $format : 'Y-m-d H:i:s';
		return date($format,$time);
	}

	/**
	 * @brief  根据输入的时间返回时间戳
	 * @param  $time String 输入的时间，格式为年-月-日 时:分：秒,如2010-01-01 00:00:00
	 * @return $time Int 指定时间的时间戳
	 */
	public static function getTime($time='')
	{
		if($time)
		{
			return strtotime($time);
		}
		return time();
	}

	/**
	 * @brief 获取第一个时间与第二个时间之间相差的秒数
	 * @param $first_time  String 第一个时间 格式为英文时间格式，如2010-01-01 00:00:00
	 * @param $second_time String 第二个时间 格式为英文时间格式，如2010-01-01 00:00:00
	 * @return $difference Int 时间差，单位是秒
	 * @note  如果第一个时间早于第二个时间，则会返回负数
	 */
	public static function getDiffSec($first_time,$second_time='')
	{
		$second_time = $second_time ? $second_time : self::getDateTime();
		$difference  = strtotime($first_time) - strtotime($second_time);
		return $difference;
	}

	/**
	 * @brief 获取过去或未来的时间
	 * @param $second  string 差秒数
	 * @param $format  string 日期时间格式
	 * @param $time    int    时间戳
	 * @param string   返回日期时间
	 */
	public static function pass($interval_spec,$format = 'Y-m-d H:i:s',$time = '')
	{
		$datetime   = self::getDateTime($format,$time);
		$dateObject = new DateTime($datetime);

		if($interval_spec > 0)
		{
			$dateObject->add(new DateInterval('PT'.$interval_spec.'S'));
		}
		else
		{
			$dateObject->sub(new DateInterval('PT'.abs($interval_spec).'S'));
		}
		return $dateObject->format($format);
	}
	/**
	 * @brief 获取多久以前
	 * @param $format  string 日期时间格式
	 * @param string   返回字符串
	 */
	public static function tran($time = '')
	{
		$t=time()-$time;
		if($t<1)return "刚刚";
		$f=array(
			'31536000'=>'年',
			'2592000'=>'个月',
			'604800'=>'星期',
			'86400'=>'天',
			'3600'=>'小时',
			'60'=>'分钟',
			'1'=>'秒'
		);
		foreach ($f as $k=>$v)    {
			if (0 !=$c=floor($t/(int)$k)) {
				return $c.$v.'前';
			}
		}
	}
}