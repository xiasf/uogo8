<?php
/**
 * @file deco.php
 * @brief 装修模块
 * @note
 */

class V_Deco extends IController
{
    public $layout='lay_deco';

	function init()
	{
		CheckRights::checkUserRights();
	}


	function index()
	{
		$siteConfigObj = new Config("site_config");
		$site_config   = $siteConfigObj->getInfo();
		$index_slide = isset($site_config['index_slide'])? unserialize($site_config['index_slide']) :array();
		$this->index_slide = $index_slide;
		$this->redirect('index');
	}


	// 获取全部子分类
	private function get_all_category_list($id) {
		$result = array($id);
		$catDB  = new IModel('jiaju_article_category');
		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$temp = $catDB->query( 'parent_id = '.$id);
			foreach($temp as $key => $val)
			{
				$result[] = $val['id'];
			}
			next($result);
		}
		return $result;
	}


	// 获取父级分类
	private function category_list($article_category, $pid) {
		$b = array();
		if ($tem = $article_category->getObj('id = ' . $pid)) {
			$b[] = $tem;
			$b = array_merge($this->category_list($article_category, $tem['parent_id']), $b);
		}
		return $b;
	}


	// bbs文章模块
	public function article_bbs_son() {
		$id = IFilter::act(IReq::get('id'),'int');
		$article = new IModel('jiaju_article');
		$this->article = $article->getObj('id = ' . $id);

		if (!$this->article)
			$this->redirect("gonglve");

		$cid = $this->article['category_id'];

		$article_category = new IModel('jiaju_article_category');

		$this->category_list = $this->category_list($article_category, $cid);

		//增加浏览次数
		$article_visit    = ISafe::get('article_visit');
		$checkStr = "#".$id."#";
		if($article_visit && strpos($article_visit,$checkStr) !== false)
		{
		}
		else
		{
			$article->setData(array('visit' => 'visit + 1'));
			$article->update('id = '.$id,'visit');
			$article_visit = $article_visit === null ? $checkStr : $article_visit.$checkStr;
			ISafe::set('article_visit',$article_visit);
		}
		$this->redirect("article_bbs_son");
	}


	// 装修文章
	public function yezhu(){
		$id = IFilter::act(IReq::get('id'),'int');
		$article = new IModel('jiaju_article');
		$this->article = $article->getObj('id = ' . $id);

		if (!$this->article)
			$this->redirect("gonglve");

		$cid = $this->article['category_id'];

		$article_category = new IModel('jiaju_article_category');

		$this->category_list = $this->category_list($article_category, $cid);

		//增加浏览次数
		$article_visit    = ISafe::get('article_visit');
		$checkStr = "#".$id."#";
		if($article_visit && strpos($article_visit,$checkStr) !== false)
		{
		}
		else
		{
			$article->setData(array('visit' => 'visit + 1'));
			$article->update('id = '.$id,'visit');
			$article_visit = $article_visit === null ? $checkStr : $article_visit.$checkStr;
			ISafe::set('article_visit',$article_visit);
		}

		// 判断当前是否可赞
		$article_praise    = ISafe::get('article_praise');
		$checkStr = "#".$id."#";
		if($article_praise && strpos($article_praise,$checkStr) !== false)
		{
		} else {
			$this->article_praise = true;
		}
		

//var_dump(self::generateTree($Handle->find()));

		$this->config['keys'] = $this->article['keyword'];


		$this->redirect("yezhu");
	}
	//提取评论数据
	function acticle_comment_sourec(){
/*		error_reporting(E_ALL);
		header('Content-Type: text/event-stream'); 
		header('Cache-Control: no-cache'); 
		$time = time(); 
		echo "data: The  {$time} \n\n"; 
		flush();*/		
		$id = IFilter::act(IReq::get('id'),'int');
		$Handle = new IQuery('jiaju_article_comment as jac');
		$Handle->order    = "jac.id desc";
		$Handle->distinct = "jac.id";
		$Handle->fields   = "jac.*,m.true_name,u.head_ico ";
		$Handle->where    = ' jac.article_id = '.$id;
		$Handle->join     = " left join user as u on u.id = jac.user_id  left join member as m on m.user_id = u.id";
		echo JSON::encode(self::generateTree($Handle->find()));
	}
	//把评论转换成树形结构
	function generateTree($rows, $id='id', $pid='re_id'){  
        $items = array();  
        foreach ($rows as $row) {
			$items[$row[$id]] = $row;
			$items[$row[$id]]['comment_time'] = ITime::tran(ITime::getTime($items[$row[$id]]['comment_time']));
			//comment_time
		}
        foreach ($items as $item)$items[$item[$pid]]['lv'][$item[$id]] = $items[$item[$id]]; 
        return isset($items[0]['lv']) ? $items[0]['lv'] : array();  
    }  

	public function article_list() {
		$cid = IFilter::act(IReq::get('cid'), 'int');

		$tag = IFilter::act(IReq::get('tag'), 'int');

		$where =  '';
		$article = new IQuery('jiaju_article');
		if ($cid)
			$where = 'category_id in (' . join(',', $this->get_all_category_list($cid)) . ')';
		if ($tag)
			$where .= ' and FIND_IN_SET("'.$tag.'",tag)';

		$article->where = trim($where, ' and');
		$article->limit = 50;
		$article->order = 'id desc,sort asc';
		$article->page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$this->article_list = $article->find();
		$this->getPageBar = $article->getPageBar();

		$this->redirect("article_list");
	}
	
	public function company(){
		$this->config['title'] = "装修公司 - "; 
		//echo $this->config['name']; 
		//var_dump($this->config); 
		$this->redirect("company"); 
	}

	// 展示公司评论
	public function company_comments() {
		$this->layout='lay_deco_min';
		$company_id     = IFilter::act(IReq::get('id'),'int');

		$company = new IModel('jiaju_company');
		$this->company = $company->getObj('id = ' . $company_id);

		
		$this->redirect('company_comments');
	}

	// 展示公司设计师
	public function company_designer() {
		$this->layout='lay_deco_min';
		$company_id     = IFilter::act(IReq::get('id'),'int');

		$company = new IModel('jiaju_company');
		$this->company = $company->getObj('id = ' . $company_id);

		// 公司关联的设计师
		$designer = new IQuery('jiaju_designer');
		$designer->where = 'company = ' . $this->company['id'];
		$designer->limit = 6;
		$this->designer = $designer->find();
		
		$this->redirect('company_designer');
	}

	// 设计师页面
	public function designer() {
		$designer_id     = IFilter::act(IReq::get('id'),'int');

		$designer = new IModel('jiaju_designer');
		$this->designer = $designer->getObj('id = ' . $designer_id);

		if (!$this->designer)
			exit('设计师不存在！');

		// 设计师关联的设计方案
		$designer_works = new IQuery('jiaju_designer_works');
		$designer_works->where = 'status = 1 and designer = ' . $designer_id;
		$designer_works->limit = 6;
		$this->designer_works = $designer_works->find();
		
		$this->redirect('designer');
	}


	// 展示公司设计方案
	public function company_designer_works() {
		$this->layout='lay_deco_min';
		$company_id     = IFilter::act(IReq::get('id'),'int');

		$company = new IModel('jiaju_company');
		$this->company = $company->getObj('id = ' . $company_id);

		// 公司关联的设计师
		$designer = new IQuery('jiaju_designer');
		$designer->where = 'company = ' . $this->company['id'];
		$designer->limit = 6;
		$this->designer = $designer->find();

		$designer_list = array();
		foreach ($this->designer as $value) {
			$designer_list[] = $value['id'];
		}

		if ($designer_list) {
			// 公司关联的设计方案
			$designer_works = new IQuery('jiaju_designer_works');
			$designer_works->where = 'designer in (' . join(',', $designer_list).') and status = 1';
			$designer_works->limit = 6;
			$this->designer_works = $designer_works->find();
		} else {
			$this->designer_works = array();
		}
		
		$this->redirect('company_designer_works');

	}

	public function company_info(){
		$this->layout='lay_deco_min';

		$company_id     = IFilter::act(IReq::get('id'),'int');
		$company = new IModel('jiaju_company');
		$this->company = $company->getObj('id = ' . $company_id);

		// 公司关联的效果图
		$show_img = new IModel('jiaju_show_img');
		$this->show_img = $show_img->getObj('company = ' . $this->company['id']);

		// 公司关联的设计师
		$designer = new IQuery('jiaju_designer');
		$designer->where = 'company = ' . $this->company['id'];
		$designer->limit = 6;
		$this->designer = $designer->find();


		$designer_list = array();
		foreach ($this->designer as $value) {
			$designer_list[] = $value['id'];
		}

		if ($designer_list) {
			// 公司关联的设计方案
			$designer_works = new IQuery('jiaju_designer_works');
			$designer_works->where = 'designer in (' . join(',', $designer_list).') and status = 1';
			$designer_works->limit = 6;
			$this->designer_works = $designer_works->find();
		} else {
			$this->designer_works = array();
		}
		

		$this->redirect('company_info');
	}

	public function designer_works(){
		$this->layout='lay_deco_min';

		$id     = IFilter::act(IReq::get('id'),'int');
		$designer_works = new IModel('jiaju_designer_works');
		$this->designer_works = $designer_works->getObj('id = ' . $id);

		$designer = new IModel('jiaju_designer');
		$this->designer = $designer->getObj('id = ' . $this->designer_works['designer']);

		$company = new IModel('jiaju_company');
		$this->company = $company->getObj('id = ' . $this->designer['company']);

		$this->redirect('designer_works');
	}


	// 拼接筛选url
	public function searchUrl($queryKey,$queryVal = '')
	{
		$urlVal = IReq::get($queryKey);
		//如果此项url中没有$urlVal 并且 赋值还存在，则直接追加到url中即可
		if($urlVal === null && $queryVal !== '')
		{
			if ($_SERVER['QUERY_STRING'])
				return IFilter::clearUrl('?'.$_SERVER['QUERY_STRING'].'&'.$queryKey.'='.$queryVal);
			else
				return IFilter::clearUrl('?'.$queryKey.'='.$queryVal);
		}
		else
		{
			// 完美
			parse_str($_SERVER['QUERY_STRING'], $arr);

			if ($queryVal === '') {
				unset($arr[$queryKey]);
			} else {
				$arr[$queryKey] = $queryVal;
			}
			return IFilter::clearUrl('?' . http_build_query($arr));

			// $fromStr[] = '&'.$queryKey.'='.$urlVal;
			// if($queryVal === '')
			// {
			// 	$toStr = '';
			// }
			// else
			// {
			// 	$toStr[] = '&'.$queryKey.'='.$queryVal;
			// }
			// return IFilter::clearUrl(str_replace($fromStr,$toStr,'?'.urldecode($_SERVER['QUERY_STRING'])));
		}
	}

	public function effectpic_json(){
		$space = IFilter::act(IReq::get('space'),'int');
		$local = IFilter::act(IReq::get('local'),'int');
		$style = IFilter::act(IReq::get('style'),'int');

		$show_img = new IQuery('jiaju_show_img');
		$where = 'status = 1';
		if ($space)
			$where .= ' and space_type = ' . $space;
		if ($local)
			$where .= ' and local_type = ' . $local;
		if ($style)
			$where .= ' and style_type = ' . $style;
		$show_img->where = $where;
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$show_img->order = 'sort asc, id desc';
		$show_img->page = $page;
		$show_img_list = $show_img->find();

/*		foreach ($show_img_list as $key => &$value) {
			$value['img_list'] = current(explode(',', $value['img_list']));

		}*/
		echo json_encode($show_img_list);
	}

	// 装修申请
	public function zb_post() {

		$result = array(
			'status' => 1,
			'info'    => '申请成功！稍后会有客服与您联系。',
		);

		$data = array(
			'name' => IFilter::act(IReq::get('name'),'text'),
			'mobile' => IFilter::act(IReq::get('mobile'),'text'),
			'oarea' => IFilter::act(IReq::get('oarea'),'text'),
			'zxys' => IFilter::act(IReq::get('zxys'),'text'),
			'datetime' => ITime::getDateTime(),
		);

		$zx_post = new IModel('jiaju_zx_post');

		$zx_post->setData($data);
		if (!$zx_post->add())
			$result = array(
				'status' => 0,
				'info'    => '申请失败！',
			);
		
		echo JSON::encode($result);
	}


	// 获取文章评论
	public function article_comment_json () {
		$article_id = IFilter::act(IReq::get('article_id'),'int');
		$article_comment = new IQuery('jiaju_article_comment');
		$article_comment->where = 'article_id = ' . $article_id;
		$page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$article_comment->order = 'id desc';
		$article_comment->page = $page;
		$article_comment_list = $article_comment->find();
		echo JSON::encode($article_comment_list);
	}


	// 评论
	public function article_comment_post() {
		$result = array(
			'status' => 1,
			'info'    => '评论成功',
		);
		$data = array(
			'user_id' => $this->user['user_id'] ? : 0,
			're_id' => IFilter::act(IReq::get('re_id'),'int'),
			'article_id' => IFilter::act(IReq::get('article_id'),'int'),
			'contents' => IFilter::act(IReq::get('contents'),'text'),
			'comment_time' => ITime::getDateTime(),
		);
		$zx_post = new IModel('jiaju_article_comment');
		$zx_post->setData($data);
		if (!$zx_post->add())
			$result = array(
				'status' => 0,
				'info'    => '评论失败',
			);
		//echo mysql_insert_id();
		echo JSON::encode($result);
	}


	// 文章赞
	public function article_praise() {
		$id = IFilter::act(IReq::get('id'),'int');
		$article_praise    = ISafe::get('article_praise');
		$checkStr = "#".$id."#";
		if($article_praise && strpos($article_praise,$checkStr) !== false)
		{
			$result = array(
				'status' => 0,
				'info'    => '已赞',
			);
			echo JSON::encode($result);
			exit;
		}
		else
		{
			$article_praise = $article_praise === null ? $checkStr : $article_praise.$checkStr;
			ISafe::set('article_praise',$article_praise);
		}


		$result = array(
			'status' => 1,
			'info'    => '赞成功',
		);
		$article = new IModel('jiaju_article');
		$data = array(
			'praise' => 'praise + 1',
		);
		$article->setData($data);
		if (!$article->update('id='. $id, array('praise')))
			$result = array(
				'status' => 0,
				'info'    => '赞失败',
			);
		echo JSON::encode($result);
	}


	// 评论赞
	public function article_comment_praise() {
		$result = array(
			'status' => 1,
			'info'    => '赞成功',
		);
		$zx_post = new IModel('jiaju_article_comment');
		$data = array(
			'praise' => 'praise + 1',
		);
		$zx_post->setData($data);
		if (!$zx_post->update('id='. IFilter::act(IReq::get('id'),'int'), array('praise')))
			$result = array(
				'status' => 0,
				'info'    => '赞失败',
			);
		echo JSON::encode($result);
	}



	public function article_bbs() {
		$cid = IFilter::act(IReq::get('cid'), 'int');

		$tag = IFilter::act(IReq::get('tag'), 'int');

		$where =  '';
		$article = new IQuery('jiaju_article');
		if ($cid)
			$where = 'category_id in (' . join(',', $this->get_all_category_list($cid)) . ')';
		if ($tag)
			$where .= ' and FIND_IN_SET("'.$tag.'",tag)';

		$article->where = trim($where, ' and');
		$article->limit = 50;
		$article->order = 'id desc,sort asc';
		$article->page = IReq::get('page') ? IFilter::act(IReq::get('page'),'int') : 1;
		$this->article_list = $article->find();
		$this->getPageBar = $article->getPageBar();
		$this->redirect("article_bbs");
	}

	function bbs_post_act() {
		$result = array(
			'status' => 1,
			'info'    => '发表成功',
		);
		$articleObj = new IModel('jiaju_article');
		$dataArray  = array(
			'title'       => IFilter::act(IReq::get('title','post')),
			'content'     => IFilter::act(IReq::get('content','post'),'text'),
			'category_id' => IFilter::act(IReq::get('category_id','post'),'int'),
			'create_time' => ITime::getDateTime(),
			'description' => IFilter::act(IReq::get('description','post'),'text'),
			'img' => IFilter::act(IReq::get('img','post'),'text'),
		);
		$articleObj->setData($dataArray);
		$articleObj->add();
		echo JSON::encode($result);
	}


	// 招标
	function zb_post_act() {
		$result = array(
			'status' => 1,
			'info'    => '发布成功',
		);
		$zbObj = new IModel('zhaobiao');
		$dataArray = array(
			'user_id' => $this->user['user_id'] ? : 0,
			'title'       => IFilter::act(IReq::get('title','post')),
			'from'   => IFilter::act(IReq::get('from','post')),
			'contact'      => IFilter::act(IReq::get('contact','post')),
			'home_id'      => IFilter::act(IReq::get('home_id','post'),'int'),
			'way_id'      => IFilter::act(IReq::get('way_id','post')),
			'type_id'      => IFilter::act(IReq::get('type_id','post')),
			'style_id'      => IFilter::act(IReq::get('style_id','post')),
			'budget_id'      => IFilter::act(IReq::get('budget_id','post')),
			'service_id'      => IFilter::act(IReq::get('service_id','post')),
			'house_type_id'      => IFilter::act(IReq::get('house_type_id','post')),
			'house_mj'      => IFilter::act(IReq::get('house_mj','post')),
			'area'      => IFilter::act(IReq::get('area','post')),
			'address'      => IFilter::act(IReq::get('address','post')),
			'comment'      => IFilter::act(IReq::get('comment','post')),
			'zx_time'      => IFilter::act(IReq::get('zx_time','post')),
			'gold'      => IFilter::act(IReq::get('gold','post')),
			'max_look'      => IFilter::act(IReq::get('max_look','post')),
			'looks'      => IFilter::act(IReq::get('looks','post')),
			'views'      => IFilter::act(IReq::get('views','post')),
			'status'      => IFilter::act(IReq::get('status','post')),
			'remark'      => IFilter::act(IReq::get('remark','post')),
			'audit'      => IFilter::act(IReq::get('audit','post')),
			'mobile'      => IFilter::act(IReq::get('mobile','post')),
			'create_time' => ITime::getDateTime(),
		);

		if(isset($_FILES['img']['name']) && $_FILES['img']['name'])
		{
			$photoInfo = $this->picUpload();
			if(isset($photoInfo['img']['img']) && file_exists($photoInfo['img']['img']))
			{
				$dataArray['img'] = $photoInfo['img']['img'];
			}
		}

		$zbObj->setData($dataArray);
		$zbObj->add();
		echo JSON::encode($result);
	}

	function picUpload($kdir = 'zhaobiao') {
		$uploadObj = new PhotoUpload('', $kdir);
		return $uploadObj->run2();
	}

	public function zb_detail(){
		// $this->layout='lay_deco_min';
		$id     = IFilter::act(IReq::get('id'),'int');
		$zhaobiao = new IModel('zhaobiao');
		$this->zhaobiao = $zhaobiao->getObj('id = ' . $id);

		if (!$this->zhaobiao)
			$this->redirect("tenders_list");

		$this->redirect('zb_detail');
	}


	// 竞标
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