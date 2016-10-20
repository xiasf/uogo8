<?php
return array(
	//取商品数据
	'getGoodsInfo' => array(
		'query' => array(
			'name'   => 'goods as go',
			'where'  => 'id = #id# and is_del = 0',
			'fields' => 'go.name,go.id as goods_id,go.img,go.sell_price,go.point,go.weight,go.store_nums,go.exp,go.goods_no,0 as product_id,go.seller_id',
			'type'   => 'row',
		)
	),
	//取货品数据
	'getProductInfo' => array(
		'query' => array(
			'name'   => 'goods as go,products as pro',
			'where'  => 'pro.id = #id# and pro.goods_id = go.id and go.is_del = 0',
			'fields' => 'pro.sell_price,pro.weight,pro.id as product_id,pro.spec_array,pro.goods_id,pro.store_nums,pro.products_no as goods_no,go.name,go.point,go.exp,go.img,go.seller_id',
			'type'   => 'row',
		)
	),
	//取文章置顶列表
	'getArtList' => array(
		'query' => array(
			'name' => 'article',
			'where' => 'visibility = 1 and top = 1',
			'order' => 'sort ASC,id DESC',
			'fields'=> 'title,id,style,color',
			'limit' => '10'
		)
	),
	//团购列表
	'getRegimentList' => array(
		'query' => array(
			'name' => 'regiment',
			'where' => 'is_close = 0 and NOW() between start_time and end_time',
			'order' => 'id desc',
		)
	),
	//团购列表
	'getEverRegimentList' => array(
		'query' => array(
			'name' => 'regiment',
			'where' => 'is_close = 0 and NOW() > end_time',
			'order' => 'id desc',
		)
	),
	 //椐据ID团购
	'getRegimentRowById' => array(
		'query' => array(
			'name'  => 'regiment',
			'where' => 'id = #id# and NOW() between start_time and end_time',
			'type'  => 'row',
		)
	),
	//限时抢购列表
	'getPromotionList'=> array(
		'query' => array(
			'name' => 'promotion as p',
			'join' => 'left join goods as go on go.id = p.condition',
			'fields'=>'p.end_time,go.img as img,p.name as name,p.award_value as award_value,go.id as goods_id,p.id as p_id,end_time',
			'where'=>'p.type = 1 and p.is_close = 0 and go.is_del = 0 and NOW() between start_time and end_time AND go.id is not null',
			'order'=>'p_id desc',
			'limit'=>'10'
		)
	),
	//根据ID限时抢购
	'getPromotionRowById'=> array(
		'query' => array(
			'name'  => 'promotion',
			'fields'=> 'award_value,end_time,user_group,`condition`',
			'where' => 'type = 1 and `id` = #id# and NOW() between start_time and end_time',
			'type'  => 'row',
		)
	),
	//新品列表
	'getCommendNew' => array(
		'query' => array(
			'name' => 'commend_goods as co',
			'join' => 'left join goods as go on co.goods_id = go.id',
			'where' => 'co.commend_id = 1 and go.is_del = 0 AND go.id is not null',
			'fields' => 'go.img,go.sell_price,go.name,go.id,go.market_price',
			'limit'=>'10',
			'order'=>'sort asc,id desc'
		)
	),
	//特价商品列表
	'getCommendPrice' => array(
		'query' => array(
			'name' => 'commend_goods as co',
			'join' => 'left join goods as go on co.goods_id = go.id',
			'where' => 'co.commend_id = 2 and go.is_del = 0 AND go.id is not null',
			'fields' => 'go.img,go.sell_price,go.name,go.id,go.market_price',
			'limit'=>'10',
			'order'=>'sort asc,id desc'
		)
	),
	//热卖商品列表
	'getCommendHot' => array(
		'query' => array(
			'name' => 'commend_goods as co',
			'join' => 'left join goods as go on co.goods_id = go.id',
			'where' => 'co.commend_id = 3 and go.is_del = 0 AND go.id is not null',
			'fields' => 'go.img,go.sell_price,go.name,go.id,go.market_price',
			'limit'=>'10',
			'order'=>'sort asc,id desc'
		)
	),
	//推荐商品列表
	'getCommendRecom' => array(
		'query' => array(
			'name' => 'commend_goods as co',
			'join' => 'left join goods as go on co.goods_id = go.id',
			'where' => 'co.commend_id = 4 and go.is_del = 0 AND go.id is not null',
			'fields' => 'go.img,go.sell_price,go.name,go.id,go.market_price',
			'limit'=>'10',
			'order'=>'sort asc,id desc'
		)
	),
	//已配送的订单
	'getOrderDistributed' => array(
		'query' => array(
			'name' => 'order',
			'where' => 'distribution_status = 1 and if_del = 0',
			'limit' => '10',
			'order' => 'id desc'
		)
	),

	//根据品牌热卖商品列表
	'getCommendHotBrand'   => array(
		'query' => array(
			'name' => 'commend_goods as co',
			'join' => 'left join goods as go on co.goods_id = go.id',
			'where' => 'co.commend_id = 3 and go.is_del = 0 AND go.id is not null and go.brand_id = #brandid#',
			'fields' => 'go.img,go.sell_price,go.name,go.id',
			'limit'=>'10',
			'order'=>'sort asc,id desc'
		)
	),
	//导航列表
	'getGuideList'=>array(
		'query'=>array('name'=>'guide','limit'=>20)
	),
	//公告列表
	'getAnnouncementList'=>array(
		'query'=>array('name'=>'announcement','order'=>'id desc','limit'=>10)
	),
	//所有关键字列表
	'getKeywordAllList'=>array(
		'query'=>array('name'=>'keyword','order'=>'`order` asc','limit'=>50)
	),
	//所有商品分类
	'getcategoryList'=>array(
		'query'=>array('name'=>'category','order'=>'sort asc')
	),
	//根据商品分类取得商品列表
	'getCategoryExtendList'=>array(
	    'query'=>array(
	    	'name'  => 'category_extend as ca',
	    	'join'  => 'left join goods as go on go.id = ca.goods_id',
	    	'where' => ' ca.category_id = #categroy_id# and go.is_del = 0',
	    	'order' => ' go.sort asc,go.id desc',
	    	'limit' => 10,
	    )
	),
	//根据分类取销量排名列表
	'getCategoryExtendListByCategoryid'=>array(
	    'query'=>array(
	    	'name'  => 'category_extend as ca',
	    	'join'  => 'left join goods as go on ca.goods_id = go.id left join order_goods as ord on ord.goods_id = go.id',
	    	'where' => 'ca.category_id in (#categroy_id#)  and go.is_del = 0 and ord.goods_nums > 0',
	    	'fields'=> 'go.id,go.name,go.img,go.sell_price,SUM(ord.goods_nums) as sum',
		   	'order' => ' sum desc',
		   	'group' => ' ord.goods_id',
	    	'limit' => 10,
	    )
	),
	//所有一级分类
	'getCategoryListTop'=>array(
	    'query'=>array(
	    	'name'  => 'category',
	    	'where' => ' parent_id = 0 and visibility = 1 ',
	    	'order' => ' sort asc',
	    	'limit' => 20,
	    )
	),
	//根据一级分类输出二级分类列表
	'getCategoryByParentid'=>array(
	    'query'=>array(
	    	'name'  => 'category',
	    	'where' => ' parent_id = #parent_id# and visibility = 1 ',
	    	'order' => ' sort asc',
	    	'limit' => 10,
	    )
	),
	//所有品牌列表
	'getBrandList'=>array(
	    'query'=>array(
	    	'name'  => 'brand',
	    	'order' => ' sort asc',
	    	'limit' => 10,
	    )
	),
	//取得品牌详情
	'getBrandInfo'=>array(
	   'file' => 'brand.php','class' => 'APIBrand'
	),
	//取得商户详情
	'getSellerInfo'=>array(
	   'file' => 'seller.php','class' => 'APISeller'
	),
	//取得商户列表
	'getSellerInfo'=>array(
	   'file' => 'seller.php','class' => 'APISeller'
	),
	//取得VIP商户列表
	'getVipSellerList'=>array(
	    'query'=>array(
	    	'name'  => 'seller',
	    	'order' => ' sort asc ',
	    	'limit' => 10,
	    	'where' => 'is_del = 0 and is_vip = 1 and is_lock = 0',
	    )
	),
	//取得商户列表
	'getSellerList'=>array(
	   'file' => 'seller.php','class' => 'APISeller'
	),
	//最新评论列表
	'getCommentList'=>array(
	    'query'=>array(
	    	'name'  => 'comment as co',
	    	'join'  => 'left join goods as go on co.goods_id = go.id',
	    	'where' => ' co.status = 1 AND go.is_del = 0 AND go.id is not null',
	    	'fields'=> 'go.img as img,go.name as name,co.point,co.contents,co.goods_id',
	    	'order' => ' co.id desc',
	    	'limit' => 10,
	    )
	),
	//热门关键词列表
	'getKeywordList'=>array(
	 	 'query'=>array(
	    	'name'  => 'keyword',
	    	'where' => ' hot = 1',
	    	'order' => '`order` asc',
	    	'limit' => 5,
	    )
	),
	//帮助中心底部列表
	'getHelpCategoryFoot'=>array(
	 	 'query'=>array(
	    	'name'  => 'help_category',
	    	'where' => ' position_foot = 1',
	    	'order' => 'sort ASC,id desc',
	    	'limit' => 5,
	    )
	),
	//帮助中心左侧列表
	'getHelpCategoryLeft'=>array(
	 	 'query'=>array(
	    	'name'  => 'help_category',
	    	'where' => ' position_left = 1',
	    	'order' => 'sort ASC,id desc',
	    	'limit' => 5,
	    )
	),
	//取帮助中心列表
	'getHelpListByCatidAll'=>array(
	 	 'query'=>array(
	    	'name'  => 'help',
	    	'where' => ' cat_id =  #cat_id# ',
	    	'order' => 'sort ASC,id desc',
	    	'limit' => 5,
	    )
	),
	//文章分类
	'getArticleCategoryList'=>array(
	 	 'query'=>array(
	    	'name'  => 'article_category',
	    	'where' => ' issys = 0 ',
	    	'order' => 'sort ASC',
	    	'limit' => 10,
	    )
	),
	//文章详情
	'getArticleCategoryInfo'=>array(
		'file' => 'article.php','class' => 'APIArticle'
	),
	//文章列表
	'getArticleList' => array(
		'file' => 'article.php','class' => 'APIArticle'
	),
	//根据分类读列表
	'getArticleListByCatid' => array(
		'file' => 'article.php','class' => 'APIArticle'
	),
	//公告列表
	'getNoticeList' => array(
		'file' => 'notice.php','class' => 'APINotice'
	),
	//品牌分类
	'getBrandCategory'=>array(
	    'query'=>array(
	    	'name'  => 'brand_category',
	    	'order' => ' id desc',
	    )
	),
	//查找相关分类
	'getBrandListWhere'=>array(
	    'query'=>array(
	    	'name'  => 'brand',
	    	'where'=> "category_ids like '%#id#%'",
	    	'order' => ' sort asc',
	    )
	),
	//根据品牌销量排名列表
	'getGoodsListBrandSum'=>array(
	    'query'=>array(
	    	'name'   => 'goods as go',
	    	'join'   => 'left join order_goods as ord on ord.goods_id = go.id',
	    	'fields' => 'go.id,go.name,go.img,go.sell_price,SUM(ord.goods_nums) as sum',
	    	'where'  => " go.brand_id = #brandid#",
	    	'order'  => ' sum desc',
	    	'group'  => ' ord.goods_id',
	    	'limit'  => 10,
	    )
	),
	//根据商家销量排名列表
	'getGoodsListBySellerid'=>array(
	    'query'=>array(
	    	'name'   => 'goods as go',
	    	'join'   => 'left join order_goods as ord on ord.goods_id = go.id',
	    	'fields' => 'go.id,go.name,go.img,go.sell_price,SUM(ord.goods_nums) as sum',
	    	'where'  => " go.seller_id = #seller_id#",
	    	'order'  => ' sum desc',
	    	'group'  => ' ord.goods_id',
	    	'limit'  => 10,
	    )
	),
	//根据商家取商品列表
	'getGoodsListBySelleridList'=>array(
	    'query'=>array(
	    	'name'   => 'goods',
	    	'fields' => 'id,img,name,sell_price',
	    	'where'  => "  seller_id = #seller_id# AND is_del = 0",
	    	'order'  => ' sort asc,id desc',
	    	'limit'  => 10,
	    )
	),
	//帮助中心列表
	'getHelpList' => array(
		'file' => 'help.php','class' => 'APIHelp'
	),
	//根据分类取帮助中心列表
	'getHelpListByCatId' => array(
		'file' => 'help.php','class' => 'APIHelp'
	),
	//根据分类取推荐商品
	'getCategoryExtendByCommendid'=>array(
	    'query'=>array(
	    	'name'  => 'category_extend as ca',
	    	'join'  => 'left join goods as go on ca.goods_id = go.id left join commend_goods as co on co.goods_id = go.id',
	    	'where' => 'ca.category_id in (#childId#) and co.commend_id = 4 and go.is_del = 0',
	    	'fields'=> 'DISTINCT go.id,go.img,go.sell_price,go.name,go.market_price,go.description',
		   	'order' => 'go.sort asc,go.id desc',
	    	'limit' => 6,
	    )
	),
	//根据商品分类获取品牌
	'getCategoryExtendByBrandid'=>array(
	    'query'=>array(
	    	'name'  => 'category_extend as ca',
	    	'join'  => 'left join goods as go on ca.goods_id = go.id left join brand as b on b.id = go.brand_id',
	    	'where' => 'ca.category_id in ( #childId# ) and go.is_del = 0 and go.brand_id != 0',
	    	'fields'=> 'DISTINCT b.id,b.name',
	    	'limit' => 10,
	    )
	),

	//判断是否有评价商品的功能
	'getCommentByGoodsid'=>array(
	    'query'=>array(
	    	'name'  => 'comment',
	    	'where' => ' status = 0 and goods_id = #id# and user_id = #user_id#',
	    	'fields'=> 'id',
	    )
	),
	//帮助中心内容
	'getHelpContent'=>array(
	 	 'query'=>array(
	    	'name'  => 'help',
	    	'where' => ' id =  #id# ',
	    	'fields'=> 'content',
	    	'limit' => 1,
	    )
	),
	//查找关键字
	'getKeywordByWord'=>array(
		'query'=>array(
			'name'=>'keyword',
			'where'=>'word like "%#word#%" and word != "#word#"',
			'limit'=>10
		)
	),
	//查找商品
	'getGoodsCategoryExtend'=>array(
		'query'=>array(
			'name'  => 'goods as go',
			'join'  => 'left join category_extend as ca on go.id = ca.goods_id left join category as c on c.id = ca.category_id',
			'where' => 'go.name like "%#word#%" or FIND_IN_SET("#word#",search_words)',
			'fields'=> 'c.name,c.id,count(*) as num',
			'group' => 'ca.category_id',
			'limit' => 20
		)
	),
	//查找关键字取销量排名
	'getGoodsListByWordSum'=>array(
	    'query'=>array(
	    	'name'   => 'goods as go',
	    	'join'   => 'left join category_extend as ca on ca.goods_id = go.id left join order_goods as ord on ord.goods_id = go.id',
	    	'fields' => 'go.id,go.name,go.img,go.sell_price,SUM(ord.goods_nums) as sum',
	    	'where'  => 'go.name like "%#word#%" or FIND_IN_SET("#word#",search_words) and ord.goods_nums > 0 #whereCat#',
	    	'fields' => 'go.id,go.name,go.img,go.sell_price,SUM(ord.goods_nums) as sum',
	    	'order'  => 'sum desc',
	    	'group'  => 'ord.goods_id',
	    	'limit'  => 10,
	    )
	),
	//用户中心-账户余额
	'getUcenterAccoutLog' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-我的建议
	'getUcenterSuggestion' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-商品讨论
	'getUcenterConsult' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-商品评价
	'getUcenterEvaluation' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-收藏夹
	'getUcenterFavoriteByCatid'=>array(
		'query'=>array(
			'name'=>'favorite as f,category as c ',
			'where'=>'f.user_id = #user_id# and f.cat_id = c.id ',
			'fields'=> 'count(*) as num,c.name,c.id ',
			'group'=> 'cat_id',
		)
	),
	//用户中心-个人资料
	'getMemberInfo' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-个人主页统计
	'getMemberTongJi' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-个人主页统计
	'getPropTongJi' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-订单列表
	'getOrderListByUserid'=>array(
	 	 'query'=>array(
	    	'name'  => 'order',
	    	'where' => ' user_id = #user_id# and if_del = 0 ',
	    	'order'=> 'id desc',
	    	'limit' => 6,
	    )
	),
	//用户中心-感兴趣的商品
	'getGoodsByCommendgoods'=>array(
	 	 'query'=>array(
	    	'name'  => 'goods',
	    	'where' => 'id in( select goods_id from commend_goods where commend_id = 4 ) and is_del = 0 ',
	    	'limit' => 12,
	    )
	),
	//用户中心-积分列表
	'getUcenterPointLog' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-代金券列表
	'getTicketList'=>array(
	 	 'query'=>array(
	    	'name'  => 'ticket',
	    	'where' => 'point > 0 and start_time <= "#nowtime#" and end_time > "#nowtime#" ',
	    	'limit' => 20,
	    )
	),
	//用户中心-信息列表
	'getUcenterMessageList' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-订单列表
	'getOrderList' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-订单中商品列表
	'getOrderGoodsListByGoodsid'=>array(
	 	 'query'=>array(
	    	'name'  => 'order_goods as og',
	    	'join'  => 'left join goods as go on og.goods_id = go.id',
	    	'where' => 'order_id = #order_id# ',
	    	'fields'=> 'og.*,go.point',
	    )
	),
	//用户中心-我的代金券
	'getPropList' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-退款记录
	'getRefundmentDocList' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//用户中心-提现记录
	'getWithdrawList' => array(
		'file' => 'ucenter.php','class' => 'APIUcenter'
	),
	//快捷登录
	'getOauthList'=>array(
	 	 'query'=>array(
	    	'name'  => 'oauth',
	    	'where' => "is_close=0",
	    )
	),
	//查看大图相册
	'getGoodsPhotoRelationList'=>array(
	 	 'query'=>array(
	    	'name'  => 'goods_photo_relation AS a ',
	    	'join'  => "left join goods_photo AS b ON a.photo_id = b.id",
	    	'where' => "a.goods_id = #id# ",
	    	'order' => "a.id DESC",
	    	'fields' => "a.*,b.img",
	    )
	),
	//地区列表
	'getAreasListTop'=>array(
	 	 'query'=>array(
	    	'name'  => 'areas',
	    	'where' => "parent_id =0 ",
	    )
	),
	//购买本商品的。也购买的商品列表
	'getOrderGoodsByBuyerid'=>array(
	 	 'query'=>array(
	    	'name'  => 'order_goods as og',
	    	'join' => "join order as o on og.order_id = o.id left join goods as lg on lg.id = og.goods_id AND lg.is_del = 0",
	    	'where' => "o.user_id in (#buyer_id#) AND lg.id is not null",
	    	'fields' => "DISTINCT lg.id,lg.sell_price as price,lg.img,lg.name",
	    	'order' => "o.completion_time desc ",
	    	'limit' => 5,
	    )
	),
	//支付列表
	'getPaymentList'=>array(
		'file'  => 'other.php','class' => 'APIOther',
	),
	//充值支付列表
	'getPaymentListByOnline'=>array(
		'file'  => 'other.php','class' => 'APIOther',
	),
	//根据分类读品牌
	'getBrandListByGoodsCategoryId' => array(
		'file' => 'brand.php','class' => 'APIBrand'
	),
	//获取促销规则
	'getProrule' => array(
		'file' => 'other.php','class' => 'APIOther'
	),
	//获取配送方式
	'getDeliveryList' => array(
		'query' => array(
			'name' => 'delivery',
			'where'=> 'is_delete = 0 and status = 1',
		)
	),
);