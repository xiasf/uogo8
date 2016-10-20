<?php
/**
 * @class task
 * @brief task协议登录基础类
 */
abstract class TaskBase
{
	abstract public function getTaskInfo();
	abstract public function toTask();
	abstract public function touchTask($user_task_id);
	abstract public function getTaskStatus($user_task_id);
	abstract public function getTaskProgress($user_task_id);
	abstract public function getTaskParticipate();
	abstract public function getTaskUserParticipate($user_task_id);
	abstract public function toTaskRewards();

	// 错误处理
	public function error($errCode, $errMsg, $data = null) {
		return array('errCode' => $errCode, 'errMsg' => $errMsg, 'data' => $data);
	}

	// 成功处理
	public function success($status, $info, $data = null) {
		return array('status' => $status, 'info' => $info, 'data' => $data);
	}
}