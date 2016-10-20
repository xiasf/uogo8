<?php
/**
 * @file test.php
 * @brief 专用测试类
 */
class Test extends IController
{	
public $layout = 'site';





public function a(){


	$model = new IModel('xiak_test');
	$model->setData(array(
		'name' => 'test',
	));
	echo $new = $model->add();



// 	$model->setData(array(
// 		'name' => 'xxx',
// 	));
// 	$model->update('id = ' . $new);


	$info = $model->query();

	var_dump($info);


}


	// 老的商户迁移功能
	// function t() {
	// 	$seller = new IModel('seller');
	// 	$seller_user = new IModel('seller_user');

	// 	$shop_list = $seller->query();

	// 	$tem = array();

	// 	foreach ($shop_list as $value) {
	// 		$name = $value['name'];
	// 		$sellerdata = $seller_user->query('seller_name = \'' . $name .'\'');

	// 		if ($sellerdata[0]['seller_name']) {
	// 			$seller->setData(array('seller_user_id' => $sellerdata[0]['id']));
	// 			$seller->update('id =' . $value['id']);

	// 			echo $sellerdata[0]['id'] . $sellerdata[0]['seller_name'] . ' >>> ' . $name . $value['id'] . '<br />';

	// 		}

	// 		ob_flush();
	// 		flush();
	// 	}
	// }

    public function s()
    {
        $this->redirect('s');
    }

	function hello() {
		echo"string";		
	}



	public function get_new_order() {

		$last_id = IFilter::act(IReq::get('last_id'),'int');
		$callback = IFilter::act(IReq::get('callback'),'text');

		$order = new IQuery('order');
		$order_goods = new IModel('order_goods');

		if ($last_id) {
			$order->where = 'id > ' . $last_id . ' and pay_status = 1';
		} else {
			$order->limit = 10;
			$order->where = 'pay_status = 1';
		}

		$order->fields = 'id,addregion,address,accept_name,create_time,pay_time,real_freight,order_amount';
		$order->order = 'id desc';
		$result = $order->find();

		foreach ($result as $key => &$value) {
			$item = $order_goods->query('order_id = '. $value['id'], 'goods_id,img,goods_array,goods_nums,goods_price');
			foreach ($item as &$v) {
				$v['img'] = Thumb::get($v['img'], 80, 80);
				$info = json_decode($v['goods_array'], true);
				$v['name'] = $info['name'] . ' (' . $info['value'] . '￥' . $v['goods_price'] .')';
				unset($v['goods_array'], $v['goods_price']);
			}
			$value['goods_nums'] = count($item);
			$value['item'] = $item;
		}

		if ($callback) {
			echo $callback.'('.JSON::encode($result).')';
		} else {
			echo JSON::encode($result);
		}
	}

}