<?php
class category {

    /**
     * 无限分类处理
	 * @static
     * @access public
     * @param array 	$cate 	要处理的分类数组
	 * @param int 		$pid  	分类PID
	 * @param string 	$html 	分类标示
	 * @param int 		$level 	分类层级
	 * @return array
	 */
	static public function unlimitedForLevel($cate, $pid = 0, $html = '├─', $level = 0) {
		$arr = array();
		foreach ($cate as $k => $value) {
			if ($value['parent_id'] == $pid) {
                unset($cate[$k]);
				$value['level'] = $level + 1;
				$value['html'] = str_repeat('　', $level) . $html;
				$arr[] = $value;
				$arr = array_merge($arr, self::unlimitedForLevel($cate, $value['id'], $html, $value['level']));
			}
		}
		return $arr;
	}
}