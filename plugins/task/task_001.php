<?php
/**
 * @class 连续多天转发任务
 * @brief task协议接口
 * updateTime：2016-7-23 16:34:39
 */
class Task_001 extends TaskBase
{
	private $param          = array();
	private $taskInfo       = array();

	// 会员表
	private $memberTable  = 'member';

	// 任务表
	private $taskTable  = 'task';
	// 用户任务表
	private $taskUserTable  = 'task_user';
	// 转发任务表
	private $taskShareTable  = 'task_share';

	private $uid            = '';

	public function __construct($param, $taskInfo)
	{
		$this->param    = $param;
		$this->taskInfo = $taskInfo;
		$this->uid      = IWeb::$app->getController()->user['user_id'];
	}


	// 获取任务信息
	public function getTaskInfo()
	{
		return $this->taskInfo;
	}


	// 参加（领取任务）任务
	public function toTask()
	{
		$taskUser = new IModel($this->taskUserTable);
		// 当前有已有该任务未完成的不能再次领取
		if ($info = $taskUser->getObj('uid = ' . $this->uid . ' and task_id = ' . $this->taskInfo['id'] . ' and status != 1')) {
			return $this->error(0, '你有该类型的任务未完成，不能再次领取哦', array('user_task_id' => $info['user_task_id']));
		}

		$day = date('Y-m-d');
		$limit_day = (int) $this->taskInfo['param']['limit_day'];
		$info = $taskUser->getObj('uid = ' . $this->uid . ' and create_time like \'%' . $day . '%\'', 'count(user_task_id) as num');
		if ($limit_day && ($info['num'] >= $limit_day)) {
			return $this->error(2, '一天内限制最多只能领取' . $limit_day . '次哦！');
		}

		$data = array(
			'uid'         => $this->uid,
			'task_id'     => $this->taskInfo['id'],
			'create_time' => ITime::getDateTime(),
		);
		$taskUser->setData($data);
		if ($new_id = $taskUser->add()) {
			$task = new IModel($this->taskTable);
			$task->setData(array(
				'participants_counts' => 'participants_counts + 1',
			));
			$task->update('id = ' . $this->taskInfo['id'], 'participants_counts');
			return $this->success(1, '领取任务成功', array('user_task_id' => $new_id));
		} else {
			return $this->error(1, '领取任务失败');
		}
	}


	/**
	 * [执行任务（触摸任务）]
	 * @param  [type] $user_task_id [用户的任务]
	 * @return [type]      [array]
	 */
	public function touchTask($user_task_id)
	{
		$taskCode = $this->checkTaskStatus($user_task_id);
		if (0 !== $taskCode) {
			return $this->error('00' . $taskCode, $this->showTaskStasus($taskCode));
		}
		$taskShare = new IModel($this->taskShareTable);
		$day = date('Y-m-d');
		$new_id = 0;
		$ok = 1;
		// 当前已经分享过则不需要再插入记录
		if (!$taskShare->getObj('user_task_id = ' . $user_task_id . ' and share_time like \'%' . $day . '%\'')) {
			$taskShare->setData(array(
				'uid'          => $this->uid,
				'user_task_id' => $user_task_id,
				'share_time'   => ITime::getDateTime(),
			));
			if ($new_id = $taskShare->add()) {
				// return $this->success(1, 'ok');

				// 更新摸得时间
				$taskUser = new IModel($this->taskUserTable);
				$taskUser->setData(array(
					'update_time' => ITime::getDateTime(),
				));
				$taskUser->update('user_task_id = ' . $user_task_id);

				$ok = 2;
			} else {
				return $this->error(0, '执行失败');
			}
		}
		$this->touchTaskAfter($user_task_id, $new_id);
		return $this->success($ok, ($ok == 2 ? '转发成功' : '今日已转发，明天别忘了继续哦'));
	}


	/**
	 * [触摸任务之后的回调]
	 * @param  [type] $user_task_id [用户的任务]
	 * @return [void]               [没有返回值]
	 */
	private function touchTaskAfter($user_task_id, $new_id)
	{
		$arr = $this->getAdhereDay($user_task_id, true);
		if ($arr[0] == $arr[1]) {
			$taskUser = new IModel($this->taskUserTable);
			$taskUser->setData(array(
				'status' => 1,	// 任务完成
				'completion_time' => ITime::getDateTime(),
			));
			$taskUser->update('user_task_id = ' . $user_task_id);

			// 奖励的积分发放
			$member_model        = new IModel($this->memberTable);
			$member_model->setData(array('point' => 'point+' . $this->taskInfo['rewards']));
			$member_model->update('user_id = ' . $this->uid, array('point'));

		} elseif (1 === $arr[0] && $new_id) {
			$taskShare = new IModel($this->taskShareTable);
			$taskShare->setData(array(
				'status'          => 1,// 标记为不连续的分享
			));
			// 将不连续的记录都做标记（注意用的是 id < …… 哦）
			$taskShare->update('user_task_id = ' . $user_task_id . ' and id < ' . $new_id);
		}
	}


	// 获取任务状态
	public function getTaskStatus($user_task_id)
	{
		$code = $this->checkTaskStatus($user_task_id);
		return array('status' => $code, 'info' => $this->showTaskStasus($code));
	}


