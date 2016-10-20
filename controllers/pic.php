<?php
/**
 * @file pic.php
 * @brief 图库处理
 */
class Pic extends IController
{
	private static $openAction = array('thumb','img_upload');
	public $layout = '';

	function init()
	{
		if(!in_array(IReq::get('action'),self::$openAction))
		{
			IInterceptor::reg('CheckRights@onCreateAction');
		}
	}
	//规格图片上传
	function uploadFile()
	{
		//上传状态
		$state = false;

		//规格索引值
		$specIndex = IFilter::act(IReq::get('specIndex'));
		if($specIndex === null)
		{
			$message = '没有找到规格索引值';
		}
		else
		{
			//本地上传方式
			if(isset($_FILES['attach']) && $_FILES['attach']['name'][0]!='')
			{
				$photoInfo = $this->upload();
				if($photoInfo['flag']==1)
				{
					$fileName = $photoInfo['dir'].$photoInfo['name'];
					$state = true;
				}
			}

			//远程网络方式
			else if($fileName=IReq::get('outerSrc','post'))
			{
				$state = true;
			}

			//图库选择方式
			else if($fileName=IReq::get('selectPhoto','post'))
			{
				$state = true;
			}
		}

		//根据状态值进行
		if($state == true)
		{
			die("<script type='text/javascript'>parent.art.dialog({id:'addSpecWin'}).iframe.contentWindow.updatePic(".$specIndex.",'".$fileName."');</script>");
		}
		else
		{
			die("<script type='text/javascript'>alert('添加图片失败');window.history.go(-1);</script>");
		}
	}

	//本地上传方式
	function upload()
	{
		//图片上传
		$upObj = new IUpload();

		//目录散列
		$dir = IWeb::$app->config['upload'].'/specphoto/'.date('Y').date('m').date('d');
		$upObj->setDir($dir);
		$upState = $upObj->execute();

		//实例化
		$obj = new IModel('spec_photo');

		//检查上传状态
		foreach($upState['attach'] as $val)
		{
			if($val['flag']==1)
			{
				$insertData = array(
					'address'     => $val['dir'].$val['name'],
					'name'        => $val['ininame'],
					'create_time' => ITime::getDateTime(),
				);
				$obj->setData($insertData);
				$obj->add();
			}
		}
		if(count($upState['attach'])==1)
			return $upState['attach'][0];
		else
			return $upState['attach'];
	}

	//获取图片列表
	function getPhotoList()
	{
		$obj = new IModel('spec_photo');
		$photoRs = $obj->query();
		echo JSON::encode($photoRs);
	}

	//kindeditor图片上传
/*	public function upload_json()
	{
		$save_path = $this->app->getBasePath().$this->app->config['upload'].'/';
		$save_url  = IUrl::creatUrl('').$this->app->config['upload'].'/';
		$realpath  = $this->app->getRuntimePath().'_systemjs/editor/php/upload_json.php';
		include($realpath);
	}*/

	//kindeditor flash多图片上传
/*	public function file_manager_json()
	{
		$root_path = $this->app->getBasePath().$this->app->config['upload'].'/';
		$root_url  = IUrl::creatUrl('').$this->app->config['upload'].'/';
		$realpath  = $this->app->getRuntimePath().'_systemjs/editor/php/file_manager_json.php';
		include($realpath);
	}*/
	/**
	 * @brief 商家公用 图片上传的方法
	 */
	public function img_upload()
	{
	 	//调用文件上传类
		$photoObj = new PhotoUpload('', 'shop');
		$photo    = current($photoObj->run2());

		//判断上传是否成功，如果float=1则成功
		if($photo['flag'] == 1)
		{
			$result = array(
				'flag'=> 1,
				'img' => $photo['img']
			);
		}
		else
		{
			$result = array('flag'=> $photo['flag']);
		}
		echo JSON::encode($result);
	}

	//生成缩略图
	public function thumb()
	{
		//配置参数
		$mixData = IFile::dirExplodeDecode(IReq::get('img'));
		if($mixData)
		{
			preg_match("#/w/(\d+)#",$mixData,$widthData);
			preg_match("#/h/(\d+)#",$mixData,$heightData);

			if(isset($widthData[1]) && isset($heightData[1]))
			{
				$imgSrc = str_replace(array($widthData[0],$heightData[0]),"",$mixData);
				$width  = $widthData[1];
				$height = $heightData[1];

	    		if(is_file($imgSrc))
	    		{
		 			$thumbSrc   = Thumb::get($imgSrc,$width,$height);
		 			$fileExt    = pathinfo($thumbSrc, PATHINFO_EXTENSION);
	                $mtime      = filemtime($thumbSrc);
	                $gmdate_mod = gmdate('D, d M Y H:i:s',$mtime).' GMT';

					header('Last-Modified: '.$gmdate_mod);
		 			header('Expires: '.gmdate('D, d M Y H:i:s', time() + (60*60*24*30)) . ' GMT');
		 			header('Content-type: image/'.$fileExt);
		 			header('Content-Length: ' .filesize($thumbSrc));
					readfile($thumbSrc);
	    		}
			}
		}
        return '';
	}
}