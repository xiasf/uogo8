<?php
/**
 * @brief 活动逻辑控制器
 * @class activity
 * @note  
 */
class activity extends IController
{
	public $checkRight  = 'all';
	public $layout = 'admin';

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}


	// 活动报名（前台）
	function activity_signup() {
		if ($_POST) {
			$id = IFilter::act(IReq::get('id', 'post'),'int');
			$name = IFilter::act(IReq::get('name', 'post'),'text');
			$mobile = IFilter::act(IReq::get('mobile', 'post'),'text');
			$mobile = IFilter::act(IReq::get('mobile', 'post'),'text');
			$num = IFilter::act(IReq::get('num', 'post'),'int');

			$activity = new IModel('activity');
			if ($activity->query('id ='.$id)) {
				$activity_signup = new IModel('activity_signup');
				if ($signup = $activity_signup->getObj('name ='.$name.' and tel='.$mobile)) {
					$activity_signup->setData(array('nums' => $signup['nums']+$num));
					$activity_signup->update('id = '.$signup['id']);
				} else {
					$activity_signup->setData(array('activity_id'=>$id,'nums' => $num,'name'=>$name,'tel'=>$mobile,'datetime' => ITime::getDateTime()));
					$activity_signup->add();
				}
				$isError = false;
				$data = '报名成功';
			} else {
				$isError = true;
				$data = '活动不存在';
			}
			//json数据
			$result = array(
				'isError' => $isError,
				'data'    => $data,
			);
			echo JSON::encode($result);
		}
	}

	// 活动报名
	function activity_signup_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$activity_catObj = new IModel('activity_signup');
			$where = 'id = '.$id;
			$this->activity_catRow = $activity_catObj->getObj($where);
		}
		$this->redirect('activity_signup_edit');
	}


	// 活动报名
	function activity_signup_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$activity_catObj = new IModel('activity_signup');

		$dataArray = array(
			'activity_id'      => IFilter::act(IReq::get('activity_id','post')),
			'name'       => IFilter::act(IReq::get('name','post')),
			'tel'      => IFilter::act(IReq::get('tel','post')),
			'nums'      => IFilter::act(IReq::get('nums','post')),
			'datetime'      => IFilter::act(IReq::get('datetime','post')),
		);

		$activity = new IModel('activity');
		if(!$activity->getObj('id = '. $dataArray['activity_id'])) {
			die('活动ID不存在');
		}

		$activity_catObj->setData($dataArray);

		if($id)
		{
			if ($signup = $activity_catObj->getObj('name ='.$dataArray['name'].' and tel='.$dataArray['tel'])) {
				if ($signup['id'] != $id)
					die('该用户已报名，请直接修改对应记录：'.$signup['id']);
			}

			$where = 'id = '.$id;
			$activity_catObj->update($where);
		}
		else
		{

			$activity_signup = new IModel('activity_signup');
			if ($signup = $activity_signup->getObj('name ='.$dataArray['name'].' and tel='.$dataArray['tel']))
				die('该用户已报名，请直接修改对应记录：'.$signup['id'].'哦');

			$activity_catObj->add();
		}
		$this->redirect('activity_signup_list');
	}


	//[活动] 添加修改 [单页]
	function activity_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$activityObj = new IModel('activity');
			$where = 'id = '.$id;
			$this->activityRow = $activityObj->getObj($where);
		}
		$this->redirect('activity_edit');
	}


	//[活动] 添加修改 [动作]
	function activity_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$activityObj = new IModel('activity');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'is_open'   => IFilter::act(IReq::get('is_open','post')),
			'address'      => IFilter::act(IReq::get('address','post')),
			'start_time' => IFilter::act(IReq::get('start_time','post')),
			'end_time'   => IFilter::act(IReq::get('end_time','post')),
			'smalldescribe'      => IFilter::act(IReq::get('smalldescribe','post')),
			'describe'      => IFilter::act(IReq::get('describe','post'),'text'),
			'seller_id'      => IFilter::act(IReq::get('seller_id','post')),
			'tel'      => IFilter::act(IReq::get('tel','post')),
			'sort'      => IFilter::act(IReq::get('sort','post')),
			'cat_id'      => IFilter::act(IReq::get('cat_id','post')),
		);

		if(isset($_FILES['imglist']['name']) && $_FILES['imglist']['name'])
		{
			$uploadObj = new PhotoUpload();
			$uploadObj->setIterance(false);
			$photoInfo = $uploadObj->run();
			if(isset($photoInfo['imglist']['img']) && file_exists($photoInfo['imglist']['img']))
			{
				$dataArray['imglist'] = $photoInfo['imglist']['img'];
			}
		}

		$activityObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$activityObj->update($where);
		}
		else
		{
			$activityObj->add();
		}
		$this->redirect('activity_list');
	}


	//[活动] 删除
	function activity_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$activityObj = new IModel('activity');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$activityObj->del($where);
			$this->redirect('activity_list');
		}
		else
		{
			$this->redirect('activity_list',false);
			Util::showMessage('请选择要删除的促销活动');
		}
	}


	//[活动分类] 添加修改 [单页]
	function activity_cat_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$activity_catObj = new IModel('activity_cat');
			$where = 'id = '.$id;
			$this->activity_catRow = $activity_catObj->getObj($where);
		}
		$this->redirect('activity_cat_edit');
	}


	//[活动分类] 添加修改 [动作]
	function activity_cat_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$activity_catObj = new IModel('activity_cat');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'sort'      => IFilter::act(IReq::get('sort','post')),
		);

		$activity_catObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$activity_catObj->update($where);
		}
		else
		{
			$activity_catObj->add();
		}
		$this->redirect('activity_cat_list');
	}


	//[活动分类] 删除
	function activity_cat_del() 
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$activity_catObj = new IModel('activity_cat');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$activity_catObj->del($where);
			$this->redirect('activity_cat_list');
		}
		else
		{
			$this->redirect('activity_cat_list',false);
			Util::showMessage('请选择要删除的活动');
		}
	}


}