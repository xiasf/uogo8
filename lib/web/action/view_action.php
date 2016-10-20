<?php
/**
 * @file view_action.php
 * @brief 视图动作
 * @version 0.6
 */

/**
 * @class IViewAction
 * @brief 视图动作
 */
class IViewAction extends IAction
{
	//完整的视图路径地址(无扩展名)
	public $view;

	/**
	 * @brief 执行视图渲染
	 * @return 视图
	 */
	public function run()
	{
		$controller = $this->getController();
		IInterceptor::run("onCreateView",$controller);

		$this->view = $this->resolveView();
		if(is_file($this->view.$controller->extend))
		{
			$controller->render($this->view);
		}
		else
		{
			$path = $this->view.$controller->extend;
			$path = IException::pathFilter($path);
			IError::show("not found this view page({$path})",404);
		}
		IInterceptor::run("onFinishView");
	}

	/**
	 * @brief 生成模板完整物理路径
	 * @return string 完整的图片物理路径
	 */
	public function resolveView()
	{
		$viewPath = IFilter::act($this->getId(),'filename');

		//分割模板目录的层次
		$viewPath = strtr($viewPath,'-','/');
		return $this->getController()->getViewFile($viewPath);
	}
}