<?php
/**
 * @brief 频道逻辑控制器
 * @class channel
 * @note  
 */
class channel extends IController
{
	public $checkRight  = 'all';
	public $layout = 'admin';

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}


		//[频道] 添加修改 [单页]
	function channel_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$channelObj = new IModel('channel');
			$where = 'id = '.$id;
			$data = $channelObj->getObj($where);
			$data['category_list'] = (explode(',', $data['category_list']));
			$this->channelRow = $data;
		}
		$this->redirect('channel_edit');
	}


	//[频道] 添加修改 [动作]
	function channel_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$channelObj = new IModel('channel');

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'describe'      => IFilter::act(IReq::get('describe','post'),'text'),
			'sort'      => IFilter::act(IReq::get('sort','post')),
			'is_open'      => IFilter::act(IReq::get('is_open','post')),
			'category_list'     => implode(',', IFilter::act(IReq::get('category_list','post'))),
		);

		$channelObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$channelObj->update($where);
		}
		else
		{
			$channelObj->add();
		}
		$this->redirect('channel_list');
	}


	//[频道] 删除
	function channel_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$channelObj = new IModel('channel');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$channelObj->del($where);
			$this->redirect('channel_list');
		}
		else
		{
			$this->redirect('channel_list',false);
			Util::showMessage('请选择要删除的促销频道');
		}
	}
}