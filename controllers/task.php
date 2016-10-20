<?php
/**
 * @brief Task
 * @class Task
 * @note  前台
 * updateTime： 2016-7-27 16:07:18
 */

class Task extends IController
{
	public $layout = 'site';

	public function init()
	{
		CheckRights::checkUserRights();
		if(!$this->user)
		{
			$this->redirect('/simple/login?callback=/task/task_info/id/' . IFilter::act(IReq::get('id'),'int'));
		}
	}


	// 显示商品列表（积分商城）
	public function goods_list() {
		$goods_model        = new IQuery('task_goods');
		$goods_model->where = 'is_lock = 0';
		$goods_model->order = 'sort asc, id desc';
		$goods_list         = $goods_model->find();
		$this->setRenderData(array('goods_list' => $goods_list));
		$this->redirect('goods_list');
	}


	// 显示商品详情
	public function goods_info() {
		$id         = IFilter::act(IReq::get('id'), 'int');
		$goods_model        = new IModel('task_goods');
		$goods_info         = $goods_model->getObj('is_lock = 0 and id = ' . $id);
		if (empty($goods_info)) {
			IError::show(403, "这件商品不存在");
			exit;
		}
		$this->setRenderData(array('goods_info' => $goods_info));
		$this->redirect('goods_info');
	}


	// 显示兑换列表
	public function order_list() {
		$order_model        = new IQuery('task_order');
		$order_model->order = 'id desc';
		$order_model->where = 'uid = ' . $this->user['user_id'];
		$order_list         = $order_model->find();
		$this->setRenderData(array('order_list' => $order_list));
		$this->redirect('order_list');
	}


	// 兑换商品
	public function exchangeGoods()
	{
		$id          = IFilter::act(IReq::get('id'), 'int');
		$goods_model = new IModel('task_goods');
		$goods_info  = $goods_model->getObj('is_lock = 0 and id = ' . $id);

		if (empty($goods_info)) {
			$res = array('errCode' => -2, 'errMsg' => '抱歉，商品不存在');
			echo json_encode($res);
			return;
		}

		if (!$goods_info['inventory']) {
			$res = array('errCode' => -1, 'errMsg' => '抱歉，商品库存不足');
			echo json_encode($res);
			return;
		}


		$member_model = new IModel('member');
		$user_info    = $member_model->getObj('user_id = ' . $this->user['user_id'], 'point');
		if ($user_info['point'] < $goods_info['integral']) {
			$res = array('errCode' => 0, 'errMsg' => '哎呀，您的积分不足' . $goods_info['integral'] . '，赶紧去做任务赚积分吧');
			echo json_encode($res);
			return;
		}

		$order_model      = new IModel('task_order');

		if ($goods_info['limit_num']) {
			$info = $order_model->getObj('uid = ' . $this->user['user_id'] . ' and goods_id = ' . $goods_info['id'], 'count(id) as num');
			$num = $info['num'];
			if ($num >= $goods_info['limit_num']) {
				$res = array('errCode' => 1, 'errMsg' => '每人限制最多能兑换' . $goods_info['limit_num'] . '次，贪心会长胖的哦');
				echo json_encode($res);
				return;
			}
		}

		$data = array(
			'uid'         => $this->user['user_id'],
			'goods_id'    => $goods_info['id'],
			'goods_name'  => $goods_info['name'],
			'goods_img'   => $goods_info['img'],
			'integral' 	  => $goods_info['integral'],
			'create_time' => ITime::getDateTime(),
		);
		$order_model->setData($data);
		if ($order_model->add($data)) {
			// 改变库存和销量（会出现超卖）
			$goods_model->setData(array('sales' => 'sales+1', 'inventory' => 'inventory-1'));
			$goods_model->update('id = ' . $id, array('sales', 'inventory'));

			// 扣除相应的积分
			$member_model->setData(array('point' => 'point-' . $goods_info['integral']));
			// 做一个乐观锁，防止并发导致的问题
			if (!$member_model->update('point >= ' . $goods_info['integral'] . ' and user_id = ' . $this->user['user_id'], array('point'))) {
				$member_model->rollback();
				$res = array('errCode' => 2, 'errMsg' => '啊！您的积分不足' . $goods_info['integral'] . '，赶紧去做任务赚积分吧');
				echo json_encode($res);
				return;
			}

			$res = array('status' => 1, 'info' => '恭喜，兑换成功');
		} else {
			$res = array('errCode' => 1, 'errMsg' => '抱歉，未知错误');
		}
		echo json_encode($res);
	}


	############################# 积分商城完毕 ################################




	// 显示我的任务列表（进行中|待领取奖励|已完成）
	public function task_user() {
		$status         = IFilter::act(IReq::get('status'), 'int');
		$task_model        = new IQuery('task_user tu');
		$task_model->join = 'left join task as t on t.id = tu.task_id';
		$task_model->where = "tu.uid = " . $this->user['user_id'] . ' and tu.status = ' . $status;
		$task_model->order = 'tu.user_task_id desc';
		$task_list         = $task_model->find();
		$this->setRenderData(array('task_list' => $task_list));
		$this->redirect('task_user');
	}


