<?php
/**
 * @file jspackage_class.php
 * @brief 系统JS包加载类文件
 */
class IJSPackage
{
	//系统JS注册表
	private static $JSPackages = array(
		'jquery' => array(
			'js' => array(
				'jquery/jquery-1.11.3.min.js'
			)
		),
		
		'jqueryext' => array(
			'js' => array(
				'jquery/jquery-migrate-1.2.1.min.js'
			)
		),
		
		'form' => array('js' => 'form/form.js'),

		'dialog' => array(
			'js' => array(
				'artdialog/artDialog.js',
				'artdialog/plugins/iframeTools.js'
			),
			'css' => 'artdialog/skins/aero.css'
		),
		'baidueditor' => array(
			'js' => array(
				'baidueditor/ueditor.config.js',
				'baidueditor/ueditor.all.min.js'
			), 
			//'callback' => 'initKindEditior'  
		),

		'validate' => array(
			'js'=>'autovalidate/validate.js',
			'css'=>'autovalidate/style.css'
		),

		'webuploader' => array(
			'js'=> array('webuploader/webuploader.min.js','webuploader/webuploader.model.js'),
			'css'=>'webuploader/webuploader.css' 
		),

		'my97date' => array('js' => 'my97date/wdatepicker.js'),

		'artTemplate' => array(
			'js' => array(
				'artTemplate/artTemplate.js',
				'artTemplate/artTemplate-plugin.js'
			)
		),
		'cookie' => array('js' => 'cookie/jquery.cookie.js'),

		'chart' => array('js' => 'highcharts/highcharts.js'),
	);

	/**
	 * @brief 加载系统的JS方法
	 * @param $name    string
	 * @param $charset string
	 * @return String
	 */
	public static function load($name,$charset='UTF-8')
	{
		if(!isset(self::$JSPackages[$name]))
		{
			return '';
		}

		$dir       = self::getFileOrDir(self::$JSPackages[$name]);
		$realjspath= IWeb::$app->getRuntimePath().'_systemjs/'.$dir;

		//如果没有创建就开始拷贝文件
		if(!file_exists($realjspath))
		{
			IFile::xcopy(dirname(__FILE__).'/source/'.$dir,$realjspath);
		}

		$webjspath    = IWeb::$app->getWebRunPath().'_systemjs/';
		$resultString = '';
		foreach(self::$JSPackages[$name] as $key => $val)
		{
			switch($key)
			{
				case "js":
				{
					if(is_array($val))
					{
						foreach($val as $file)
						{
							$resultString .= self::getJsHtml($webjspath.$file,$charset);
						}
					}
					else
					{
						$resultString .= self::getJsHtml($webjspath.$val,$charset);
					}
				}
				break;

				case "css":
				{
					if(is_array($val))
					{
						foreach($val as $file)
						{
							$resultString .= self::getCssHtml($webjspath.$file,$charset);
						}
					}
					else
					{
						$resultString .= self::getCssHtml($webjspath.$val,$charset);
					}
				}
				break;

				case "callback":
				{
					$resultString .= call_user_func(array('IJSPackage',$val));
				}
				break;
			}
		}

		return $resultString;
	}

	/**
	 * 获取文件或者目录
	 */
	private static function getFileOrDir($pathInfo)
	{
		if(is_array($pathInfo))
		{
			return self::getFileOrDir(current($pathInfo));
		}
		else
		{
			return dirname($pathInfo);
		}
	}

	/**
	 * @brief 获取JS的html
	 */
	private static function getJsHtml($fileName,$charset)
	{
		return '<script type="text/javascript" charset="'.$charset.'" src="'.$fileName.'"></script>';
	}

	/**
	 * @brief 获取CSS的html
	 */
	private static function getCssHtml($fileName,$charset)
	{
		return '<link rel="stylesheet" type="text/css" href="'.$fileName.'" />';
	}

	/**
	 * @brief 输出脚本
	 */
	private static function getCallback($functionName)
	{
		return '<script type="text/javascript" charset="'.$charset.'">'.$code.'</script>';
	}

	/**
	 * @brief kindeditor的参数设置
	 */
	private static function initKindEditior()
	{
		$result = '<script type="text/javascript">';
		$result.= 'window.KindEditor.options.uploadJson = "'.IUrl::creatUrl('/pic/upload_json').'";';
		$result.= 'window.KindEditor.options.fileManagerJson = "'.IUrl::creatUrl('/pic/file_manager_json').'";';
		$result.= '</script>';
		return $result;
	}
}