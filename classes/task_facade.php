<?php
/**
 * @class Task
 * @brief task协议接口
 */
class task_facade
{
	private $taskObj = null;

	/**
	 * 构造函数
	 * @param [int] $id [任务ID]
	 */
	public function __construct($id)
	{
		$taskInfo = $this->initTask($id);
		if (!$taskInfo) {
			return $this->error(001, "[ID:{$id}]：任务/模型不存在");
		}

		$tem = array();
		$tem2 = array();

		$model_param = json_decode($taskInfo['model_param'], true);
		$taskInfo['param'] = $task_param = json_decode($taskInfo['param'], true);

		foreach ($model_param as $value) {
			$tem[] = $value['name'];
		}

		$tem2 = array_keys($task_param);
		$array_diff = array_diff($tem, $tem2);
		unset($tem, $tem2);

		// 防止任务规则与模型规则不一致
		if (!empty($array_diff)) {
			return $this->error(002, "[ID:{$id}]：任务参数设置不正确，请更新任务规则参数");
		}
		unset($array_diff);
		if ($this->requireFile($taskInfo['name'])) {
			$className = 'Task_' . $taskInfo['name'];
			$this->taskObj = new $className($task_param, $taskInfo);
		} else {
			return $this->error(003, "[ID:{$id}]：任务接口不存在");
		}
	}


	// 获取任务信息
	public function getTaskInfo()
	{
		return $this->taskObj->getTaskInfo();
	}


	// 参加（领取任务）任务
	public function toTask() {
		$res = $this->taskObj->toTask();
		return $res;
	}


	// 执行任务（触摸任务）
	public function touchTask($user_task_id) {
		$res = $this->taskObj->touchTask($user_task_id);
		return $res;
	}


	// 获取任务状态
	public function getTaskStatus($user_task_id) {
		$res = $this->taskObj->getTaskStatus($user_task_id);
		return $res;
	}


	// 获取任务进度
	public function getTaskProgress($user_task_id) {
		$res = $this->taskObj->getTaskProgress($user_task_id);
		return $res;
	}


	// 获取任务的参与情况
	public function getTaskParticipate() {
		$res = $this->taskObj->getTaskParticipate();
		return $res;
	}


	// 获取我的任务参与情况
	public function getTaskUserParticipate($user_task_id) {
		$res = $this->taskObj->getTaskUserParticipate($user_task_id);
		return $res;
	}


	// 领取任务奖励
	public function toTaskRewards() {
		$res = $this->taskObj->toTaskRewards();
		return $res;
	}


	// 错误处理
	private function error($errCode, $errMsg) {
		exit(json_encode(array('errCode' => $errCode, 'errMsg' => $errMsg)));
	}


	// 成功处理
	public function success($status, $info) {
		return array('status' => $status, 'info' => $info);
	}


	/**
	 * [任务工厂初始化任务]
	 * @param  [int] $id [任务ID]
	 * @return [array]     [任务信息]
	 */
	private function initTask($id)
	{
		$task = new IModel('task t, task_model tm');
		$taskInfo = $task->getObj('t.model_id = tm.id and t.id = ' . $id, 't.*,tm.name, tm.param as model_param');
		return $taskInfo;
	}


	// 引入任务接口文件
	private function requireFile($fileName)
	{
		$taskBase = 'plugins/task/taskBase.php';
		$classFile = 'plugins/task/task_'.$fileName.'.php';
		if (file_exists($classFile)) {
			include_once($taskBase);
			include_once($classFile);
			return true;
		} else {
			return false;
		}
	}
}