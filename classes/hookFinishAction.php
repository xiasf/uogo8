<?php
/**
 * @file hookFinishAction.php
 * @brief 对action动作进行拦截，对部分需要钩子的action设置代码调用
 *        钩子名称为： function 控制器ID_动作ID,遇到此动作时优先调用钩子方法
 */
class hookFinishAction extends IInterceptorBase
{
	//根据控制器ID和动作ID生成钩子方法名
	public static function getHookRule()
	{
		$ctrlId  = IWeb::$app->getController()->getId();
		//$actionId= IWeb::$app->getController()->getAction()->getId();
		return join('_',array($ctrlId,$actionId));
	}

	//createAction拦截器统一入口
	public static function onFinishAction()
	{
		$hookName = self::getHookRule();
		if(method_exists(__CLASS__,$hookName))
		{
			call_user_func(array(__CLASS__,$hookName));
		}
	}
}