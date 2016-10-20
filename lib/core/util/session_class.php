<?php
/**
 * @file session_class.php
 * @brief session机制处理类
 */

//开户session
if( isset($_COOKIE[session_name()]) && $_COOKIE[session_name()] )
{
	session_id($_COOKIE[session_name()]);
}

if(!isset($_SESSION))
{
	session_start();
}
/**
 * @brief ISession 处理类
 * @class ISession
 * @note
 */
class ISession
{
	//session前缀
	private static $pre='iweb_';

	//获取配置的前缀
	private static function getPre()
	{
		return self::$pre;
	}

	/**
	 * @brief 设置session数据
	 * @param string $name 字段名
	 * @param mixed $value 对应字段值
	 */
	public static function set($name,$value='')
	{
		self::$pre = self::getPre();
		if(self::checkSafe()==-1) $_SESSION[self::$pre.'safecode']=self::sessionId();
		$_SESSION[self::$pre.$name]=$value;
	}
    /**
     * @brief 获取session数据
     * @param string $name 字段名
     * @return mixed 对应字段值
     */
	public static function get($name)
	{
		self::$pre  = self::getPre();
		$is_checked = self::checkSafe();

		if($is_checked == 1)
		{
			return isset($_SESSION[self::$pre.$name])?$_SESSION[self::$pre.$name]:null;
		}
		else if($is_checked == 0)
		{
			self::clear(self::$pre.'safecode');
		}
		return null;
	}
    /**
     * @brief 清空某一个Session
     * @param mixed $name 字段名
     */
	public static function clear($name)
	{
		self::$pre = self::getPre();
		unset($_SESSION[self::$pre.$name]);
	}
    /**
     * @brief 清空所有Session
     */
	public static function clearAll()
	{
		return session_destroy();
	}

    /**
     * @brief Session的安全验证
     * @return int 1:通过验证,0:未通过验证
     */
	private static function checkSafe()
	{
		self::$pre = self::getPre();
		if(isset($_SESSION[self::$pre.'safecode']))
		{
			if($_SESSION[self::$pre.'safecode']==self::sessionId())
			{
				return 1;
			}
			else
			{
				return 0;
			}
		}
		else
		{
			return -1;
		}
	}
    /**
     * @brief 得到session安全码
     * @return String  session安全码
     */
	private static function sessionId()
	{
		return md5(filemtime(__FILE__));
	}
}