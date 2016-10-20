<?php
/**
 * @file action.php
 * @brief action 基类文件
 */

/**
 * @class IAction
 * @brief action 基类文件
 */
class IAction
{
	protected $id;
	protected $controller;

	/**
	 * @brief IAction的构造函数
	 * @param object $controller 控制器对象
	 * @param string $id         动作名称
	 */
	public function __construct($controller, $id)
	{
		$this->controller = $controller;
		$this->id = $id;
	}

	/**
	 * @brief 获取当前动作的controller
	 * @return object 当前的控制器对象
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * @brief 获取当前动作Id
	 * @return string 动作名称
	 */
	public function getId()
	{
		return $this->id;
	}
}