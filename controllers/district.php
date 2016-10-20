<?php
/**
 * @brief 会员模块
 * @class Member
 * @note  后台
 */
class District extends IController
{	
	public $checkRight  = 'all';
    public $layout='admin';
	private $data = array();

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}
	public function del()
	{
		$id = IFilter::act( IReq::get('id'),'int');
		$districtObj = new IModel('district');
		if ($a = $districtObj->del("id = ".$id))
			die(JSON::encode(array('result' => 'succeed','data' => '')));
		else
			die(JSON::encode(array('result' => 'fail','data' => '')));
	}

	public function ajax_sort() {
		$id = IFilter::act( IReq::get('id'),'int');
		$sort = IFilter::act( IReq::get('sort'),'int');
		$districtObj = new IModel('district');
		$districtObj->setData(array('sort' => $sort));
		if (false !== $districtObj->update('id = '.$id))
			die(JSON::encode(array('result' => 'succeed','data' => '')));
		else
			die(JSON::encode(array('result' => 'fail','data' => '')));
	}

	public function district_edit() {
		$id = IFilter::act( IReq::get('id'),'int');
		if($id)
		{
			$userDB = new IQuery('district');
			$userDB->where= 'id = '.$id;
			$userInfo = $userDB->find();
			if($userInfo)
			{
				$this->userInfo = current($userInfo);
			}
			else
			{
				$this->member_list();
				Util::showMessage("没有找到相关记录！");
				exit;
			}
		}
		$this->redirect('district_edit');
	}

	public function district_save() {
		$id      = IFilter::act( IReq::get('id'),'int' );
		$upload = IReq::get('upload');
		$pic = IReq::get('pic');
		$position_id = IFilter::act(IReq::get('position_id'));

		//图片上传
		if(isset($_FILES) && $_FILES)
		{
			$photoObj = new PhotoUpload('', 'district');
			$photo    = $photoObj->run();

			$result = $photo ? current($photo) : "";

			if($result && isset($result['flag']) && $result['flag'] == 1)
			{
				$pic = $result['img'];
			}
/*			else if(!$pic)
			{
				IError::show(403,"请上传正确的图片数据");
			}*/
		}

		$districtObj = new IModel('district');
		$dataArray = array(
			'upload'     => IFilter::addSlash($pic),
			'name'        => IFilter::act(IReq::get('name')),
			'description' => IFilter::act(IReq::get('description'),'text'),
			'dx' => IFilter::act(IReq::get('dx'),'text'),
			'dy' => IFilter::act(IReq::get('dy'),'text'),
			'sort'        => IFilter::act(IReq::get('sort'), 'int'),
			'ishide'        => IFilter::act(IReq::get('ishide'), 'int'),
			'mainbuild'   => IFilter::act(IReq::get('mainbuild')),
		);

		$districtObj->setData($dataArray);
		if($id)
		{
			$where = 'id = '.$id;
			$districtObj->update($where);
		}
		else
		{
			$districtObj->add();
		}
		$this->redirect("district_list");
	}
}