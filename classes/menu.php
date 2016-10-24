<?php
/**
 * @file menu.php
 * @brief 后台系统菜单管理
 * @class Menu
 * @note
 */
class Menu
{
	private static $commonMenu = array('/system/default');
	public $current;
	private $adminRights = '';//管理员的权限

	/**
	 * @brief 构造函数
	 * @param array checkrights里面的admin对象数据
	 */
	public function __construct($admin)
	{
		$adminObj = new IModel('admin');
		$adminRow = $adminObj->getObj('admin_name = "'.$admin['admin_name'].'"');
		if($adminRow && ($adminRow['password'] == $admin['admin_pwd']) && ($adminRow['is_del'] == 0))
		{
			//根据角色分配权限
			if($adminRow['role_id'] == 0)
			{
				$this->adminRights = 'administrator';
			}
			else
			{
				$roleObj = new IModel('admin_role');
				$where   = 'id = '.$adminRow["role_id"].' and is_del = 0';
				$roleRow = $roleObj->getObj($where);
				$this->adminRights = isset($roleRow['rights']) ? $roleRow['rights'] : '';
			}
		}
	}

    //菜单的配制数据
	private static $menu = array(
		'商品'=>array(
			'商品管理'=>array(
				'/goods/goods_list' => '商品列表',
				'/goods/goods_edit' => '商品添加',
				'/goods/goods_recycle_list' => '商品回收站'
			),
			'商品分类'=>array(
				'/channel/channel_list'	=>	'频道列表',
				'/goods/category_list'	=>	'分类列表',
				'/goods/category_edit'	=>	'添加分类'
			),
			'品牌'=>array(
				'/brand/category_list'  =>	'品牌分类',
				'/brand/brand_list'		=>	'品牌列表'
			),
			'推荐标签'=>array(
				'/brand/best_list'  =>	'标签列表',
				'/brand/best_edit'  =>	'添加标签'
			),
			'服务标签'=>array(
				'/goods/svcpro_list'  =>	'服务列表',
				'/goods/svcpro_edit'  =>	'添加服务'
			),
			'模型'=>array(
				'/goods/model_list'=>'模型列表',
				'/goods/spec_list'=>'规格列表',
				'/goods/spec_photo'=>'规格图库',
				'/goods/spec_recycle_list' => '规格回收站'
			),
			'搜索'=>array(
				'/tools/keyword_list' => '关键词列表',
				'/tools/search_list' => '搜索统计'
			)
		),

		'会员'=>array(
			'会员管理'=>array(
	    		'/member/member_list' => '会员列表',
				'/member/recycling'  => '会员回收站',
	     		'/member/group_list' => '会员组列表',
			),
			'会员提现'=>array(
	     		'/member/withdraw_list'=>'会员提现',
				'/member/withdraw_recycle/type/del' => '提现回收站'
			),
			'信息处理' => array(
				'/comment/suggestion_list'  => '建议管理',
				'/comment/refer_list'		=> '咨询管理',
				'/comment/discussion_list'	=> '讨论管理',
				'/comment/comment_list'		=> '评价管理',
				'/comment/message_list'		=> '站内消息',
				'/message/notify_list'      => '到货通知',
				'/message/registry_list'    => '邮件订阅',
			),
		),
		
		'商户'=>array(
			'商户管理' => array(
				'/member/seller_list' => '商户列表',
				'/member/seller_recycle_list' => '商户回收站',
				'/member/seller_edit' => '添加商户',
			),
			'店铺管理' => array(
				'/member/shop_list' => '店铺列表',
				'/member/shop_recycle_list' => '店铺回收站',
				'/member/shop_edit' => '添加店铺',
			),
			'商户货款'=>array(
				'/market/bill_list' => '结算申请',
				'/market/order_goods_list' => '结算明细',
			),
			'商圈管理' => array(
				'/district/district_list'  => '商圈管理',
				'/district/district_edit'  => '添加商圈'
			),
		),

	   '订单'=>array(
        	'订单管理'=>array(
                '/order/order_list' => '订单列表',
                '/order/order_edit' => '添加订单'
        	),
        	'单据管理'=>array(
             	'/order/order_collection_list'  => '收款单',
             	'/order/order_refundment_list'  => '退款单',
        		'/order/order_delivery_list'    => '发货单',
        		'/order/refundment_list'        => '退款申请列表',
        	),
        	'发货地址'=>array(
        		'/order/ship_info_list'         => '发货地址管理',
        	),
		),

		'活动'=>array(
        	'促销活动' => array(
        		'/market/pro_rule_list' => '促销活动列表'
        	),
        	'营销活动' => array(
        		'/market/pro_speed_list' => '限时抢购',
        		'/market/regiment_list'  => '团购',
				'/market/sale_list'      => '特价',
        	),
        	'代金券管理'=>array(
        		'/market/ticket_list'       => '代金券列表',
        	),
        	'活动管理'=>array(
        		'/activity/activity_list'       => '活动列表',
        		'/activity/activity_cat_list'       => '活动分类',
        		'/activity/activity_signup_list'	=> '参与情况',
        	),
		),

		'统计'=>array(
			'基础数据统计'=>array(
      			'/market/user_reg' 	   => '用户注册统计',
				'/market/spanding_avg' => '人均消费统计',
      			'/market/amount'       => '销售金额统计'
			),
			'日志操作记录'=>array(
				'/market/account_list'   => '充值操作记录',
				'/market/operation_list' => '后台操作记录',
			),
		),


        '网站'=>array(
    		'后台首页'=>array(
    			'/system/default' => '后台首页',
    		),
        	'网站管理'=>array(
        		'/system/conf_base' => '网站设置',
        		// '/system/conf_ui'   => '主题设置',
        	),
			'文章管理'=>array(
				'/tools/article_cat_list'=> '文章分类',
				'/tools/article_list'=> '文章列表'
			),

			'帮助管理'=>array(
   				'/tools/help_cat_list'=> '帮助分类',
   				'/tools/help_list'=> '帮助列表'
   			),

   			'公告管理'=>array(
   				'/tools/notice_list'=> '公告列表',
   				'/tools/notice_edit'=> '公告发布'
   			),
   			'短信提醒'=>array(
   				'/system/conf_smsbase'=> '短信提醒设置',
   			),
   			'浏览统计'=>array(
   				'/bg_online/online'=> '当前在线',
   				'/bg_online/user_history'=> '浏览历史',
   			),
		),
		'区块'=>array(
   			'区块管理'=>array(
   				'/tools/ad_position_list'=> '区块位列表',
   				'/tools/ad_list'=> '区块列表'
   			),
		),
       '配置'=>array(
			'数据库管理'=>array(
				'/tools/db_bak' => '数据库备份',
				'/tools/db_res' => '数据库还原',
			),
        	'支付管理'=>array(
            	'/system/payment_list' => '支付方式'
        	),
        	'第三方平台'=>array(
            	'/system/oauth_list' => 'oauth登录列表',
            	'/system/wechat' => '微信平台',
            	'/system/hsms' => '手机短信平台',
        	),
        	'配送管理'=>array(
            	'/system/delivery'  	=> '配送方式',
        		'/system/freight_list'	=> '物流公司',
	    		'/system/takeself_list' => '自提点列表',
	    		'/system/takeself_edit'  => '添加自提点',
        	),
        	'地域管理'=>array(
        		'/system/area_list' => '地区列表',
        	),
        	'权限管理'=>array(
        		'/system/admin_list' => '管理员',
        		'/system/role_list'  => '角色',
        		'/system/right_list' => '权限资源'
        	),			
     		'网站地图'=>array(
            	'/tools/seo_sitemaps' => '网站搜索地图',
			)
		),
       /*
		'家居建材'=>array(
   			'装修效果图'=>array(
   				'/jiaju/show_img_list'=> '效果图库',
   				'/jiaju/show_img_cat_list'=> '分类列表',
   			),
   			'装修公司'=>array(
   				'/jiaju/company_list'=> '装修公司',
   			),
   			'设计师'=>array(
   				'/jiaju/designer_list'=> '设计师',
   				'/jiaju/designer_works_list'=> '设计作品',
   			),
   			'文章'=>array(
   				'/jiaju/article_list'=> '文章列表',
   				'/jiaju/article_edit'=> '添加文章',
   				'/jiaju/article_cat_list'=> '文章分类',
   				'/jiaju/article_cat_edit'=> '添加分类',
   				'/jiaju/article_comment_list'=> '评论列表',
   			),
   			'装修报名'=>array(
   				'/jiaju/zx_post_list'=> '报名列表',
   			),
   			'招标'=>array(
   				'/bg_zhaobiao/zb_home_list'=> '楼盘列表',
   				'/bg_zhaobiao/zb_home_edit'=> '添加楼盘',
   				'/bg_zhaobiao/zb_edit'=> '添加招标',
   				'/bg_zhaobiao/zb_list'=> '招标列表',
   			),
		),
		*/
		'任务' => array(
			'任务管理' => array(
				'/bg_task/task_list' => '任务列表',
				'/bg_task/task_edit' => '添加任务',
			),
			'任务模型' => array(
				'/bg_task/model_list' => '模型列表',
				'/bg_task/model_edit' => '添加模型',
			),
			'积分商城' => array(
				'/bg_task/goods_list' => '商品列表',
				'/bg_task/goods_edit' => '添加商品',
				'/bg_task/order_list' => '兑换列表',
			),
		),
	);

