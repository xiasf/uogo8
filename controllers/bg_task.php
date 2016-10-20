<?php
/**
 * @brief 任务
 * @class task
 * @note  后台
 * @update：2016-7-11 09:14:11
 */
class bg_task extends IController
{
    public $layout     ='admin';
    public $checkRight = array('check' => 'all','uncheck' => array('seo_sitemaps'));

	function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}


    public function order_receive() {
        $id      = IFilter::act(IReq::get('id'),'int');
        $orderObj = new IModel('task_order');
        $orderObj->setData(array('status' => 1));
        if (false !== $orderObj->update('id = '.$id))
            die(JSON::encode(array('result' => 'succeed','data' => '领取成功')));
        else
            die(JSON::encode(array('result' => 'fail','data' => '领取失败')));
    }


    public function ajax_goods_sort() {
        $id      = IFilter::act( IReq::get('id'),'int');
        $sort    = IFilter::act( IReq::get('sort'),'int');
        $goodsObj = new IModel('task_goods');
        $goodsObj->setData(array('sort' => $sort));
        if (false !== $goodsObj->update('id = '.$id))
            die(JSON::encode(array('result' => 'succeed','data' => '')));
        else
            die(JSON::encode(array('result' => 'fail','data' => '')));
    }


    public function goods_edit_act()
    {
        $id       = IFilter::act(IReq::get("id"),'int');
        $name    = IFilter::act(IReq::get("name"));
        $describe = IFilter::act(IReq::get("describe"));
        $smalldescription       = IFilter::act(IReq::get("smalldescription"));
        $is_lock       = IFilter::act(IReq::get("is_lock"),'int');
        $limit_num       = IFilter::act(IReq::get("limit_num"),'int');
        $type       = IFilter::act(IReq::get("type"),'int');
        $inventory       = IFilter::act(IReq::get("inventory"),'int');
        $integral       = IFilter::act(IReq::get("integral"),'int');
        $sort       = IFilter::act(IReq::get("sort"),'int');
        $task_goods = new IModel('task_goods');
        $data = array(
            'name'    => $name,
            'describe' => $describe,
            'smalldescription' => $smalldescription,
            'is_lock'     => $is_lock,
            'limit_num'     => $limit_num,
            'type'    => $type,
            'integral'    => $integral,
            'inventory'    => $inventory,
            'sort'    => $sort,
        );
        if((isset($_FILES['img']['name']) && $_FILES['img']['name']))
        {
            $photoInfo = $this->picUpload();
            if(isset($photoInfo['img']['img']) && file_exists($photoInfo['img']['img']))
            {
                $data['img'] = $photoInfo['img']['img'];
            }
        }
        $task_goods->setData($data);
        if (!$id) {
            $task_goods->add();
        } else {
            $task_goods->update('id = ' . $id);
        }
        $this->redirect('goods_list');
    }


    public function goods_edit()
    {
        // 获取POST数据
        $id = IFilter::act(IReq::get("id"),'int');
        if($id)
        {
            //初始化Model类对象
            $modelObj = new IModel('task_goods');
            //获取模型详细信息
            $model_info = $modelObj->getObj('id = ' . $id);
            //向前台渲染数据
            $this->setRenderData($model_info);
        }
        $this->redirect('goods_edit');
    }


    public function goods_del()
    {
        $id = IFilter::act(IReq::get("id"), 'int');
        if ($id) {
            $model = new IModel('task_goods');
            $model->del('id = ' . $id);
        }
        $this->redirect('goods_list');
    }


    public function ajax_task_sort() {
        $id      = IFilter::act( IReq::get('id'),'int');
        $sort    = IFilter::act( IReq::get('sort'),'int');
        $taskObj = new IModel('task');
        $taskObj->setData(array('sort' => $sort));
        if (false !== $taskObj->update('id = '.$id))
            die(JSON::encode(array('result' => 'succeed','data' => '')));
        else
            die(JSON::encode(array('result' => 'fail','data' => '')));
    }


    public function task_edit_act()
    {
    	$model_id       = IFilter::act(IReq::get("model_id"),'int');
    	$model = new IModel('task_model');

    	if (!$model_info = $model->getObj('id = ' . $model_id)) {
    		die('任务模型不存在');
    	}
        $model_param = json_decode($model_info['param'], true);

		$id       = IFilter::act(IReq::get("id"),'int');
		$title    = IFilter::act(IReq::get("title"));
		$describe = IFilter::act(IReq::get("describe"));
        // $content = IFilter::act(IReq::get("content"), 'text');
        $content     = IFilter::act(IReq::get('content','post'), 'html');
		$rule = IFilter::act(IReq::get("rule"));
        $is_lock       = IFilter::act(IReq::get("is_lock"),'int');
        $sort       = IFilter::act(IReq::get("sort"),'int');
		$param    = IFilter::act(IReq::get("param"));
        $rewards    = IFilter::act(IReq::get("rewards"));
		$tem      = array();
        // 这个值取决于模型，而不是信任客户端的提交
    	foreach ($model_param as $value) {
            if (!isset($param[$value['name']])) die('参数非法！');
    		$tem[$value['name']] = $param[$value['name']];
    	}
    	$param = $tem;
    	$param = addslashes(json_encode($param));

    	$task = new IModel('task');
    	$data = array(
            'model_id' => $model_id,
            'title'    => $title,
            'describe' => $describe,
            'content'  => $content,
            'rule'     => $rule,
            'is_lock'  => $is_lock,
            'sort'     => $sort,
            'param'    => $param,
            'rewards'  => $rewards,
    	);
        if((isset($_FILES['logo']['name']) && $_FILES['logo']['name']) || (isset($_FILES['banner']['name']) && $_FILES['banner']['name']))
        {
            $photoInfo = $this->picUpload();
            if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
            {
                $data['logo'] = $photoInfo['logo']['img'];
            }
            if(isset($photoInfo['banner']['img']) && file_exists($photoInfo['banner']['img']))
            {
                $data['banner'] = $photoInfo['banner']['img'];
            }
        }
    	$task->setData($data);
    	if (!$id) {
    		$task->add();
    	} else {
    		$task->update('id = ' . $id);
    	}
    	$this->redirect('task_list');
    }


    public function task_edit()
    {
    	// 获取POST数据
    	$id = IFilter::act(IReq::get("id"),'int');
    	if($id)
    	{
    		//初始化Model类对象
    		$modelObj = new IModel('task');
    		//获取模型详细信息
			$model_info = $modelObj->getObj('id = ' . $id);
			//向前台渲染数据
			$this->setRenderData($model_info);
    	}
		$this->redirect('task_edit');
    }

    public function task_del()
    {
		$id = IFilter::act(IReq::get("id"), 'int');
		if ($id) {
			$model = new IModel('task');
			$model->del('id = ' . $id);
		}
		$this->redirect('task_list');
    }



	public function get_task_model_param()
    {
		$res = array('status' => 0, 'info' => '');
		$id = IFilter::act(IReq::get('id'), 'int');
		$model = new IModel('task_model');
		$param = $model->getObj('id = ' . $id, 'param');
		if ($param) {
			$res['status'] = 1;
			$res['info'] = $param['param'];
		} else {
			$res['info'] = '任务模型不存在';
		}
		echo json_encode($res);
	}


    public function model_edit_act()
    {
		$id       = IFilter::act(IReq::get("id"),'int');
		$title    = IFilter::act(IReq::get("title"));
		$name     = IFilter::act(IReq::get("name"));
		$describe = IFilter::act(IReq::get("describe"));
		$param    = IFilter::act(IReq::get("param"));
		$tem      = array();
    	foreach ($param as $key => $item) {
    		foreach ($item as $i => $value) {
    			$tem[$i][$key] = $value;
    		}
    	}
    	$param = $tem;
    	$param = addslashes(json_encode($param));

    	$model = new IModel('task_model');
    	$data = array(
			'title'    => $title,
			'name'     => $name,
			'describe' => $describe,
			'param'    => $param,
    	);

        if(isset($_FILES['logo']['name']) && $_FILES['logo']['name'])
        {
            $photoInfo = $this->picUpload();
            if(isset($photoInfo['logo']['img']) && file_exists($photoInfo['logo']['img']))
            {
                $data['logo'] = $photoInfo['logo']['img'];
            }
        }

    	$model->setData($data);
    	if (!$id) {
    		$model->add();
    	} else {
    		$model->update('id = ' . $id);
    	}
    	$this->redirect('model_list');
    }

    public function model_del() {
		$id = IFilter::act(IReq::get("id"), 'int');
		if ($id) {
			$model = new IModel('task_model');
			$model->del('id = ' . $id);
		}
		$this->redirect('model_list');
    }


    public function model_edit()
    {
    	// 获取POST数据
    	$id = IFilter::act(IReq::get("id"),'int');
    	if($id)
    	{
    		//初始化Model类对象
    		$modelObj = new IModel('task_model');
    		//获取模型详细信息
			$model_info = $modelObj->getObj('id = ' . $id);
			$model_info['param'] = json_decode($model_info['param'], true);
			//向前台渲染数据
			$this->setRenderData($model_info);
    	}
		$this->redirect('model_edit');
    }


    function picUpload($kdir = 'task')
    {
        $uploadObj = new PhotoUpload('', $kdir);
        return $uploadObj->run2();
    }
}