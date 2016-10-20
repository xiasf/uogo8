<?php
	define('PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
	
	include PATH . '/classes/Upload.class.php';

	include PATH .'/classes/Image.class.php';

	$handle = mysql_connect('127.0.0.1:3306',"root","SQL20015$#@!") or die('数据库连接失败');		// 连接MYSQL数据库
	mysql_select_db('Itaohz', $handle) or die('数据库中没有此库名');				// 找到数据库
	mysql_query("set names utf8");	

	function sy($file='')
	{
		$sy = PATH .'/plugins/sy/sy.png';
		$ttf = PATH  . '/plugins/sy/ttfs/ww.ttf';

		$_image = new Image();
		$image = new Image();

		$_image->open($sy);
		$image->open($file);

		list($_width, $_height) = $_image->size();
		list($width, $height) = $image->size();

		$h = ($height - $_height) / 2;

		$start_width = 0;
		$arr = array();
		while ($start_width <= $width) {
		    $arr[] = array($start_width, $h);
		    $image->water($sy, array($start_width, $h), 100);
		    $start_width += $_width + 10;
		}

		$image->save($file);
	}

	if ($_POST) {

	    $post = array();
	    $_imgList = array();
	    $content = array();

		$z = $_FILES['imgList'];
		$c = $_FILES['imgListCnt'];

		$config1 = array(
		    'maxSize'    =>    2097152,
		    'rootPath'   =>    PATH,
		    'savePath'   =>    'upload/imagedef/',
		    'saveName'   =>    'uniqid',
		    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
		    'autoSub'    =>    true,
		    'subName'    =>    array('date','Y-m-d'),
		);
		$upload = new Upload($config1);
	    if (!$info1 = $upload->upload(array('imgList'=>$z))) {
	        exit($upload->getError());
	    }

	    $config2 = array(
		    'maxSize'    =>    2097152,
		    'rootPath'   =>    PATH,
		    'savePath'   =>    'upload/imagedef/',
		    'saveName'   =>    'uniqid',
		    'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
		    'autoSub'    =>    true,
		    'subName'    =>    array('date','Y-m-d'),
		);
		$upload2 = new Upload($config2);
	    if (!$info2 = $upload2->upload(array('content'=>$c))) {
	       exit($upload->getError());
	    }


		$info = array_merge($info1, $info2);


		// $config = array(
		//     'maxSize'    =>    2097152,
		//     'rootPath'   =>    PATH,
		//     'savePath'   =>    'upload/imagedef/',
		//     'saveName'   =>    'uniqid',
		//     'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
		//     'autoSub'    =>    true,
		//     'subName'    =>    array('date','Y-m-d'),
		// );
		// $upload = new Upload($config);
	 //    if (!$info = $upload->upload()) {
	 //        exit($upload->getError());
	 //    }

		// print_r($info);
		// exit;
// file_put_contents(PATH.'filename.php', "<?php \r\n return ".var_export($_POST, true).';');
	    foreach ($info as $key => $value) {
	    	$url = $value['savepath'] . $value['savename'];
	    	if ($value['key'] == 'imgList') {
	    		$md5 = $value['md5'].time();
	    		$a = mysql_query("insert into itaohz_goods_photo (id,img) values ('$md5', '$url')", $handle);//var_dump($a);
	    		$_imgList[] = $url;
	    	} else {
		    	if ($key != 0)
			    	sy(PATH . $url);
	    		$content[] = '/'.$url;
	    	}
	    }

	    // file_put_contents(PATH.'filename2.php', "<?php \r\n return ".var_export($_POST, true).';');

	    // IMG
	    $post['_imgList'] = implode(',', $_imgList);

	    $post['content'] = '';
	    foreach ($content as $value) {
	    	$post['content'] .= '<img src="'.$value.'" />';
	    }
	    $post['content'] = '<p>' . $post['content'] . '</p>';

	    $post['img'] = $_imgList[0];


		// SKU
	    $post['_goods_no'][0] = 'HZ' . time();
		$post['_store_nums'][0] = 0;
		$post['_market_price'][0] = $_POST['market_price'];
		$post['_groupPrice'][0] = 0;
		$post['_sell_price'][0] = $_POST['sell_price'];
		$post['_cost_price'][0] = 0.00;
		$post['_weight'][0] = 0.00;


		// category
		$tem = explode('|', trim($_POST['goods_category'], '|'));
		$id = 59;
		foreach ($tem as $value) {
			$r = mysql_query("select id from itaohz_category where name = '$value'",$handle);
			if ($categoryInfo = mysql_fetch_assoc($r)) {
				// var_dump($categoryInfo);echo "string";
				$id = $categoryInfo['id'];//var_dump($categoryInfo);
				break;
			}
		}
		//echo $id;

		$post['_goods_category'][] = $id;


		$post['name'] = $_POST['name'];
		$post['smalldescription'] = '';


		$post['search_words'] = '';
		$post['seller_id'] = 207;
		$post['is_del'] = 0;
		$post['is_share'] = 0;
		$post['point'] = 0;
		$post['sort'] = 599;
		$post['unit'] = '千克';
		$post['exp'] = 0;
		$post['keywords'] = '';
		$post['description'] = '';
		$post['model_id'] = 0;


		// echo '<pre>';
		// print_r($info);
		// echo '</pre>';
		// echo '<hr />';

		// echo '<pre>';
		// print_r($post);
		// echo '</pre>';

		return $post;

		$a[] = $post;

		if (is_file(PATH.'filename.php')) {

			$t = require(PATH.'filename.php');

			$a = file_put_contents(PATH.'filename.php', "<?php \r\n return ".var_export(array_merge($t, $a), true).';');
		} else {
			$a = file_put_contents(PATH.'filename.php', "<?php \r\n return ".var_export($a, true).';');
		}
		var_dump($a);

	} else {
		echo 'no post';
	}

	/*

id:450
img:upload/image/2016/01/21/20160121105524549.jpg
_imgList:upload/image/2016/01/21/20160121105524213.jpg,upload/image/2016/01/21/20160121105524140.jpg,upload/image/2016/01/21/20160121105522412.jpg,upload/image/2016/01/21/20160121105524549.jpg,upload/image/2016/01/21/20160121105521591.jpg
callback:/goods/goods_list
name:éçĺ¤Šéşť ĺ¤§ĺŤĺąąçšçş§ĺ¤Šéşť çşŻĺ¤Šçśéçć çĄŤ100gč˘čŁ ćšĺçšäş§çšäťˇ
search_words:éç,ĺ¤Šéşť,ĺ¤§ĺŤĺąą,ĺ¤§ĺŤ,çšçş§,ĺ¤Šéşť,çşŻĺ¤Šçś,éç,ć ,ć çĄŤ,100g,č˘čŁ,ćšĺ,çšäş§,çšäťˇ
smalldescription:
seller_id:0
_goods_category[]:116
_goods_category[]:511
is_del:0
is_share:0
point:0
sort:99
unit:ĺĺ
exp:0
content:<p><img src="/upload/image/2016/01/21/1453344935368486.jpg" style="" title="1453344935368486.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344934567238.jpg" style="" title="1453344934567238.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344937784077.jpg" style="" title="1453344937784077.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344936462851.jpg" style="" title="1453344936462851.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344936305082.jpg" style="" title="1453344936305082.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344937967736.jpg" style="" title="1453344937967736.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344939229962.jpg" style="" title="1453344939229962.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344939190945.jpg" style="" title="1453344939190945.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344939838486.jpg" style="" title="1453344939838486.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344941821126.jpg" style="" title="1453344941821126.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344940165004.jpg" style="" title="1453344940165004.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344940163281.jpg" style="" title="1453344940163281.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344941573970.jpg" style="" title="1453344941573970.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344942677722.jpg" style="" title="1453344942677722.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344943529684.jpg" style="" title="1453344943529684.jpg"/></p><p><img src="/upload/image/2016/01/21/1453344942344790.jpg" style="" title="1453344942344790.jpg"/></p><p><br/></p>
contentwap:
keywords:
description:
_goods_no[0]:HZ145334488149
_store_nums[0]:0
_market_price[0]:125.00
_groupPrice[0]:
_sell_price[0]:110.00
_cost_price[0]:0.00
_weight[0]:0.00
model_id:0

	*/