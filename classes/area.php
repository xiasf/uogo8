<?php
/**
 * @file area.php
 * @brief 省市地区调用函数
 * @note
 */

 /**
 * @class area
 * @brief 省市地区调用函数
 */
class area
{
	/**
	 * @brief 根据传入的地域ID获取地域名称，获取的名称是根据ID依次获取的
	 * @param int 地域ID 匿名参数可以多个id
	 * @return array
	 */
	public static function name()
	{
		$result     = array();
		$paramArray = func_get_args();
		$areaDB     = new IModel('areas');
		$areaData   = $areaDB->query("area_id in (".trim(join(',',$paramArray),",").")");

		foreach($areaData as $key => $value)
		{
			$result[$value['area_id']] = $value['area_name'];
		}
		return $result;
	}
}