	// 获取任务进度
	public function getTaskProgress($user_task_id)
	{
		$taskCode = $this->checkTaskStatus($user_task_id);
		if (0 > $taskCode) {
			return $this->error('00' . $taskCode, $this->showTaskStasus($taskCode));
		}
		if (false == $this->isSad($user_task_id)) {
			$day = (int) $this->taskInfo['param']['day'];
			return array(0, $day, '非常遗憾，由于你没有坚持住，之前的努力都白费了，加油啊！');
		}
		$arr = $this->getAdhereDay($user_task_id, true);
		if ($arr[0] == $arr[1]) {
			return array($arr[0], $arr[1], '已圆满完成任务，你超棒的！');
		} else {
			if (0 == $arr[0]) {
				return array($arr[0], $arr[1], '开始任务吧，看好你哦');
			} else {
				return array($arr[0], $arr[1], '已连续分享' . $arr[0] . '天，继续加油哦！');
			}
		}
	}


	// 获取任务的参与情况
	public function getTaskParticipate()
	{
		$taskUser = new IModel($this->taskUserTable . ' t, user u');
		$res = $taskUser->query('t.uid = u.id and t.task_id = ' . $this->taskInfo['id'], 't.create_time,t.status,u.head_ico,u.username', 't.user_task_id', 'desc', 10);
		return $res;
	}


	// 获取我的任务参与情况
	public function getTaskUserParticipate($user_task_id)
	{
		$taskCode = $this->checkTaskStatus($user_task_id);
		if (0 > $taskCode) {
			return $this->error('00' . $taskCode, $this->showTaskStasus($taskCode));
		}
		$taskUser = new IModel($this->taskShareTable);
		$res = $taskUser->query('user_task_id = ' . $user_task_id . ' and uid = ' . $this->uid, '*', 'id', 'desc');
		return $res;
	}


	// 领取任务奖励
	public function toTaskRewards()
	{
	}


	/**
	 * [检测用户的任务]
	 * @param  [int] $user_task_id [用户的任务]
	 * @return [int]               [用户任务状态]
	 */
	private function checkTaskStatus($user_task_id)
	{
		$taskUser = new IModel($this->taskUserTable);
		if ($taskUserInfo = $taskUser->getObj('uid = ' . $this->uid . ' and user_task_id = ' . $user_task_id)) {
			$status = $taskUserInfo['status'];
			if ($taskUserInfo['task_id'] != $this->taskInfo['id']) {
				$status = -2;
			}
		} else {
			$status = -1;	// 你没有参与该任务
		}
		switch ($status) {
			case -2:
				$code = -2;	// 任务参数非法
				break;
			case -1:
				$code = -1;	// 你没有参与该任务
				break;
			case '0':
				$code = 0;	// 已领取（进行中）
				break;
			case '1':
				$code = 1;	// 已完成
				break;
			case '2':
				$code = 2;	// 已失败
				break;
			default:
				break;
		}
		return $code;
	}


	/**
	 * [获得任务状态]
	 * @param  [int] $code [状态代码]
	 * @return [string]       [任务状态]
	 */
	private function showTaskStasus($code)
	{
		switch ($code) {
			case -2:  $error = '任务参数非法'; break;
			case -1:  $error = '你没有参与该任务哦'; break;
			case 0:  $error  = '已领取（进行中）'; break;
			case 1:  $error  = '已完成'; break;
			case 2:  $error  = '已失败'; break;
			default:  $error = '未知状态';
		}
		return $error;
	}


	/**
	 * [算出已连续多少天转发的逻辑]（计算坚持了多少天）
	 * @param [int] $user_task_id [description]
	 * @param [mixed] $user_task_id [description]
	 */
	private function getAdhereDay($user_task_id, $count = false)
	{
		$day = (int) $this->taskInfo['param']['day'];
		$t   = time();
		$_t  = $t - ($day * 24 * 3600);
		$_t  = date('Y-m-d', $_t);
		$num = 0;
		$taskShare        = new IQuery($this->taskShareTable);
		$taskShare->where = 'uid = ' . $this->uid . ' and user_task_id = ' . $user_task_id . ' and share_time >= ' . $_t;
		$taskShare->order = 'id asc';
		$info = $taskShare->find();
		$_d = '';
		if (!empty($info)) {
			$num = 1;
			foreach ($info as $value) {
				$d = $value['share_time'];
				if ($_d) {
					if ($this->isContinuousDay($d, $_d)) {
						$num++;
					} else {
						$num = 1;
					}
				}
				$_d = $d;
			}
		}
		if ($count) {
			return array($num, $day);
		}
		if ($num == $day && $num == count($info)) {
			return true;
		} else {
			return false;
		}
	}


	// 判断两个日期是否为连续的两天
	private function isContinuousDay($d1, $d2) {
		return 1 === $this->apartDay($d1, $d2);
	}


	// 判断两个日期相隔天数（两个连续的天相隔一天 0 < day < 2（因int算法问题），同理相隔3天，2 < day < 4）
	// 原理：两天相隔 0 < day < 48h
	private function ApartDay($d1, $d2) {
		list($d1, ) = explode(' ', $d1);
		list($d2, ) = explode(' ', $d2);
		$d = (int) ((strtotime($d1) - strtotime($d2)) / (24 * 3600));
		return ($d < 1 ? 1 : $d);
	}


	// 判断任务是否“前功尽弃/功亏一篑”，好不悲哀
	private function isSad($user_task_id) {
		$res = true;
		$taskShare        = new IQuery($this->taskShareTable);
		$taskShare->where = 'uid = ' . $this->uid . ' and user_task_id = ' . $user_task_id;
		$taskShare->order = 'id desc';
		$taskShare->limit = 1;
		$shareInfo        = $taskShare->find();
		if (!empty($shareInfo)) {
			$shareInfo = current($shareInfo);
			$d         = date('Y-m-d H:i:s');
			$apartDay  = $this->ApartDay($d, $shareInfo['share_time']);
			if ($apartDay > 1) {
				$res = false;
			} else {
				$res = true;
			}
		}
		return $res;
	}
}