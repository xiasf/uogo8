<?php
/**
 * @brief 商家模块
 * @class Seller
 */
class Seller extends IController
{
	public $layout = 'seller_min';

	/**
	 * @brief 初始化检查
	 */
	public function init()
	{
		IInterceptor::reg('CheckRights@onCreateAction');
	}


	// 商圈匹配
	function autoComplete_district() {
		$name = IFilter::act(IReq::get('name'),'text');
		$cb = IFilter::act(IReq::get('cb'),'text');
		$isError = true;
		$result    = array();
		$where = empty($name)?'name is not null':'name like "'.$name.'%"';
		$wordObj  = new IModel('district');
		$wordList = $wordObj->query($where,'name, id');

		if(!empty($wordList))
		{
			$result = $wordList;
		}
		$date = array();
		foreach ($result as $key => $value) {
			$date['s'][$key]['id'] = $value['id'];
			$date['s'][$key]['name'] = $value['name'];
		}
		echo JSON::encode($date);
	}


	// 店铺编辑
	function shop_edit() {
		$id = IFilter::act(IReq::get('id'),'int');
		if ($_POST) {
	    	$shop = new IModel('seller');
	    	$arr = array(
	    			'shopname' => IFilter::act(IReq::get('shopname')),
	    			'logo' => IFilter::act(IReq::get('logo')),
	    			'weixin_logo' => IFilter::act(IReq::get('weixin_logo')),
	    			'address' => IFilter::act(IReq::get('address')),
	    			'addregion' => IFilter::act(IReq::get('addregion')),
	    			'addressreference' => IFilter::act(IReq::get('addressreference')),
	    			'district_id' => IFilter::act(IReq::get('district_id'), 'int'),
	    			'mobile' => IFilter::act(IReq::get('mobile')),
	    			'lat' => IFilter::act(IReq::get('lat'), 'float'),
	    			'lng' => IFilter::act(IReq::get('lng'), 'float'),
	    			'is_vis' => IFilter::act(IReq::get('is_vis'), 'int'),
	    			'sort' => IFilter::act(IReq::get('sort'),'int'),
	    			'seller_user_id' => $this->seller['seller_id']
	    		);
	    	$shop->setData($arr);

	    	if($id)
	    	{
	    		$shop->update('id='.$id." and seller_user_id = ".$this->seller['seller_id']);
	    	}
	    	else
	    	{
	    		$shop->add();
	    	}
			$this->redirect('shop_list');

		} else {
			$shop   = new IModel("seller");
			$this->data = $shop->getObj("id=".$id." and seller_user_id = ".$this->seller['seller_id']);
			if (!$this->data && $id) {
				$this->redirect('shop_list');
			}
			$this->setRenderData($this->data);
			$this->redirect('shop_edit');
		}
	}


/*	// 取得全部子分类
	private function category_x($id) {
		$arr = array();
		$arr[] = $id;
		$query = new IQuery("seller_category");
		$query->where = "parent_id = ".$id.' and seller_id = ' . $this->seller['seller_id'];
		$items = $query->find();
		foreach ($items as $value) {
			$arr = array_merge($arr, $this->category_x($value['id']));
		}
		return $arr;
	}*/


	// 获取全部子分类
	public function getCallChidCategory($id) {
		$result = array($id);
		$catDB  = new IModel('seller_category');
		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$temp = $catDB->query( 'parent_id = '.$id.' and seller_id = ' . $this->seller['seller_id']);
			foreach($temp as $key => $val)
			{
				$result[] = $val['id'];
			}
			next($result);
		}
		return $result;
	}


	// 商家分类删除
	function seller_cat_del() {
		$goods_list = array();
		$id = IFilter::act(IReq::get('id'),'int');
		$cat_list = $this->getCallChidCategory($id);

		if (!$cat_list) exit('分类不存在！');

		$seller_category_extend = new Imodel('seller_category_extend');
		$tem = $seller_category_extend->query('category_id in (' . join(',', $cat_list) . ')');
		foreach ($tem as $value) {
			$goods_list[] = $value['goods_id'];
		}

		if ($goods_list) {
			$goods = new Imodel('goods');
			$goods->setDate(array('is_del' => 1));
			$goods->update('id in (' . join(',', $goods_list) . ')');
		}

		$seller_category = new Imodel('seller_category');
		$seller_category->del('id in (' . join(',', $cat_list) . ')');

		echo JSON::encode(array('isError'=>false, 'message'=>'ok'));
	}


	// 分类“树”关系检查（$a, $b）检查a-b的关系兄弟同级，父子级，
	// code


	// 任意数组 转 层级结构
	// 数组结构必须包含pid层级关系和顺序 此顺序非sort排序，数组本身就是排序，请自行决定从数据库中读取出来的顺序即可）
	// 注意此方法不参与排序，实际上转换方法自始至终都没有参与排序规则，转换时设置sort和pid只是为了数据结构的需要，它只是数据结构的承载者！
	// 必须一次取出全部数据才有意义，不能分页，否则将会是“错误不完整意义”的数据结构
	//  update：2016-3-11 01:06:58
	function create_array_tree($arr,$pid=0) {
        $ret = array();
        foreach($arr as $k => $v) {
            if($v['pid'] == $pid) {
                $tmp = $arr[$k];
                unset($arr[$k]);
                $tmp['children'] = $this->create_array_tree($arr, $v['id']);
                if (!$tmp['children']) unset($tmp['children']);
                $ret[] = $tmp;
            }
        }
        return $ret;
    }



	// 层级结构 转 数组 
	// json的结构包含了两个重要的信息：排序和层级
	// 需解密排序和pid层级两个信息
	//  update：2016-3-11 01:06:58
	function par_array_tree($a, $pid = 0)
	{
		$sort = 1; // 同级排序
		$array = array();
		foreach ($a as $key => $value) {
			$value['sort'] = $sort++;

			$value['pid'] = $pid;
			$tem = isset($value['children']) ? $value['children'] : '';
			if(isset($value['children'])) unset($value['children']);
			$array[] = $value;

			if (isset($tem) && is_array($tem))
				$array = array_merge($array, $this->par_array_tree($tem, $value['id']));
		}
		return $array;
	}

	
	// Nestable 插件专用，增删改统一处理  update：2016-3-10 17:20:14
	public function seller_cat_edit() {
		$array = $this->par_array_tree(json_decode(IReq::get('str', 'post'), true));
		foreach ($array as $key => $value) {
			$_POST['id'] = $value['id'];
			$_POST['parent_id'] = $value['pid'];
			$_POST['name'] = $value['name'];
			$_POST['sort'] = $value['sort'];
			$_POST['shop_id'] = IReq::get('shopid', 'post');
			$this->seller_cat_edit_act(1);
		}
		die(JSON::encode(array('result' => 'succeed','data' => '')));
	}


	// public function category_ajax_sort() {
	// 	$id = IFilter::act(IReq::get('id'),'int');
	// 	$sort = IFilter::act(IReq::get('sort'),'int');
	// 	$seller_category = new IModel('seller_category');
	// 	$seller_category->setData(array('sort' => $sort));
	// 	if (false != $seller_category->update("id=$id"))
	// 		die(JSON::encode(array('result' => 'succeed','data' => '')));
	// 	else
	// 		die(JSON::encode(array('result' => 'fail','data' => '')));
	// }

	// public function category_ajax_del()
	// {
	// 	$id = IFilter::act( IReq::get('id'),'int');
	// 	$seller_category = new IModel('seller_category');
	// 	if ($seller_category->del("id in (" . implode(',', $this->category_x($id)).")"))
	// 		die(JSON::encode(array('result' => 'succeed','data' => '')));
	// 	else
	// 		die(JSON::encode(array('result' => 'fail','data' => '')));
	// }


	// public function category_list() {
	// 	$seller_category  = new IModel('seller_category');
	// 	$tem = array();
	// 	$seller_category_list = $seller_category->query('seller_id = ' . $this->seller['seller_id'] . ' order by sort asc,id desc');

	// 	foreach ($seller_category_list as $key => $value) {
	// 		$tem[$key]['id'] = $value['id'];
	// 		$tem[$key]['pid'] = $value['parent_id'];
	// 		$tem[$key]['name'] = $value['name'];
	// 	}

	// 	$a = $this->create_array_tree($tem);

	// 	$this->data_json = json_encode($a);
	// 	$this->redirect('category_list');
	// }


	// 取得店铺的分类
	function seller_cat_shop_getjson() {
		$shop_id = IFilter::act(IReq::get('id'),'int');

		$seller_category  = new IModel('seller_category');
		$tem = array();
		$seller_category_list = $seller_category->query('shop_id = ' . $shop_id . ' order by sort asc,id desc');

		foreach ($seller_category_list as $key => $value) {
			$tem[$key]['id'] = $value['id'];
			$tem[$key]['pid'] = $value['parent_id'];
			$tem[$key]['name'] = $value['name'];
		}

		$a = $this->create_array_tree($tem);

		echo json_encode($a);
		// $this->redirect('category_list');
	}

	// 修改店铺分类
	private function seller_cat_edit_act($Nestable = 0) {
	    $id = IFilter::act(IReq::get('id'),'int');
		if ($_POST) {
	    	$parent_id = IFilter::act(IReq::get('parent_id'),'int');
	    	$name = IFilter::act(IReq::get('name'));
	    	$sort = IFilter::act(IReq::get('sort'),'int');
	    	$shop_id = IFilter::act(IReq::get('shopid'),'int');
	    	$seller_category = new IModel('seller_category');

			if ($id && $parent_id == $id) {
				die('分类参数错误');
			}

	    	if ($parent_id && !$seller_category->getObj("id=".$parent_id." and shop_id = ".$shop_id)) {
	    		die('上级分类不存在');
	    	}

	    	$arr = array(
	    			'parent_id' => $parent_id,
	    			'name' => $name,
	    			'sort' => $sort,
	    			'shop_id' => $shop_id
	    		);
	    	$seller_category->setData($arr);
	    	if($id)
	    	{
	    		$seller_category->update('id='.$id." and shop_id = ".$shop_id);
	    	}
	    	else
	    	{
	    		$seller_category->add();
	    	}
	    	if ($Nestable)
	    		return true;
	    	else
				$this->redirect('category_list');

		} else {
			$seller_category   = new IModel("seller_category");
			$this->data = $seller_category->getObj("id=".$id." and shop_id = ".$shop_id);
			if (!$this->data && $id) {
				$this->redirect('category_list');
			}
			$this->setRenderData($this->data);
			$this->redirect('category_edit');
		}
	}

