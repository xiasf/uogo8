<?php
/**
 * @brief 家具
 * @class jiaju
 * @note  后台
 * @update：2016-3-13 09:58:39
 */
class jiaju extends IController
{
	public $layout='admin';
	public $checkRight = array('check' => 'all','uncheck' => array('seo_sitemaps'));

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}


	//[设计作品] 添加修改 [单页]
	function designer_works_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$statusObj = new IModel('jiaju_designer_works');
			$where = 'id = '.$id;
			$this->designer_worksRow = $statusObj->getObj($where);
		}
		$this->redirect('designer_works_edit');
	}


	//[设计作品] 添加修改 [动作]
	function designer_works_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$statusObj = new IModel('jiaju_designer_works');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'status'   => IFilter::act(IReq::get('status','post')),
			'smalldescribe'      => IFilter::act(IReq::get('smalldescribe','post')),
			'describe'      => IFilter::act(IReq::get('describe','post'),'text'),
			'designer'      => IFilter::act(IReq::get('designer','post'), 'int'),
			'building'      => IFilter::act(IReq::get('building','post'),'text'),
			'room'      => IFilter::act(IReq::get('room','post'),'text'),
			'price'      => IFilter::act(IReq::get('price','post'),'text'),
			'style'      => IFilter::act(IReq::get('style','post'),'text'),
			'area_size'      => IFilter::act(IReq::get('area_size','post'),'text'),
			'sort'      => IFilter::act(IReq::get('sort','post')),
		);

		if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'])
		{
			$photoInfo = $this->picUpload();

			if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
			{
				$dataArray['logo'] = $photoInfo['logo']['img'];
			}
		}

		$statusObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$statusObj->update($where);
		}
		else
		{
			$statusObj->add();
		}
		$this->redirect('designer_works_list');
	}



		//[设计师] 添加修改 [单页]
	function designer_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$statusObj = new IModel('jiaju_designer');
			$where = 'id = '.$id;
			$this->designerRow = $statusObj->getObj($where);
		}
		$this->redirect('designer_edit');
	}


	//[设计师] 添加修改 [动作]
	function designer_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$statusObj = new IModel('jiaju_designer');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'status'   => IFilter::act(IReq::get('status','post')),
			'smalldescribe'      => IFilter::act(IReq::get('smalldescribe','post')),
			'design'      => IFilter::act(IReq::get('design','post'),'text'),
			'mobile'      => IFilter::act(IReq::get('mobile','post')),
			'company'      => IFilter::act(IReq::get('company','post'), 'int'),
			'sort'      => IFilter::act(IReq::get('sort','post')),
		);

		if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'])
		{
			$photoInfo = $this->picUpload();

			if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
			{
				$dataArray['logo'] = $photoInfo['logo']['img'];
			}
		}

		$statusObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$statusObj->update($where);
		}
		else
		{
			$statusObj->add();
		}
		$this->redirect('designer_list');
	}



	//[公司] 添加修改 [单页]
	function company_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$statusObj = new IModel('jiaju_company');
			$where = 'id = '.$id;
			$this->companyRow = $statusObj->getObj($where);
		}
		$this->redirect('company_edit');
	}


	//[公司] 添加修改 [动作]
	function company_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$statusObj = new IModel('jiaju_company');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'status'   => IFilter::act(IReq::get('status','post')),
			'address'      => IFilter::act(IReq::get('address','post')),
			'smalldescribe'      => IFilter::act(IReq::get('smalldescribe','post')),
			'describe'      => IFilter::act(IReq::get('describe','post'),'text'),
			'mobile'      => IFilter::act(IReq::get('mobile','post')),
			'sort'      => IFilter::act(IReq::get('sort','post')),
			'star'      => IFilter::act(IReq::get('star','post'),'int'),
			'seller_user_id'      => IFilter::act(IReq::get('seller_user_id','post'),'int'),
		);

		if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'])
		{
			$photoInfo = $this->picUpload();

			if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
			{
				$dataArray['logo'] = $photoInfo['logo']['img'];
			}
		}

		$statusObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$statusObj->update($where);
		}
		else
		{
			$statusObj->add();
		}
		$this->redirect('company_list');
	}


	function picUpload($kdir = 'jiaju') {
		$uploadObj = new PhotoUpload('', $kdir);
		return $uploadObj->run2();
	}

	public function show_img_upload()
	{
	 	//调用文件上传类
		$photo    = current($this->picUpload());
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


	//[活动] 添加修改 [单页]
	function show_img_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$show_imgObj = new IModel('jiaju_show_img');
			$where = 'id = '.$id;
			$this->show_imgRow = $show_imgObj->getObj($where);
		}
		$this->redirect('show_img_edit');
	}


	//[活动] 添加修改 [动作]
	function show_img_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$show_imgObj = new IModel('jiaju_show_img');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'status'   => IFilter::act(IReq::get('status','post')),
			'company'      => IFilter::act(IReq::get('company','post'),'int'),
			'img_list'      => IFilter::act(IReq::get('img_list','post')),
			'cat_id'      => IFilter::act(IReq::get('cat_id','post')),
			'space_type'      => IFilter::act(IReq::get('space_type','post'), 'int'),
			'local_type'      => IFilter::act(IReq::get('local_type','post'), 'int'),
			'style_type'      => IFilter::act(IReq::get('style_type','post'), 'int'),
			'color_type'      => IFilter::act(IReq::get('color_type','post'), 'int'),
			'sort'      => IFilter::act(IReq::get('sort','post')),
		);

		$show_imgObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$show_imgObj->update($where);
		}
		else
		{
			$show_imgObj->add();
		}
		$this->redirect('show_img_list');
	}


	//[活动] 删除
	function show_img_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$show_imgObj = new IModel('jiaju_show_img');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$show_imgObj->del($where);
			$this->redirect('show_img_list');
		}
		else
		{
			$this->redirect('show_img_list',false);
			Util::showMessage('请选择要删除的促销活动');
		}
	}







	// [装修效果图分类] 添加修改 [单页]
	function show_img_cat_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$show_img_catObj = new IModel('jiaju_show_img_cat');
			$where = 'id = '.$id;
			$this->show_img_catRow = $show_img_catObj->getObj($where);
		}
		$this->redirect('show_img_cat_edit');
	}


	//[装修效果图分类] 添加修改 [动作]
	function show_img_cat_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$show_img_catObj = new IModel('jiaju_show_img_cat');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'sort'      => IFilter::act(IReq::get('sort','post')),
		);

		$show_img_catObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$show_img_catObj->update($where);
		}
		else
		{
			$show_img_catObj->add();
		}
		$this->redirect('show_img_cat_list');
	}


	//[装修效果图分类] 删除
	function show_img_cat_del() 
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$show_img_catObj = new IModel('jiaju_show_img_cat');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$show_img_catObj->del($where);
			$this->redirect('show_img_cat_list');
		}
		else
		{
			$this->redirect('show_img_cat_list',false);
			Util::showMessage('请选择要删除的活动');
		}
	}



		// [文章分类] 添加修改 [单页]
	function article_cat_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$article_catObj = new IModel('jiaju_article_category');
			$where = 'id = '.$id;
			$this->article_catRow = $article_catObj->getObj($where);
		}
		$this->redirect('article_cat_edit');
	}


	//[文章分类] 添加修改 [动作]
	function article_cat_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$article_catObj = new IModel('jiaju_article_category');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'sort'      => IFilter::act(IReq::get('sort','post')),
			'parent_id'      => IFilter::act(IReq::get('parent_id','post')),
		);

		$article_catObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$article_catObj->update($where);
		}
		else
		{
			$article_catObj->add();
		}
		$this->redirect('article_cat_list');
	}


	//[文章分类] 删除
	function article_cat_del() 
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$article_catObj = new IModel('jiaju_article_category');

			//是否执行删除检测值
			$isCheck=true;

			//检测是否有parent_id 为 $id
			$where   = 'parent_id = '.$id;
			$catData = $article_catObj->getObj($where);
			if(!empty($catData))
			{
				$isCheck=false;
				$message='此分类下还有子分类';
			}

			//检测是否有article的category_id 为 $id
			else
			{
				$articleObj = new IModel('jiaju_article');
				$where = 'category_id = '.$id;
				$catData = $articleObj->getObj($where);

				if(!empty($catData))
				{
					$isCheck=false;
					$message='此分类下还有文章';
				}
			}

			if($isCheck == false)
			{
				$message = isset($message) ? $message : '删除失败';
				$this->redirect('article_cat_list',false);
				Util::showMessage($message);
			}
			else
			{
				$where = 'id = '.$id;
				$article_catObj->del($where);
				$this->redirect('article_cat_list');
			}
		}
		else
		{
			$this->redirect('article_cat_list',false);
			Util::showMessage('请选择要删除的活动');
		}
	}



	//[文章]删除
	function article_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$show_img_catObj = new IModel('jiaju_article');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$show_img_catObj->del($where);
			$this->redirect('article_list');
		}
		else
		{
			$this->redirect('article_list',false);
			Util::showMessage('请选择要删除的活动');
		}
	}

	//[文章]单页
	function article_edit()
	{
		$data = array();
		$id   = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			//获取文章信息
			$articleObj       = new IModel('jiaju_article');
			$this->articleRow = $articleObj->getObj('id = '.$id);
			if(!$this->articleRow)
			{
				IError::show(403,"文章信息不存在");
			}
		}
		$this->redirect('article_edit');
	}

	//[文章]增加修改
	function article_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$articleObj = new IModel('jiaju_article');
		$dataArray  = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'content'     => IFilter::act(IReq::get('content','post'),'html'),
			'category_id' => IFilter::act(IReq::get('category_id','post'),'int'),
			'create_time' => ITime::getDateTime(),
			'description' => IFilter::act(IReq::get('description','post'),'text'),
			'img' => IFilter::act(IReq::get('img','post'),'text'),
			'sort'        => IFilter::act(IReq::get('sort','post'),'int'),
			'tag'        => join(',', (array) (IFilter::act(IReq::get('tag','post')))),
			'commend'        => join(',', (array) (IFilter::act(IReq::get('commend','post')))),
			'keyword' => IFilter::act(IReq::get('keyword','post'),'text'),
		);

		$articleObj->setData($dataArray);

		if($id)
		{
			//开始更新操作
			$where = 'id = '.$id;
			$articleObj->update($where);
		}
		else
		{
			$articleObj->add();
		}

		$this->redirect('article_list');
	}


		//[文章评论]删除
	function article_comment_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$article_commentObj = new IModel('jiaju_article_comment');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$article_commentObj->del($where);
			$this->redirect('article_comment_list');
		}
		else
		{
			$this->redirect('article_comment_list',false);
			Util::showMessage('请选择要删除的活动');
		}
	}

}