	/**
	 * @brief 对于menu列表未定义的进行映射别名操作 key => 当前URL地址 ， value => 替换的URL地址
	 */
	private static $menu_non_display = array(
		'/system/navigation' => '/system/default',
		'/system/navigation_edit' => '/system/default',
		'/system/navigation_recycle' => '/system/default',
		'/system/delivery_edit' => '/system/delivery',
		'/system/delivery_recycle' => '/system/delivery',

		//'/member/recycling' => '/member/member_list',

		'/tools/article_edit_act'=>'/tools/article_list',

		'/market/ticket_edit' => '/market/ticket_list',
		'/market/bill_edit' => '/market/bill_list',

		'/order/collection_show' => '/order/order_collection_list',
		'/order/refundment_show' => '/order/order_refundment_list',
		'/order/delivery_show' => '/order/order_delivery_list',
		'/order/refundment_doc_show' => '/order/refundment_list',
		'/order/print_template' => '/order/order_list',
		'/order/collection_recycle_list' => '/order/order_collection_list',
		'/order/delivery_recycle_list' => '/order/order_delivery_list',
		'/order/ship_recycle_list'	=>	'/order/ship_info_list',
		'/order/expresswaybill_edit' => '/order/order_list',
		'/order/order_show' => '/order/order_list',
	);

