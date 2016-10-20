<?php
/**
 * @brief 招标
 * @class zb
 * @note  后台
 * @update：2016-3-13 09:58:39
 */
class bg_zhaobiao extends IController
{
	public $layout='admin';
	public $checkRight = array('check' => 'all','uncheck' => array('seo_sitemaps'));

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}


	function zb_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$zbObj = new IModel('zhaobiao');
			$where = 'id = '.$id;
			$this->zb_homeRow = $zbObj->getObj($where);
		}
		$this->redirect('zb_edit');
	}


	// 招标
	function zb_post_act() {
		$id = IFilter::act(IReq::get('id'),'int');
		$zbObj = new IModel('zhaobiao');
		$dataArray = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'from'   => IFilter::act(IReq::get('from','post')),
			'contact'      => IFilter::act(IReq::get('contact','post')),
			'home_id'      => IFilter::act(IReq::get('home_id','post'),'int'),
			'way_id'      => IFilter::act(IReq::get('way_id','post')),
			'type_id'      => IFilter::act(IReq::get('type_id','post')),
			'style_id'      => IFilter::act(IReq::get('style_id','post')),
			'budget_id'      => IFilter::act(IReq::get('budget_id','post')),
			'service_id'      => IFilter::act(IReq::get('service_id','post')),
			'house_type_id'      => IFilter::act(IReq::get('house_type_id','post')),
			'house_mj'      => IFilter::act(IReq::get('house_mj','post')),
			'area'      => IFilter::act(IReq::get('area','post')),
			'address'      => IFilter::act(IReq::get('address','post')),
			'comment'      => IFilter::act(IReq::get('comment','post')),
			'zx_time'      => IFilter::act(IReq::get('zx_time','post')),
			'gold'      => IFilter::act(IReq::get('gold','post')),
			'max_look'      => IFilter::act(IReq::get('max_look','post')),
			'looks'      => IFilter::act(IReq::get('looks','post')),
			'views'      => IFilter::act(IReq::get('views','post')),
			'status'      => IFilter::act(IReq::get('status','post')),
			'remark'      => IFilter::act(IReq::get('remark','post')),
			'audit'      => IFilter::act(IReq::get('audit','post')),
			'mobile'      => IFilter::act(IReq::get('mobile','post')),
			'create_time'      => ITime::getDateTime(),
		);

		if(isset($_FILES['img']['name']) && $_FILES['img']['name'])
		{
			$photoInfo = $this->picUpload();
			if(isset($photoInfo['img']['img']) && file_exists($photoInfo['img']['img']))
			{
				$dataArray['img'] = $photoInfo['img']['img'];
			}
		}

		$zbObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$zbObj->update($where);
		}
		else
		{
			$zbObj->add();
		}
		$this->redirect('zb_list');
	}

		// 删除
	function zb_del() {
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$zbObj = new IModel('zhaobiao');
			$zbObj->del(Util::joinStr($id));
			$this->redirect('zb_list');
		}
		else
		{
			$this->redirect('zb_list',false);
			Util::showMessage('请选择要删除的小区');
		}
	}



	function zb_home_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$zbObj = new IModel('zhaobiao_home');
			$where = 'id = '.$id;
			$this->zb_homeRow = $zbObj->getObj($where);
		}
		$this->redirect('zb_home_edit');
	}


	// 楼盘编辑
	function zb_home_edit_act() {
		$id = IFilter::act(IReq::get('id'),'int');
		$zbObj = new IModel('zhaobiao_home');
		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'mobile'   => IFilter::act(IReq::get('mobile','post')),
			'area'      => IFilter::act(IReq::get('area','post')),
			'time'      => IFilter::act(IReq::get('time','post'),'text'),
			'price'      => IFilter::act(IReq::get('price','post')),
			'zdmj'      => IFilter::act(IReq::get('zdmj','post')),
			'jzmj'      => IFilter::act(IReq::get('jzmj','post')),
			'lhl'      => IFilter::act(IReq::get('lhl','post')),
			'rjl'      => IFilter::act(IReq::get('rjl','post')),
			'kfs'      => IFilter::act(IReq::get('kfs','post')),
			'wygs'      => IFilter::act(IReq::get('wygs','post')),
			'jz_type'      => IFilter::act(IReq::get('jz_type','post')),
		);

		if(isset($_FILES['img']['name']) && $_FILES['img']['name'])
		{
			$photoInfo = $this->picUpload();
			if(isset($photoInfo['img']['img']) && file_exists($photoInfo['img']['img']))
			{
				$dataArray['img'] = $photoInfo['img']['img'];
			}
		}

		$zbObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$zbObj->update($where);
		}
		else
		{
			$zbObj->add();
		}
		$this->redirect('zb_home_list');
	}

	// 楼盘删除
	function zb_home_del() {
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$zbObj = new IModel('zhaobiao_home');
			$zbObj->del(Util::joinStr($id));
			$this->redirect('zb_home_list');
		}
		else
		{
			$this->redirect('zb_home_list',false);
			Util::showMessage('请选择要删除的小区');
		}
	}

	function picUpload($kdir = 'zhaobiao') {
		$uploadObj = new PhotoUpload('', $kdir);
		return $uploadObj->run2();
	}

}