	// 显示任务列表（任务大厅）
	public function task_list() {
		$task_model        = new IQuery('task');
		$task_model->where = 'is_lock = 0';
		$task_model->order = 'sort asc, id desc';
		$task_list         = $task_model->find();
		$this->setRenderData(array('task_list' => $task_list));
		$this->redirect('task_list');
	}


	// 显示任务详情
	public function task_info() {
		$id         = IFilter::act(IReq::get('id'), 'int');
		$task_model = new IModel('task t, task_model tm');
		$task_info  = $task_model->getObj('t.is_lock = 0 and t.model_id = tm.id and t.id = ' . $id, 't.*,tm.name as model_name');
		if (empty($task_info)) {
			//IError::show(403, "任务不存在");
			$this->redirect('task_list');
			exit;
		}
		$task_info['param'] = json_decode($task_info['param'], true);
		$task_info['param']['title'] = $task_info['param']['title'] ? : $task_info['title'];
		$task_info['param']['describe'] = $task_info['param']['describe'] ? : $task_info['describe'];
		$task_info['param']['url'] = $task_info['param']['url'] ? : IUrl::getHost() . '/task/task_info/id/' . $task_info['task_id'];
		$this->setRenderData($task_info);
		$this->redirect($task_info['model_name'] . '/task_info', false);
	}


	// 显示我的任务详情
	public function task_user_info() {
		$user_task_id         = IFilter::act(IReq::get('user_task_id'), 'int');
		$task_model = new IModel('task_user tu, task t, task_model tm');
		$task_info  = $task_model->getObj('t.model_id = tm.id and tu.task_id = t.id and tu.user_task_id = ' . $user_task_id, 't.*,tu.*,tm.name as model_name');
		if (empty($task_info)) {
			IError::show(403, "任务不存在");
			exit;
		}
		$task_info['param'] = json_decode($task_info['param'], true);
		$task_info['param']['title'] = $task_info['param']['title'] ? : $task_info['title'];
		$task_info['param']['describe'] = $task_info['param']['describe'] ? : $task_info['describe'];
		$task_info['param']['url'] = $task_info['param']['url'] ? : IUrl::getHost() . '/task/task_info/id/' . $task_info['task_id'];
		$this->setRenderData($task_info);
		$this->redirect($task_info['model_name'] . '/task_user_info', false);
	}


	// 我的任务统计信息
	public function taskUserCount() {
		$res = array('total' => 0, 'ongoing' => 0, 'complete' => 0, 'failure' => 0);
		$task_model         = new IQuery('task_user');
		$task_model->fields = 'status, count(*) as num';
		$task_model->where  = 'uid = ' . $this->user['user_id'];
		$task_model->group  = 'status';
		$data               = $task_model->find();
		foreach ($data as $value) {
			switch ($value['status']) {
				case 0:
					$res['ongoing'] += $value['num'];
					break;
				case 1:
					$res['complete'] += $value['num'];
					break;
				case 2:
					$res['failure'] += $value['num'];
					break;
				default:
					break;
			}
		}
		$res['total'] = array_sum($res);
		echo json_encode($res);
	}


	// 参加（领取任务）任务
	public function toTask() {
		$id   = IFilter::act(IReq::get('id'), 'int');
		$task = new task_facade($id);
		$res  = $task->toTask();
		echo json_encode($res);
	}


	// 执行任务（触摸任务）
	public function touchTask() {
		$id   = IFilter::act(IReq::get('id'), 'int');
		$user_task_id   = IFilter::act(IReq::get('user_task_id'), 'int');
		$task = new task_facade($id);
		$res  = $task->touchTask($user_task_id);
		echo json_encode($res);
	}


	// 获取任务状态
	public function getTaskStatus() {
		$id   = IFilter::act(IReq::get('id'), 'int');
		$user_task_id   = IFilter::act(IReq::get('user_task_id'), 'int');
		$task = new task_facade($id);
		$res  = $task->getTaskStatus($user_task_id);
		echo json_encode($res);
	}


	// 获取任务进度
	public function getTaskProgress() {
		$id   = IFilter::act(IReq::get('id'), 'int');
		$user_task_id   = IFilter::act(IReq::get('user_task_id'), 'int');
		$task = new task_facade($id);
		$res  = $task->getTaskProgress($user_task_id);
		echo json_encode($res);
	}


	// 获取任务的参与情况
	public function getTaskParticipate()
	{
		$id   = IFilter::act(IReq::get('id'), 'int');
		$task = new task_facade($id);
		$res  = $task->getTaskParticipate();
		echo json_encode($res);
	}


	// 获取我的任务参与情况
	public function getTaskUserParticipate()
	{
		$id   = IFilter::act(IReq::get('id'), 'int');
		$user_task_id   = IFilter::act(IReq::get('user_task_id'), 'int');
		$task = new task_facade($id);
		$res  = $task->getTaskUserParticipate($user_task_id);
		echo json_encode($res);
	}


	// 领取任务奖励
	public function toTaskRewards() {
		$id   = IFilter::act(IReq::get('id'), 'int');
		$task = new task_facade($id);
		$res  = $task->toTaskRewards();
		echo json_encode($res);
	}
}