/*	public function clerk_ajax_del()
	{
		$id = IFilter::act( IReq::get('id'),'int');
		$seller_clerk = new IModel('seller_clerk');
		if ($seller_clerk->del("id = $id"))
			die(JSON::encode(array('result' => 'succeed','data' => '')));
		else
			die(JSON::encode(array('result' => 'fail','data' => '')));
	}

	public function clerk_edit() {
	    $id = IFilter::act(IReq::get('id'),'int');
		if ($_POST) {
	    	$is_ok = IFilter::act(IReq::get('is_ok'),'int');
	    	$name = IFilter::act(IReq::get('name'));
	    	$position = IFilter::act(IReq::get('position'));
	    	$sort = IFilter::act(IReq::get('sort'));
	    	$seller_clerk = new IModel('seller_clerk');

	    	$arr = array(
	    			'is_ok' => $is_ok,
	    			'name' => $name,
	    			'position' => $position,
	    			'sort' => $sort,
	    			'seller_id' => $this->seller['seller_id']
	    		);
	    	$seller_clerk->setData($arr);
	    	if($id)
	    	{
	    		$seller_clerk->update('id='.$id." and seller_id = ".$this->seller['seller_id']);
	    	}
	    	else
	    	{
	    		$seller_clerk->add();
	    	}
			$this->redirect('clerk_list');

		} else {
			$seller_clerk   = new IModel("seller_clerk");
			$this->data = $seller_clerk->getObj("id=".$id." and seller_id = ".$this->seller['seller_id']);
			if (!$this->data && $id) {
				$this->redirect('clerk_list');
			}
			$this->setRenderData($this->data);
			$this->redirect('clerk_edit');
		}
	}

	public function clerk_ajax_sort() {
		$id = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');
		$seller_clerk = new IModel('seller_clerk');
		$seller_clerk->setData(array('sort' => $sort));
		if (false != $seller_clerk->update("id=$id"))
			die(JSON::encode(array('result' => 'succeed','data' => '')));
		else
			die(JSON::encode(array('result' => 'fail','data' => '')));
	}
*/

	/**
	 * @brief 商品添加中图片上传的方法
	 */
	public function goods_img_upload()
	{

	 	//调用文件上传类
		$photoObj = new PhotoUpload('', 'shop');
		$photo    = current($photoObj->run());

		//判断上传是否成功，如果float=1则成功
		if($photo['flag'] == 1)
		{
			$result = array(
				'flag'=> 1,
				'img' => $photo['img']
			);
		}
		else
		{
			$result = array('flag'=> $photo['flag']);
		}
		echo JSON::encode($result);
	}
	/**
	 * @brief 商品添加和修改视图
	 */
	public function goods_edit()
	{
		$goods_id = IFilter::act(IReq::get('id'),'int');
		//验证商品是不是属于该商家

		//初始化数据
		$goods_class = new goods_class();//$this->seller['seller_id']

		//获取所有商品扩展相关数据
		$data = $goods_class->edit($goods_id);

		if($goods_id && !$data)
		{
			die("没有找到相关商品！");
		}

		if ($data['form']['sort'] > 700)
		$data['form']['sort'] -= 700;
			
		$this->setRenderData($data);
		$this->redirect('goods_edit');
	}
	//商品更新动作
	public function goods_update()
	{
		$id       = IFilter::act(IReq::get('id'),'int');
		$callback = IFilter::act(IReq::get('callback'),'url');
		$callback = strpos($callback,'seller/goods_list') === false ? '' : $callback;
		//检查表单提交状态
		if(!$_POST)
		{
			die('请确认表单提交正确');
		}

		//初始化商品数据
		unset($_POST['id']);
		unset($_POST['callback']);

		if ($_POST['sort'] > 200)
			$_POST['sort'] = 200;

		$_POST['sort'] += 700;
		//$_POST['shop_id'] = IFilter::act(IReq::get('seller_id'),'int');
		$goodsObject = new goods_class(IFilter::act(IReq::get('seller_id'),'int'));
		$goodsObject->update($id,$_POST);
		$callback ? $this->redirect($callback) : $this->redirect("goods_list");
	}


	// 获取商户的全部店铺
	public function get_all_shop($seller_id = null) {
		$seller_id = $this->seller['seller_id'] ? : $seller_id;
		$seller = new IModel('seller');
		$tem = $seller->query('seller_user_id = ' . $seller_id);
		$shopList = array();

		foreach ($tem as $value) {
			$shopList[] = $value['id'];
		}
		return $shopList;
	}


	//商品列表
	public function goods_list()
	{
		//搜索条件
		$page   = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$this->search = $search = IFilter::act(IReq::get('search')); 
		$search = Util::search(IFilter::act($this->search,'strict'));

		$shopList = join(',', $this->get_all_shop());//所有店铺ID
		$where  = 'go.seller_id in ('.$shopList.')';
		$where .= $search ? " and ".$search : "";
		$join = " left join seller as shop on shop.id = go.seller_id ";


		//条件筛选处理
		//list($join,$where) = goods_class::getSearchCondition($search);
		//拼接sql
		$goodsHandle = new IQuery('goods as go');
		$goodsHandle->order    = "go.is_del desc,go.sort asc,go.id desc";
		$goodsHandle->distinct = "go.id";
		$goodsHandle->fields   = "go.*,shop.shopname,shop.logo,shop.is_vis,shop.id as shop_id";
		$goodsHandle->page     = $page;
		$goodsHandle->where    = $where;
		$goodsHandle->join     = $join;
		$this->goodsHandle = $goodsHandle;
		
		$this->redirect("goods_list");

	}

	//商品列表
	public function goods_report()
	{
		$seller_id = $this->seller['seller_id'];
		$condition = Util::search(IFilter::act(IReq::get('search'),'strict'));

		$where  = 'go.seller_id='.$seller_id;
		$where .= $condition ? " and ".$condition : "";

		$goodHandle = new IQuery('goods as go');
		$goodHandle->order  = "go.id desc";
		$goodHandle->fields = "go.*";
		$goodHandle->where  = $where;
		$goodList = $goodHandle->find();

		//构建 Excel table;
		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;">商品名称</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="160">分类</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="60">售价</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="60">库存</td>';
		$strTable .= '</tr>';

		foreach($goodList as $k=>$val){
			$strTable .= '<tr>';
			$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['name'].'</td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.goods_class::getGoodsCategory($val['id']).' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['sell_price'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['store_nums'].' </td>';
			$strTable .= '</tr>';
		}
		$strTable .='</table>';
		unset($goodList);
		$reportObj = new report();
		$reportObj->setFileName('goods');
		$reportObj->toDownload($strTable);
		exit();
	}

	//商品删除
	public function goods_del()
	{
		//post数据
	    $id = IFilter::act(IReq::get('id'),'int');

	    //生成goods对象
	    $goods = new goods_class();
	    //$goods->seller_id = $this->seller['seller_id'];

	    if($id)
		{
			if(is_array($id))
			{
				foreach($id as $key => $val)
				{
					$goods->del($val);
				}
			}
			else
			{
				$goods->del($id);
			}
		}
		$this->redirect("goods_list");
	}
	//商品分类更新
	public function goods_cat_update(){
	    $catid        = IFilter::act(IReq::get('catid'),'int');
	    $shop_id        = IFilter::act(IReq::get('shop_id'),'int');
		$goods        = IFilter::act(IReq::get('goods'),'int');

		$seller_categoryDB = new IModel('seller_category_extend');
		$seller_category = new IModel('seller_category');
		if(isset($catid))
		{	
			if ($catid && !$seller_category->getObj("id=".$catid)) {
	    		die('分类不存在');
			}

			$seller_categoryDB->del('goods_id in ('.join(',', $goods).')');

			if ($catid) {
				foreach ($goods as $id) {
					$seller_categoryDB->setData(array('goods_id' => $id,'category_id' => $catid));
					$seller_categoryDB->add();
				}
			}
		}

		echo 'ok';
	}

	//商品状态修改
	public function goods_status()
	{
	    $id        = IFilter::act(IReq::get('id'),'int');
		$is_del    = IFilter::act(IReq::get('is_del'),'int');
		$is_del    = $is_del === 0 ? 3 : $is_del; //不能等于0直接上架

		$goodsDB = new IModel('goods');
		$goodsDB->setData(array('is_del' => $is_del));

	    if($id)
		{
			if(is_array($id))
			{
				foreach($id as $key => $val)
				{
					$goodsDB->update("id = ".$val);
				}
			}
			else
			{
				$goodsDB->update("id = ".$val);
			}
		}
		$this->redirect("goods_list");
	}

	//规格删除
	public function spec_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			$idString = is_array($id) ? join(',',$id) : $id;
			$specObj  = new IModel('spec');
			$specObj->del("id in ( {$idString} ) and seller_id = ".$this->seller['seller_id']);
			$this->redirect('spec_list');
		}
		else
		{
			$this->redirect('spec_list',false);
			Util::showMessage('请选择要删除的规格');
		}
	}
	//修改商品排序
	public function seller_modiy_sort_ajax()
	{
		$id   = IFilter::act(IReq::get('id'),'int');
		$sort = IFilter::act(IReq::get('sort'),'int');

		$goodsDB = new IModel('goods');
		$goodsDB->setData(array('sort' => $sort));
		$goodsDB->update("id = {$id}");
	}

	function spec_list() {
		$this->shop_list = '('.join(',', ($this->get_all_shop())).')';
		$this->redirect('spec_list');
	}

	function refundment_list() {
		$this->shop_list = '('.join(',', ($this->get_all_shop())).')';
		$this->redirect('refundment_list');
	}

	function comment_list() {
		$this->shop_list = '('.join(',', ($this->get_all_shop())).')';
		$this->redirect('comment_list');
	}

	public function refer_list() {
				//搜索条件
		$search = IFilter::act(IReq::get('search'));
		$page   = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;

		$seller_id = $this->seller['seller_id'];
		$condition = Util::search(IFilter::act(IReq::get('search'),'strict'));
		//$searchArray = Util::getUrlParam('search');
		//$searchParam = http_build_query($searchArray);

		$shopList = join(',', $this->get_all_shop());//所有店铺ID
		
		$condition = Util::search(IReq::get('search'));
		$where  = 'go.seller_id in ('.$shopList.')';
		$where .= $condition ? " and ".$condition : "";
		$join = " left join goods as go on go.id = re.goods_id left join user as u on u.id = re.user_id left join admin as a on a.id = re.admin_id left join seller as se on se.id = re.seller_id ";

		//条件筛选处理
		//list($join,$where) = goods_class::getSearchCondition($search);
		//拼接sql
		$goodsHandle = new IQuery('refer as re');
		$goodsHandle->order    = "go.sort asc,go.id desc";
		$goodsHandle->distinct = "go.id";
		$goodsHandle->fields   = "se.shopname,a.admin_name,u.username,re.*,go.name";
		$goodsHandle->page     = $page;
		$goodsHandle->where    = $where;
		$goodsHandle->join     = $join;

		//$this->search      = $search;
		$this->referObj = $goodsHandle;



		$this->redirect('refer_list');
	}

	//咨询回复
	public function refer_reply()
	{
		$rid     = IFilter::act(IReq::get('refer_id'),'int');
		$content = IFilter::act(IReq::get('content'),'text');

		if($rid && $content)
		{
			$tb_refer = new IModel('refer');
			$seller_id = $this->seller['seller_id'];//商户id
			$data = array(
				'answer' => $content,
				'reply_time' => date('Y-m-d H:i:s'),
				'seller_id' => $seller_id,
				'status' => 1
			);
			$tb_refer->setData($data);
			$tb_refer->update("id=".$rid);
		}
		$this->redirect('refer_list');
	}
	/**
	 * @brief查看订单
	 */
	public function order_show()
	{
		//获得post传来的值
		$order_id = IFilter::act(IReq::get('id'),'int');
		$data = array();
		if($order_id)
		{
			$order_show = new Order_Class();
			$data = $order_show->getOrderShow($order_id,0,$this->seller['seller_id']);
			if($data)
			{
		 		//获取地区
		 		$data['area_addr'] = $data["addregion"];//join('&nbsp;',area::name($data['province'],$data['city'],$data['area']));

			 	$this->setRenderData($data);
				$this->redirect('order_show',false);
			}
		}
		if(!$data)
		{
			$this->redirect('order_list');
		}
	}
	/**
	 * @brief 发货订单页面
	 */
	public function order_deliver()
	{
		$order_id = IFilter::act(IReq::get('id'),'int');
		$data     = array();

		if($order_id)
		{
			$order_show = new Order_Class();
			$data = $order_show->getOrderShow($order_id);
			if($data)
			{
				$this->setRenderData($data);
				$this->redirect('order_deliver');
			}
		}
		if(!$data)
		{
			IError::show("订单信息不存在",403);
		}
	}
	/**
	 * @brief 发货操作
	 */
	public function order_delivery_doc($back = false)
	{
	 	//获得post变量参数
	 	$order_id = IFilter::act(IReq::get('id'),'int');

	 	//发送的商品关联
	 	// $sendgoods = IFilter::act(IReq::get('sendgoods'),'int');

	 	$order_goods = new IModel('order_goods');
	 	$goods = $order_goods->query('order_id = ' . $order_id);
	 	$sendgoods = array();
	 	foreach ($goods as $value) {
	 		$sendgoods[] = $value['id'];
	 	}

	 	if(!$sendgoods)
	 	{
	 		die('请选择要发货的商品');
	 	}

	 	Order_Class::sendDeliveryGoods($order_id,$sendgoods,$this->seller['seller_id'],'seller');

	 	if (!$back)
		 	$this->redirect('order_list');
		 else
		 	return true;
	}

	//订单导出 Excel
	public function order_report()
	{
		$seller_id = $this->seller['seller_id'];
		$condition = Util::search(IFilter::act(IReq::get('search'),'strict'));

		$where  = "go.seller_id = ".$seller_id;
		$where .= $condition ? " and ".$condition : "";

		//拼接sql
		$orderHandle = new IQuery('order_goods as og');
		$orderHandle->order  = "o.id desc";
		$orderHandle->fields = "o.*,p.name as payment_name";
		$orderHandle->join   = "left join goods as go on go.id=og.goods_id left join order as o on o.id=og.order_id left join payment as p on p.id = o.pay_type";
		$orderHandle->where  = $where;
		$orderList = $orderHandle->find();

		//构建 Excel table
		$strTable ='<table width="500" border="1">';
		$strTable .= '<tr>';
		$strTable .= '<td style="text-align:center;font-size:12px;width:120px;">订单编号</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="100">日期</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">收货人</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">电话</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">订单金额</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">实际支付</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付方式</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">支付状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">发货状态</td>';
		$strTable .= '<td style="text-align:center;font-size:12px;" width="*">商品信息</td>';
		$strTable .= '</tr>';

		foreach($orderList as $k=>$val){
			$strTable .= '<tr>';
			$strTable .= '<td style="text-align:center;font-size:12px;">&nbsp;'.$val['order_no'].'</td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['create_time'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['accept_name'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">&nbsp;'.$val['telphone'].'&nbsp;'.$val['mobile'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['payable_amount'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['real_amount'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.$val['payment_name'].' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.Order_Class::getOrderPayStatusText($val).' </td>';
			$strTable .= '<td style="text-align:left;font-size:12px;">'.Order_Class::getOrderDistributionStatusText($val).' </td>';

			$orderGoods = Order_class::getOrderGoods($val['id']);

			$strGoods="";
			foreach($orderGoods as $good){
				$strGoods .= "商品编号：".$good->goodsno." 商品名称：".$good->name;
				if ($good->value!='') $strGoods .= " 规格：".$good->value;
				$strGoods .= "<br />";
			}
			unset($orderGoods);

			$strTable .= '<td style="text-align:left;font-size:12px;">'.$strGoods.' </td>';
			$strTable .= '</tr>';
		}
		$strTable .='</table>';
		//输出成EXcel格式文件并下载
		$reportObj = new report();
		$reportObj->setFileName('order');
		$reportObj->toDownload($strTable);
		exit();
	}

	//修改商户信息
	public function seller_edit()
	{
		$seller_id = $this->seller['seller_id'];
		$sellerDB        = new IModel('seller_user');
		$this->sellerRow = $sellerDB->getObj('id = '.$seller_id);
		$this->redirect('seller_edit');
	}

	/**
	 * @brief 商户的增加动作
	 */
	public function seller_add()
	{
		
		$seller_id   = $this->seller['seller_id'];
		$password    = IFilter::act(IReq::get('password'));
		$repassword  = IFilter::act(IReq::get('repassword'));
		//$mobile      = IFilter::act(IReq::get('mobile'));

		if(!$seller_id && $password == '')
		{
			$errorMsg = '请输入密码！';
		}

		if($password != $repassword)
		{
			$errorMsg = '两次输入的密码不一致！';
		}

		//操作失败表单回填
		if(isset($errorMsg))
		{
			$this->sellerRow = $_POST;
			$this->redirect('seller_edit',false);
			Util::showMessage($errorMsg);
		}

		//待更新的数据
		$sellerRow = array(
			//'mobile'    => $mobile,
		);

		//创建商家操作类
		$sellerDB   = new IModel("seller_user");

		//修改密码
		if($password)
		{
			$sellerRow['password'] = md5($password);
		}

		$sellerDB->setData($sellerRow);
		$sellerDB->update("id = ".$seller_id);

		$this->redirect('seller_edit');
	}


	//绑定商户银行卡
	public function seller_bank()
	{
		$seller_id = $this->seller['seller_id'];
		$sellerDB        = new IModel('seller_user');
		$this->sellerRow = $sellerDB->getObj('id = '.$seller_id);
		$this->redirect('seller_bank');
	}

		/**
	 * @brief 绑定商户银行卡
	 */
	public function seller_bank_bind()
	{
		$seller_id   = $this->seller['seller_id'];
		//待更新的数据
		$sellerRow = array(
			'bank_name'    => IFilter::act(IReq::get('bank_name')),
			'bank_user'  => IFilter::act(IReq::get('bank_user')),
			'bank_card'  => IFilter::act(IReq::get('bank_card')),
		);
		//创建商家操作类
		$sellerDB   = new IModel("seller_user");
		$sellerDB->setData($sellerRow);
		$sellerDB->update("id = ".$seller_id);
		$this->redirect('seller_bank');
	}


	//[团购]添加修改[单页]
	function regiment_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			$regimentObj = new IModel('regiment');
			$where       = 'id = '.$id.' and seller_id = '.$this->seller['seller_id'];
			$regimentRow = $regimentObj->getObj($where);
			if(!$regimentRow)
			{
				$this->redirect('regiment_list');
			}

			//促销商品
			$goodsObj = new IModel('goods');
			$goodsRow = $goodsObj->getObj('id = '.$regimentRow['goods_id']);

			$result = array(
				'isError' => false,
				'data'    => $goodsRow,
			);
			$regimentRow['goodsRow'] = JSON::encode($result);
			$this->regimentRow = $regimentRow;
		}
		$this->redirect('regiment_edit');
	}

	//[团购]删除
	function regiment_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$regObj = new IModel('regiment');
			if(is_array($id))
			{
				$id    = join(',',$id);
			}
			$where = ' id in ('.$id.') and seller_id = '.$this->seller['seller_id'];
			$regObj->del($where);
			$this->redirect('regiment_list');
		}
		else
		{
			$this->redirect('regiment_list',false);
			Util::showMessage('请选择要删除的id值');
		}
	}

	//[团购]添加修改[动作]
	function regiment_edit_act()
	{
		$id      = IFilter::act(IReq::get('id'),'int');
		$goodsId = IFilter::act(IReq::get('goods_id'),'int');

		$dataArray = array(
			'id'        	=> $id,
			'title'     	=> IFilter::act(IReq::get('title','post')),
			'start_time'	=> IFilter::act(IReq::get('start_time','post')),
			'end_time'  	=> IFilter::act(IReq::get('end_time','post')),
			'is_close'      => 1,
			'intro'     	=> IFilter::act(IReq::get('intro','post')),
			'goods_id'      => $goodsId,
			'store_nums'    => IFilter::act(IReq::get('store_nums','post')),
			'limit_min_count' => IFilter::act(IReq::get('limit_min_count','post'),'int'),
			'limit_max_count' => IFilter::act(IReq::get('limit_max_count','post'),'int'),
			'regiment_price'=> IFilter::act(IReq::get('regiment_price','post')),
			'seller_id'     => $this->seller['seller_id'],
		);

		if($goodsId)
		{
			$goodsObj = new IModel('goods');
			$where    = 'id = '.$goodsId.' and seller_id = '.$this->seller['seller_id'];
			$goodsRow = $goodsObj->getObj($where);

			//商品信息不存在
			if(!$goodsRow)
			{
				$this->regimentRow = $dataArray;
				$this->redirect('regiment_edit',false);
				Util::showMessage('请选择商户自己的商品');
			}

			//处理上传图片
			if(isset($_FILES['img']['name']) && $_FILES['img']['name'] != '')
			{
				$uploadObj = new PhotoUpload();
				$photoInfo = $uploadObj->run();
				$dataArray['img'] = $photoInfo['img']['img'];
			}
			else
			{
				$dataArray['img'] = $goodsRow['img'];
			}

			$dataArray['sell_price'] = $goodsRow['sell_price'];
		}
		else
		{
			$this->regimentRow = $dataArray;
			$this->redirect('regiment_edit',false);
			Util::showMessage('请选择要关联的商品');
		}

		$regimentObj = new IModel('regiment');
		$regimentObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id.' and seller_id = '.$this->seller['seller_id'];
			$regimentObj->update($where);
		}
		else
		{
			$regimentObj->add();
		}
		$this->redirect('regiment_list');
	}

	//结算单修改
	public function bill_edit()
	{


		$seller_user  = new IModel('seller_user');
		$sellerRow = $seller_user->getObj('id = '.$this->seller['seller_id']);
		if (!$sellerRow['bank_card']) {
			$this->redirect('seller_bank');
		}
		$this->sellerRow = $sellerRow;

		$id = IFilter::act(IReq::get('id'),'int');
		$billRow = array();

		if($id)
		{
			$billDB  = new IModel('bill');
			$billRow = $billDB->getObj('id = '.$id.' and seller_id = '.$this->seller['seller_id']);
		}

		$this->billRow = $billRow;
		$this->redirect('bill_edit');
	}

	//结算单删除
	public function bill_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');

		if($id)
		{
			$billDB = new IModel('bill');
			$billDB->del('id = '.$id.' and seller_id = '.$this->seller['seller_id'].' and is_pay = 0');
		}

		$this->redirect('bill_list');
	}

	//结算单更新
	public function bill_update()
	{
		$id            = IFilter::act(IReq::get('id'),'int');
		$start_time    = IFilter::act(IReq::get('start_time'));
		$end_time      = IFilter::act(IReq::get('end_time'));
		$apply_content = IFilter::act(IReq::get('apply_content'));

		$bank_user = IFilter::act(IReq::get('bank_user'));
		$bank_name = IFilter::act(IReq::get('bank_name'));
		$bank_card = IFilter::act(IReq::get('bank_card'));

		$mobile = IFilter::act(IReq::get('mobile'));

		$billDB = new IModel('bill');
		if($id)
		{exit;
			$billRow = $billDB->getObj('id = '.$id);
			if($billRow['is_pay'] == 0)
			{
				$billDB->setData(array('apply_content' => $apply_content));
				$billDB->update('id = '.$id.' and seller_id = '.$this->seller['seller_id']);
			}
		}
		else
		{
			//判断是否存在未处理的申请
			$isSubmitBill = $billDB->getObj(" seller_id = ".$this->seller['seller_id']." and is_pay = 0");
			if($isSubmitBill)
			{
				$this->redirect('bill_list',false);
				Util::showMessage('请耐心等待管理员结算后才能再次提交申请');
			}

			//获取未结算的订单
			$queryObject = CountSum::getSellerGoodsFeeQuery($this->seller['seller_id'],$start_time,$end_time,0);
			$countData   = CountSum::countSellerOrderFee($queryObject->find());

			if($countData['countFee'] > 0)
			{

				if ($countData['countFee'] < 20) {
					$this->redirect('bill_list',false);
					Util::showMessage('总金额满20元才能申请结算哦');
				}

				$countData['start_time'] = $start_time;
				$countData['end_time']   = $end_time;

				$billString = AccountLog::sellerBillTemplate($countData);
				$data = array(
					'seller_id'          => $this->seller['seller_id'],
					'apply_time'         => date('Y-m-d H:i:s'),
					'apply_content'      => IFilter::act(IReq::get('apply_content')),
					'start_time'         => $start_time,
					'end_time'           => $end_time,
					'log'                => $billString,
					'order_ids'          => join(",",$countData['order_ids']),
					'order_no_ids'       => join(",",$countData['orderNoList']),
					'bank_user'          => $bank_user,
					'bank_name'          => $bank_name,
					'bank_card'          => $bank_card,
					'mobile'             => $mobile,
					'order_num'          => $countData['orderNum'],
					'count_fee'          => $countData['countFee'],
					'order_amount_price' => $countData['orderAmountPrice'],
					'refund_fee'         => $countData['refundFee'],
					'platform_fee'       => $countData['platformFee'],
					'commission'         => $countData['commission'],
				);
				$billDB->setData($data);
				$billDB->add();


				$seller_user  = new IModel('seller_user');
				$sellerRow = $seller_user->getObj('id = '.$this->seller['seller_id']);
				// 商户申请结算通知给夏姐
				$siteConfigObj = new Config("site_config");
				$site_config   = $siteConfigObj->getInfo();
				$content = smsTemplate::bill_notice(array('{user}' => $sellerRow['seller_name'], '{tel}' => $mobile, '{money}' => $countData['countFee']));
				$result = Hsms::send($site_config['bill_mobile'], $content, 0);
			}
			else
			{
				$this->redirect('bill_list',false);
				Util::showMessage('当前时间段内没有任何结算货款');
			}
		}
		$this->redirect('bill_list');
	}

	//计算应该结算的货款明细
	public function countGoodsFee()
	{
		$seller_id   = $this->seller['seller_id'];
		$start_time  = IFilter::act(IReq::get('start_time'));
		$end_time    = IFilter::act(IReq::get('end_time'));

		$queryObject = CountSum::getSellerGoodsFeeQuery($seller_id,$start_time,$end_time,0);
		$countData   = CountSum::countSellerOrderFee($queryObject->find());
		if($countData['countFee'] > 0)
		{
			$countData['start_time'] = $start_time;
			$countData['end_time']   = $end_time;

			$billString = AccountLog::sellerBillTemplate($countData);
			$result     = array('result' => 'success','data' => $billString);
		}
		else
		{
			$result = array('result' => 'fail','data' => '当前没有任何款项可以结算');
		}

		die(JSON::encode($result));
	}

	/**
	 * @brief 显示评论信息
	 */
	function comment_edit()
	{
		$cid = IFilter::act(IReq::get('cid'),'int');

		if(!$cid)
		{
			$this->comment_list();
			return false;
		}
		$query = new IQuery("comment as c");
		$query->join = "left join goods as goods on c.goods_id = goods.id left join user as u on c.user_id = u.id";
		$query->fields = "c.*,u.username,goods.name,goods.seller_id";
		$query->where = "c.id=".$cid." and goods.seller_id = ".$this->seller['seller_id'];
		$items = $query->find();

		if($items)
		{
			$this->comment = current($items);
			$this->redirect('comment_edit');
		}
		else
		{
			$this->comment_list();
			$msg = '没有找到相关记录！';
			Util::showMessage($msg);
		}
	}

	/**
	 * @brief 回复评论
	 */
	function comment_update()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$recontent = IFilter::act(IReq::get('recontents'));
		if($id)
		{
			$commentDB = new IQuery('comment as c');
			$commentDB->join = 'left join goods as go on go.id = c.goods_id';
			$commentDB->where= 'c.id = '.$id.' and go.seller_id = '.$this->seller['seller_id'];
			$checkList = $commentDB->find();
			if(!$checkList)
			{
				IError::show(403,'该商品不属于您，无法对其评论进行回复');
			}

			$updateData = array(
				'recontents' => $recontent,
				'recomment_time' => ITime::getDateTime(),
			);
			$commentDB = new IModel('comment');
			$commentDB->setData($updateData);
			$commentDB->update('id = '.$id);
		}
		$this->redirect('comment_list');
	}

	//商品退款详情
	function refundment_show()
	{
	 	//获得post传来的退款单id值
	 	$refundment_id = IFilter::act(IReq::get('id'),'int');
	 	$data = array();
	 	if($refundment_id)
	 	{
	 		$tb_refundment = new IQuery('refundment_doc as c');
	 		$tb_refundment->join=' left join order as o on c.order_id=o.id left join user as u on u.id = c.user_id';
	 		$tb_refundment->fields = 'u.username,c.*,o.*,c.id as id,c.pay_status as pay_status';
	 		$tb_refundment->where = 'c.id='.$refundment_id.' and c.seller_id = '.$this->seller['seller_id'];
	 		$refundment_info = $tb_refundment->find();
	 		if($refundment_info)
	 		{
	 			$data = current($refundment_info);
	 			$this->data = $data;
	 			$this->setRenderData($data);
	 			$this->redirect('refundment_show',false);
	 		}
	 	}

	 	if(!$data)
		{
			$this->redirect('refundment_list');
		}
	}

	//商品退款操作
	function refundment_update()
	{
		$id           = IFilter::act(IReq::get('id'),'int');
		$pay_status   = IFilter::act(IReq::get('pay_status'),'int');
		$dispose_idea = IFilter::act(IReq::get('dispose_idea'));
		$amount       = IFilter::act(IReq::get('amount'),'float');

		//检查退款金额是否超出
		$tb_refundment_doc = new IModel('refundment_doc');
		$orderGoodsDB      = new IModel('order_goods');
		$refundsRow         = $tb_refundment_doc->getObj('id = '.$id.' and seller_id = '.$this->seller['seller_id']);
		if(!$refundsRow)
		{
			die('退款申请不存在');
		}

		$orderGoodsRow = $orderGoodsDB->getObj('seller_id = '.$this->seller['seller_id'].' and order_id = '.$refundsRow['order_id'].' and goods_id = '.$refundsRow['goods_id'].' and product_id = '.$refundsRow['product_id'].' and is_send != 2');
		if(!$orderGoodsRow)
		{
			die('退款商品信息不存在');
		}

		$actAmount = $orderGoodsRow['real_price'] * $orderGoodsRow['goods_nums'];
		if($amount > $actAmount)
		{
			die("填写的退款金额不能大于实际用户支付的金额");
		}

		//商户处理退款
		if($id && Order_Class::isSellerRefund($id,$this->seller['seller_id']) == 2)
		{
			$tb_refundment_doc = new IModel('refundment_doc');
			$updateData = array(
				'dispose_time' => ITime::getDateTime(),
				'dispose_idea' => $dispose_idea,
				'pay_status'   => $pay_status,
				'amount'       => $amount,
			);
			$tb_refundment_doc->setData($updateData);
			$tb_refundment_doc->update('id = '.$id);

			if($pay_status == 2)
			{
				$result = Order_Class::refund($id,$this->seller['seller_id'],'seller');
				if(!$result)
				{
					$tb_refundment_doc->rollback();
					die('退款失败');
				}
			}
		}
		$this->redirect('refundment_list');
	}

	//商品复制
	function goods_copy()
	{
		$idArray = explode(',',IReq::get('id'));
		$idArray = IFilter::act($idArray,'int');

		$goodsDB     = new IModel('goods');
		$goodsAttrDB = new IModel('goods_attribute');
		$goodsPhotoRelationDB = new IModel('goods_photo_relation');
		$productsDB = new IModel('products');

		$goodsData = $goodsDB->query('id in ('.join(',',$idArray).') and is_share = 1 and is_del = 0 and seller_id = 0','*');
		if($goodsData)
		{
			foreach($goodsData as $key => $val)
			{
				//判断是否重复
				if( $goodsDB->getObj('seller_id = '.$this->seller['seller_id'].' and name = "'.$val['name'].'"') )
				{
					die('商品不能重复复制');
				}

				$oldId = $val['id'];

				//商品数据
				unset($val['id'],$val['visit'],$val['favorite'],$val['sort'],$val['comments'],$val['sale'],$val['grade'],$val['is_share']);
				$val['seller_id'] = $this->seller['seller_id'];
				$val['goods_no'] .= '-'.$this->seller['seller_id'];

				$goodsDB->setData($val);
				$goods_id = $goodsDB->add();

				//商品属性
				$attrData = $goodsAttrDB->query('goods_id = '.$oldId);
				if($attrData)
				{
					foreach($attrData as $k => $v)
					{
						unset($v['id']);
						$v['goods_id'] = $goods_id;
						$goodsAttrDB->setData($v);
						$goodsAttrDB->add();
					}
				}

				//商品图片
				$photoData = $goodsPhotoRelationDB->query('goods_id = '.$oldId);
				if($photoData)
				{
					foreach($photoData as $k => $v)
					{
						unset($v['id']);
						$v['goods_id'] = $goods_id;
						$goodsPhotoRelationDB->setData($v);
						$goodsPhotoRelationDB->add();
					}
				}

				//货品
				$productsData = $productsDB->query('goods_id = '.$oldId);
				if($productsData)
				{
					foreach($productsData as $k => $v)
					{
						unset($v['id']);
						$v['products_no'].= '-'.$this->seller['seller_id'];
						$v['goods_id']    = $goods_id;
						$productsDB->setData($v);
						$productsDB->add();
					}
				}
			}
			die('success');
		}
		else
		{
			die('复制的商品不存在');
		}
	}

	/**
	 * @brief 添加/修改发货信息
	 */
	public function ship_info_edit()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get("sid"),'int');
    	if($id)
    	{
    		$tb_ship   = new IModel("merch_ship_info");
    		$ship_info = $tb_ship->getObj("id=".$id);
    		if($ship_info)
    		{
    			$this->data = $ship_info;
    		}
    		else
    		{
    			die('数据不存在');
    		}
    	}
    	$this->setRenderData($this->data);
		$this->redirect('ship_info_edit');
	}
	/**
	 * @brief 设置发货信息的默认值
	 */
	public function ship_info_default()
	{
		$id = IFilter::act( IReq::get('id'),'int' );
		$shop_id = IFilter::act(IReq::get('shop_id'),'int');
        $default = IFilter::string(IReq::get('default'));
        $tb_merch_ship_info = new IModel('merch_ship_info');
        if($default == 1)
        {
            $tb_merch_ship_info->setData(array('is_default'=>0));
            $tb_merch_ship_info->update("seller_id = ".$shop_id);
        }
        $tb_merch_ship_info->setData(array('is_default' => $default));
        $tb_merch_ship_info->update("id = ".$id." and seller_id = ".$shop_id);
        $this->redirect('ship_info_list');
	}
	/**
	 * @brief 保存添加/修改发货信息
	 */
	public function ship_info_update()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get('sid'),'int');
    	$ship_name = IFilter::act(IReq::get('ship_name'));
    	$ship_user_name = IFilter::act(IReq::get('ship_user_name'));
    	$sex = IFilter::act(IReq::get('sex'),'int');
    	// $province =IFilter::act(IReq::get('province'),'int');
    	// $city = IFilter::act(IReq::get('city'),'int');
    	// $area = IFilter::act(IReq::get('area'),'int');
    	$address = IFilter::act(IReq::get('address'));
    	$addressreference = IFilter::act(IReq::get('addressreference'));
    	// $postcode = IFilter::act(IReq::get('postcode'),'int');
    	$mobile = IFilter::act(IReq::get('mobile'));
    	// $telphone = IFilter::act(IReq::get('telphone'));
    	$is_default = IFilter::act(IReq::get('is_default'),'int');
    	$shop_id = IFilter::act(IReq::get('shop_id'),'int');

    	$tb_merch_ship_info = new IModel('merch_ship_info');

    	//判断是否已经有了一个默认地址
    	if(isset($is_default) && $is_default==1)
    	{
    		$tb_merch_ship_info->setData(array('is_default' => 0));
    		$tb_merch_ship_info->update('seller_id = ' . $shop_id);
    	}
    	//设置存储数据
    	$arr['ship_name'] = $ship_name;
	    $arr['ship_user_name'] = $ship_user_name;
	    $arr['sex'] = $sex;
    	// $arr['province'] = $province;
    	// $arr['city'] =$city;
    	// $arr['area'] =$area;
    	$arr['address'] = $address;
    	$arr['addressreference'] = $addressreference;
    	// $arr['postcode'] = $postcode;
    	$arr['mobile'] = $mobile;
    	// $arr['telphone'] =$telphone;
    	$arr['is_default'] = $is_default;
    	$arr['is_del'] = 1;
    	$arr['seller_id'] = $shop_id;

    	$tb_merch_ship_info->setData($arr);
    	//判断是添加还是修改
    	if($id)
    	{
    		$tb_merch_ship_info->update('id='.$id." and seller_id = ".$shop_id);
    	}
    	else
    	{
    		$tb_merch_ship_info->add();
    	}
		$this->redirect('ship_info_list');
	}
	/**
	 * @brief 删除发货信息到回收站中
	 */
	public function ship_info_del()
	{
		// 获取POST数据
    	$id = IFilter::act(IReq::get('id'),'int');
		//加载 商家发货点信息
    	$tb_merch_ship_info = new IModel('merch_ship_info');
		if($id)
		{
			$tb_merch_ship_info->del(Util::joinStr($id)." and seller_id = ".$this->seller['seller_id']);
			$this->redirect('ship_info_list');
		}
		else
		{
			$this->redirect('ship_info_list',false);
			Util::showMessage('请选择要删除的数据');
		}
	}

	/**
	 * @brief 配送方式修改
	 */
    public function delivery_edit()
	{
		$data = array();
        $delivery_id = IFilter::act(IReq::get('id'),'int');

        $this->shop_id = $shop_id = IFilter::act(IReq::get('shop'),'int');

        $seller = new IModel('seller');

        if (!$seller->getObj('id = '. $shop_id . ' and seller_user_id = ' . $this->seller['seller_id']))
        	die('no shop ^_^');

        if($delivery_id)
        {
            $delivery = new IModel('delivery_extend');
            $data = $delivery->getObj('delivery_id = '.$delivery_id.' and seller_id = '.$shop_id);
		}
		else
		{
			die('配送方式');
		}

		//获取省份
		$areaData = array();
		$areaDB = new IModel('areas');
		$areaList = $areaDB->query('parent_id = 0');
		foreach($areaList as $val)
		{
			$areaData[$val['area_id']] = $val['area_name'];
		}
		$this->areaList  = $areaList;
		$this->data_info = $data;
		$this->area      = $areaData;
        $this->redirect('delivery_edit');
	}

	/**
	 * 配送方式修改
	 */
    public function delivery_update()
    {
        //首重重量
        $first_weight = IFilter::act(IReq::get('first_weight'),'float');
        //续重重量
        $second_weight = IFilter::act(IReq::get('second_weight'),'float');
        //首重价格
        $first_price = IFilter::act(IReq::get('first_price'),'float');
        //续重价格
        $second_price = IFilter::act(IReq::get('second_price'),'float');
        //是否支持物流保价
        $is_save_price = IFilter::act(IReq::get('is_save_price'),'int');
        //地区费用类型
        $price_type = IFilter::act(IReq::get('price_type'),'int');
        //启用默认费用
        $open_default = IFilter::act(IReq::get('open_default'),'int');
        //支持的配送地区ID
        $area_groupid = serialize(IReq::get('area_groupid'));
        //配送地址对应的首重价格
        $firstprice = serialize(IReq::get('firstprice'));
        //配送地区对应的续重价格
        $secondprice = serialize(IReq::get('secondprice'));
        //保价费率
        $save_rate = IFilter::act(IReq::get('save_rate'),'float');
        //最低保价
        $low_price = IFilter::act(IReq::get('low_price'),'float');
		//配送ID
        $delivery_id = IFilter::act(IReq::get('deliveryId'),'int');

        $shop_id = IFilter::act(IReq::get('seller_id'),'int');;

        $deliveryDB  = new IModel('delivery');
        $deliveryRow = $deliveryDB->getObj('id = '.$delivery_id);
        if(!$deliveryRow)
        {
        	die('配送方式不存在');
        }

        if(!$shop_id)
        {
        	die('you no select shop ^_^');
        }

        $data = array(
        	'first_weight' => $first_weight,
        	'second_weight'=> $second_weight,
        	'first_price'  => $first_price,
        	'second_price' => $second_price,
        	'is_save_price'=> $is_save_price,
        	'price_type'   => $price_type,
        	'open_default' => $open_default,
        	'area_groupid' => $area_groupid,
        	'firstprice'   => $firstprice,
        	'secondprice'  => $secondprice,
        	'save_rate'    => $save_rate,
        	'low_price'    => $low_price,
        	'seller_id'    => $shop_id,
        	'delivery_id'  => $delivery_id,
        );
        $deliveryExtendDB = new IModel('delivery_extend');
        $deliveryExtendDB->setData($data);
        $deliveryObj = $deliveryExtendDB->getObj("delivery_id = ".$delivery_id." and seller_id = ".$shop_id);
        //已经存在了
        if($deliveryObj)
        {
        	$deliveryExtendDB->update('delivery_id = '.$delivery_id.' and seller_id = '.$shop_id);
        }
        else
        {
        	$deliveryExtendDB->add();
        }
		$this->redirect('delivery');
    }

	//[促销活动] 添加修改 [单页]
	function pro_rule_edit()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if($id)
		{
			$promotionObj = new IModel('promotion');
			$where = 'id = '.$id.' and seller_id in ('.join(',', $this->get_all_shop()).')';
			$this->promotionRow = $promotionObj->getObj($where);
		}
		$this->redirect('pro_rule_edit');
	}

	//[促销活动] 添加修改 [动作]
	function pro_rule_edit_act()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		$promotionObj = new IModel('promotion');

		$group_all    = IReq::get('group_all','post');
		if($group_all == 'all')
		{
			$user_group_str = 'all';
		}
		else
		{
			$user_group = IFilter::act(IReq::get('user_group','post'),'int');
			$user_group_str = '';
			if($user_group)
			{
				$user_group_str = join(',',$user_group);
				$user_group_str = ','.$user_group_str.',';
			}
		}

		$dataArray = array(
			'name'       => IFilter::act(IReq::get('name','post')),
			'condition'  => IFilter::act(IReq::get('condition','post')),
			'is_close'   => IFilter::act(IReq::get('is_close','post')),
			'start_time' => IFilter::act(IReq::get('start_time','post')),
			'end_time'   => IFilter::act(IReq::get('end_time','post')),
			'intro'      => IFilter::act(IReq::get('intro','post')),
			'award_type' => IFilter::act(IReq::get('award_type','post')),
			'type'       => 0,
			'user_group' => $user_group_str,
			'award_value'=> IFilter::act(IReq::get('award_value','post')),
			'seller_id'  => IFilter::act(IReq::get('shop_id','post'), 'int'),	// 实际是店铺ID
		);

		if(!in_array($dataArray['award_type'],array(1,2,6)))
		{
			IError::show('促销类型不符合规范',403);
		}

		$promotionObj->setData($dataArray);

		if($id)
		{
			$where = 'id = '.$id;
			$promotionObj->update($where);
		}
		else
		{
			$promotionObj->add();
		}
		$this->redirect('pro_rule_list');
	}

	//[促销活动] 删除
	function pro_rule_del()
	{
		$id = IFilter::act(IReq::get('id'),'int');
		if(!empty($id))
		{
			$promotionObj = new IModel('promotion');
			if(is_array($id))
			{
				$idStr = join(',',$id);
				$where = ' id in ('.$idStr.')';
			}
			else
			{
				$where = 'id = '.$id;
			}
			$promotionObj->del($where.' and seller_id = '.$this->seller['seller_id']);
			$this->redirect('pro_rule_list');
		}
		else
		{
			$this->redirect('pro_rule_list',false);
			Util::showMessage('请选择要删除的促销活动');
		}
	}

	//修改订单价格
	public function order_discount()
	{
		$order_id = IFilter::act(IReq::get('order_id'),'int');
		$discount = IFilter::act(IReq::get('discount'),'float');
		$orderDB  = new IModel('order');
		$orderRow = $orderDB->getObj('id = '.$order_id.' and status = 1 and distribution_status = 0 and seller_id = '.$this->seller['seller_id']);
		if($orderRow)
		{
			//还原价格
			$newOrderAmount = ($orderRow['order_amount'] - $orderRow['discount']) + $discount;
			$orderDB->setData(array('discount' => $discount,'order_amount' => $newOrderAmount));
			if($orderDB->update('id = '.$order_id))
			{
				die(JSON::encode(array('result' => true,'orderAmount' => $newOrderAmount)));
			}
		}
		die(JSON::encode(array('result' => false)));
	}
	//验证订单消费券
	public function vouchercheck() {
		$voucher = IFilter::act(IReq::get('voucher'),'int');
		$result = array('message'=>'系统错误','ok'=>'no');
		$order_voucher = new IModel('order_voucher');
		if (!$order = $order_voucher->getObj('voucher='.$voucher)){
			$result['message'] = '该消费券不存在，请重新检查输入再试试！';
			exit(JSON::encode($result));
		}
		if ($order['isused'] == 1){
			$result['message'] = '该消费券已使用！';
			exit(JSON::encode($result));
		}

		$temp = array();

		$order_goods = new IModel('order_goods');
		$goods_list = $order_goods->query('order_id='.$order['order_id']);

		foreach ($goods_list as $k => $value) {
			$name = JSON::decode($value['goods_array']);
			$temp[$k]['name'] = $name['name'];
			$temp[$k]['real_price'] = $value['real_price'];
			$temp[$k]['goods_nums'] = $value['goods_nums'];
		}

		$result['ok'] = 'yes';
		$result['message'] = $temp;
		
		echo JSON::encode($result);
	}
	//使用订单消费券
	public function voucherused() {
		$voucher = IFilter::act(IReq::get('voucher'),'int');
		$order_voucher = new IModel('order_voucher');
		if (!$result = $order_voucher->getObj('voucher='.$voucher))
			exit(JSON::encode(array('result' => 'fail', 'message' => '凭证不存在！')));
		if ($result['isused'] == 1)
			exit(JSON::encode(array('result' => 'fail', 'message' => '此凭证已验证！')));
		$order_voucher->setData(array('isused' => 1, 'usetime' => date('Y-m-d H:i:s')));
		if (false !== $order_voucher->update('voucher='.$voucher)) {
			$order_id = $result['order_id'];

			$order_show = new Order_Class();
			$data = $order_show->getOrderShow($order_id);
			if($data)
			{
				$_POST = array_merge($_POST, $data);
			}
			// $_POST['id'] = $order_id;
			$_POST['delivery_type'] = 3; // 系统默认配送方式
			$_POST['freight_id'] = 17; // 淘黄州专用自提物流

			$_POST['name'] = $data['accept_name'];
			$_POST['note'] = '验证券后自动发货哦';
			
			$this->order_delivery_doc(true); // 调用发货接口

			$order = new IModel('order');
			$order_info = $order->getObj('id = ' . $order_id);

			//确认收货后进行支付
			Order_Class::updateOrderStatus($order_info['order_no']);

    		//增加用户评论商品机会
    		Order_Class::addGoodsCommentChange($order_id);

    		// 完成
    		$order->setData(array('status' => 5,'distribution_status' => 1,'completion_time' => date('Y-m-d h:i:s')));
            if($order->update("id = ".$order_id))
			// if($order->update("id = ".$id." and distribution_status = 1 and user_id = ".$this->user['user_id']))
			{
				//确认收货后进行支付
				Order_Class::updateOrderStatus($data['order_no']);

	    		//增加用户评论商品机会
	    		Order_Class::addGoodsCommentChange($order_id);
			}

			echo JSON::encode(array('result' => 'succeed'));
		}
		else
			echo JSON::encode(array('result' => 'fail','message' => '验证失败！'));
	}
	function consultType($t,$c){
	    $type = array("在线咨询","售后服务","店长专线","金牌服务");
		$ret = array();
		$ret["name"] = $type[$t];
		$ret["nums"] = count($c);
		$ret["item"] = $c;
		$ret["id"] = $t+1;
		return $ret;
	}

	// 招标列表
	public function zhaopiao_list () {
		$this->redirect('zhaopiao_list');
	}

	// 撤销竞标
	public function zhaobiao_id_undo() {
		$zhaobiao_id = IFilter::act(IReq::get('zhaobiao_id'),'int');
		$company_id = $this->seller['seller_id'];

		$zhaobiao = new IModel('zhaobiao');
		
		if (!$zhaobiao_info = $zhaobiao->getObj('id = ' . $zhaobiao_id)) {
			echo JSON::encode(array('result' => 'fail','message' => '招标不存在！'.$zhaobiao_id));
			exit;
		}

		$zhaobiao_log = new IModel('zhaobiao_log');

		if (!$zhaobiao_log->getObj('zhaobiao_id = ' . $zhaobiao_id . ' and company_id = ' . $company_id)) {
			echo JSON::encode(array('result' => 'fail','message' => '你还没有投过该标，咋个撤销竞标呢！'));
			exit;
		}

		if ($zhaobiao_log->del('zhaobiao_id = ' . $zhaobiao_id . ' and company_id = ' . $company_id)) {

			// 减少竞标次数
			$zhaobiao->setData(array('looks' => 'looks - 1'));
			$zhaobiao->update('id = '.$zhaobiao_id,'looks');

			// echo JSON::encode(array('result' => 'succeed','message' => '撤销成功'));
			$this->redirect('zhaobiao_list');
		}

	}


	// 装修公司竞标
	public function zhaobiao_jinbiao() {
		$zhaobiao_id = IFilter::act(IReq::get('zhaobiao_id'),'int');

		// $company = new IModel('jiaju_company');
		// $company = $company->getObj('seller_user_id = ' . $this->seller['seller_id']);

		// if (!$company) {
		// 	echo JSON::encode(array('result' => 'fail','message' => '装修公司不存在！'));
		// 	exit;
		// }

		// $company_id = $company['id'];

		$company_id = $this->seller['seller_id'];

		// if ($this->seller['user_type'] != 1) {
			// echo JSON::encode(array('result' => 'fail','message' => '你没权竞标'));
			// exit;
		// }


		$zhaobiao = new IModel('zhaobiao');
		
		if (!$zhaobiao_info = $zhaobiao->getObj('id = ' . $zhaobiao_id)) {
			echo JSON::encode(array('result' => 'fail','message' => '招标不存在！'));
			exit;
		}

		if ($zhaobiao_info['status'] != 1) {
			echo JSON::encode(array('result' => 'fail','message' => '该标状态不合法'));
			exit;
		}

		$zhaobiao_log = new IModel('zhaobiao_log');

		if ($zhaobiao_log->getObj('zhaobiao_id = ' . $zhaobiao_id . ' and company_id = ' . $company_id)) {
			// echo JSON::encode(array('result' => 'fail','message' => '你已投过该标，不能重复竞标！'));
			echo '<script>alert("你已投过该标，不能重复竞标！");history.go(-1);</script>';
			exit;
		}

		$zhaobiao_log->setData(
			array(
				'zhaobiao_id' => $zhaobiao_id,
				'company_id' => $company_id,
				'create_time' => ITime::getDateTime(),
			)
		);
		if ($zhaobiao_log->add()) {

			// 增加竞标次数
			$zhaobiao->setData(array('looks' => 'looks + 1'));
			$zhaobiao->update('id = '.$zhaobiao_id,'looks');

			// echo JSON::encode(array('result' => 'succeed','message' => '竞标成功'));

			echo '<script>alert("竞标成功，你可以回到商户中心查看！");history.go(-1);</script>';
			exit;

			// $this->redirect('zhaobiao_list');
		}
	}

	// 装修公司竞标
	public function zhaobiao_jinbiao2() {
		$zhaobiao_id = IFilter::act(IReq::get('zhaobiao_id'),'int');
		$shop_name = IFilter::act(IReq::get('shop_name', 'post'),'text');
		$contact = IFilter::act(IReq::get('contact', 'post'),'text');
		$mobile = IFilter::act(IReq::get('mobile', 'post'),'text');
		$addr = IFilter::act(IReq::get('addr', 'post'),'text');
		$baojia = IFilter::act(IReq::get('baojia', 'post'),'int');

		$zhaobiao = new IModel('zhaobiao');
		
		if (!$zhaobiao_info = $zhaobiao->getObj('id = ' . $zhaobiao_id)) {
			echo JSON::encode(array('result' => 'fail','message' => '招标不存在！'));
			exit;
		}

		if ($zhaobiao_info['status'] != 1) {
			echo JSON::encode(array('result' => 'fail','message' => '该标状态不合法'));
			exit;
		}

		$zhaobiao_log2 = new IModel('zhaobiao_log2');

		if ($zhaobiao_log2->getObj('zhaobiao_id = ' . $zhaobiao_id . ' and mobile = ' . $mobile)) {
			echo JSON::encode(array('result' => 'fail','message' => '你已投过该标，不能重复竞标！'));
			exit;
		}

		$zhaobiao_log2->setData(
			array(
				'zhaobiao_id' => $zhaobiao_id,
				'shop_name' => $shop_name,
				'contact' => $contact,
				'mobile' => $mobile,
				'addr' => $addr,
				'baojia' => $baojia,
				'create_time' => ITime::getDateTime(),
			)
		);
		if ($zhaobiao_log2->add()) {
			// 增加竞标次数
			$zhaobiao->setData(array('looks' => 'looks + 1'));
			$zhaobiao->update('id = '.$zhaobiao_id,'looks');
			echo JSON::encode(array('result' => 'succeed','message' => '感谢，竞标成功！'));
		}
	}
}