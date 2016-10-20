<?php
/**
 * @brief 动态生成缩略图类
 */
class Thumb
{
	//缩略图路径
	public static $thumbDir = "runtime/_thumb/";

	/**
	 * @brief 获取缩略图物理路径
	 */
	public static function getThumbDir()
	{
		return IWeb::$app->getBasePath().self::$thumbDir;
	}

	/**
	 * @brief 生成缩略图
	 * @param string $imgSrc 图片路径
	 * @param int $width 图片宽度
	 * @param int $height 图片高度
	 * @return string WEB图片路径名称
	 */
    public static function get($imgSrc,$width=100,$height=100)
    {
    	if($imgSrc == '')
    	{
    		return '';
    	}

		//商品物理实际路径
		$sourcePath = IWeb::$app->getBasePath().$imgSrc;

		//缩略图文件名
		$preThumb      = "{$width}_{$height}_";
		$thumbFileName = $preThumb.basename($imgSrc);

		//缩略图目录
		$thumbDir    = self::getThumbDir().dirname($imgSrc)."/";
		$webThumbDir = self::$thumbDir.dirname($imgSrc)."/";

		if(is_file($thumbDir.$thumbFileName) == false && is_file($sourcePath))
		{
			IImage::thumb($sourcePath,$width,$height,$preThumb,$thumbDir);
		}
		return $webThumbDir.$thumbFileName;
    }
}