    /**
     * @brief 根据用户的权限过滤菜单
     * @return array
     */
    private function filterMenu()
    {
    	$rights = $this->adminRights;

		//如果不是超级管理员则要过滤菜单
		if($rights != 'administrator')
		{
			foreach(self::$menu as $firstKey => $firstVal)
			{
				if(is_array($firstVal))
				{
					foreach($firstVal as $secondKey => $secondVal)
					{
						if(is_array($secondVal))
						{
							foreach($secondVal as $thirdKey => $thirdVal)
							{
								if(!in_array($thirdKey,self::$commonMenu) && (stripos(str_replace('@','/',$rights),','.substr($thirdKey,1).',') === false))
								{
									unset(self::$menu[$firstKey][$secondKey][$thirdKey]);
								}
							}
							if(empty(self::$menu[$firstKey][$secondKey]))
							{
								unset(self::$menu[$firstKey][$secondKey]);
							}
						}
					}
					if(empty(self::$menu[$firstKey]))
					{
						unset(self::$menu[$firstKey]);
					}
				}
			}
		}
    }

    /**
     * @brief 取得当前菜单应该生成的对应JSON数据
     * @param boolean $is_auto 是否智能匹配菜单
     * @return Json
     */
	public function submenu($is_auto = false)
	{
		$result     = array();
		$controller = IWeb::$app->getController()->getId();
		$action     = IWeb::$app->getController()->getAction()->getId();

		//当前菜单无定义时做映射别名
		$this->current = '/'.$controller.'/'.$action;
		if(isset(self::$menu_non_display[$this->current]))
		{
			$this->current = self::$menu_non_display[$this->current];
		}
		else
		{
			$actionArray = explode("_",$action);
			$model = current($actionArray);
		}

		$find_current = false;

		//过滤无操作权限的菜单
		$this->filterMenu();

		foreach(self::$menu as $key => $value)
		{
			$item = array();
			$item['current'] = false;
			$item['title']   = $key;

			foreach($value as $big_cat_name => $big_cat)
			{
				foreach($big_cat as $link => $title)
				{
					//把菜单的第一连接项分给顶级菜单
					if(!isset($item['link']))
					{
						$item['link'] = $link;
					}

					if($find_current)
					{
						break;
					}

					//遍历菜单项找到与当前连接相同的项目
					if( $link == $this->current || ($is_auto == true && isset($model) && preg_match("!^/[^/]+/{$model}_!",$link) ) )
					{
						$item['current'] = $find_current = true;
						foreach($value as $k => $v)
						{
							foreach($v as $subMenuKey => $subMenuName)
							{
								$tmpUrl = IUrl::creatUrl($subMenuKey);
								unset($value[$k][$subMenuKey]);
								$value[$k][$tmpUrl]['name'] = $subMenuName;
								$value[$k][$tmpUrl]['urlPathinfo'] = $subMenuKey;
							}
						}
						$item['list'] = $value;
					}
				}

				if($find_current)
				{
					break;
				}
			}
			$item['link'] = IUrl::creatUrl($item['link']);
			$result[] = $item;
		}

		if($find_current == false && $is_auto == false)
		{
			return $this->submenu(true);
		}

		return JSON::encode($result);
	}
}