<?php require(ITag::createRuntime("incfile/header_base"));?><title><?php echo $this->config["seo_title"].$this->config["name"];?></title><meta name='keywords' content='<?php echo $this->config[seo_keys];?>'><meta name='description' content='<?php echo $this->config[seo_desc];?>'><link href="<?php echo IUrl::getHost().$this->getWebViewPath()."skin/res/mall.css";?>" media="screen"  rel="stylesheet"  type="text/css"></head><body><?php require(ITag::createRuntime("incfile/header_top"));?><?php require(ITag::createRuntime("incfile/header_nav"));?><div class="wrapper clearfix"><div class="position mt_10"><span>您当前的位置：</span><a href="<?php echo IUrl::getHost().IUrl::creatUrl("");?>">首页</a>» 购物车</div><div class="flow_step_no1 flow_cart"><ul><li class="step_1">1、查看购物车</li><li class="step_2">2、填写核对订单信息</li><li class="step_3">3、成功提交订单</li></ul></div><div id="cart_prompt" class="cart_prompt f14 t_l" <?php if(empty($this->promotion)){?>style="display:none"<?php }?>><p class="m_10 gray"><b class="orange">恭喜，</b>您的订单已经满足了以下优惠活动！</p><?php foreach($this->promotion as $key =>$item){?><p class="indent blue"><?php echo isset($item['plan'])?$item['plan']:"";?>，<?php echo isset($item['info'])?$item['info']:"";?></p><?php }?></div><table width="100%" class="cart_table m_10"><col width="115px" /><col /><col width="80px" /><col width="80px" /><col width="80px" /><col width="80px" /><col width="80px" /><col width="80px" /><caption>查看购物车</caption><thead><tr><th>图片</th><th>商品名称</th><th>赠送积分</th><th>单价</th><th>优惠</th><th>数量</th><th>小计</th><th class="last">操作</th></tr></thead><tbody><?php foreach($this->goodsList as $key =>$item){?><tr><td><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/66/h/66");?>" width="66px" height="66px" alt="<?php echo isset($item['name'])?$item['name']:"";?>" title="<?php echo isset($item['name'])?$item['name']:"";?>" /></td><td class="t_l"><a href="<?php echo IUrl::getHost().IUrl::creatUrl("/site/products/id/".$item['goods_id']."");?>" class="blue"><?php echo isset($item['name'])?$item['name']:"";?></a><?php if(isset($item['spec_array'])){?><p><?php $spec_array=Block::show_spec($item['spec_array']);?><?php foreach($spec_array as $specName =>$specValue){?><?php echo isset($specName)?$specName:"";?>：<?php echo isset($specValue)?$specValue:"";?>&nbsp&nbsp
<?php }?></p><?php }?></td><td><?php echo isset($item['point'])?$item['point']:"";?></td><td><b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b></td><td>减￥<?php echo isset($item['reduce'])?$item['reduce']:"";?></td><td><div class="num"><?php $item_json = JSON::encode($item)?><a class="reduce" href="javascript:void(0)" onclick='cart_reduce(<?php echo isset($item_json)?$item_json:"";?>);'>-</a><input class="tiny" value="<?php echo isset($item['count'])?$item['count']:"";?>" onchange='cartCount(<?php echo isset($item_json)?$item_json:"";?>);' type="text" id="count_<?php echo isset($item['goods_id'])?$item['goods_id']:"";?>_<?php echo isset($item['product_id'])?$item['product_id']:"";?>" /><a class="add" href="javascript:void(0)" onclick='cart_increase(<?php echo isset($item_json)?$item_json:"";?>);'>+</a></div></td><td>￥<b class="red2" id="sum_<?php echo isset($item['goods_id'])?$item['goods_id']:"";?>_<?php echo isset($item['product_id'])?$item['product_id']:"";?>"><?php echo isset($item['sum'])?$item['sum']:"";?></b></td><td><a href='javascript:removeCartByJSON(<?php echo isset($item_json)?$item_json:"";?>);'>删除</a></td></tr><?php }?><tr class="stats"><td colspan="8"><span>商品总重量：<b id='weight'><?php echo $this->weight;?></b>g</span><span>商品总金额：￥<b id='origin_price'><?php echo $this->sum;?></b>- 商品优惠：￥<b id='discount_price'><?php echo $this->reduce;?></b>- 促销活动优惠：￥<b id='promotion_price'><?php echo $this->proReduce;?></b></span><br />金额总计（不含运费）：￥<b class="orange" id='sum_price'><?php echo $this->final_sum;?></b></td></tr></tbody><tfoot><tr><td colspan="2" class="t_l"><?php if($this->user['user_id']){?><a class="file" href="<?php echo IUrl::getHost().IUrl::creatUrl("/simple/deposit_cart_set");?>">寄存购物车</a><a class="file" href="javascript:deposit_cart_get();">取出购物车</a><a class="file" href="javascript:void(0)" onclick="delModel({msg:'确定要清空寄存购物车么？',link:'<?php echo IUrl::getHost().IUrl::creatUrl("/simple/deposit_cart_clear");?>'});">清空寄存购物车</a><?php }?><a class="del" href="javascript:void(0);" onclick="delModel({msg:'确定要清空购物车么？',link:'<?php echo IUrl::getHost().IUrl::creatUrl("/simple/clearCart");?>'});">清空购物车</a></td><td colspan="6" class="t_r"><?php $callback = IFilter::act(IReq::get('callback'),'text');?><a class="btn_continue" href="<?php echo IUrl::getHost().IUrl::creatUrl("".$callback."");?>">继续购物</a><?php if($this->goodsList){?><a class="btn_pay" href="javascript:check_finish();">去结算</a><?php }?></td></tr></tfoot></table></div><script type='text/javascript'>//购物车数量改动计算
function cartCount(obj)
{
var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
var countInputVal = parseInt(countInput.val());
var oldNum = countInput.data('oldNum') ? countInput.data('oldNum') : obj.count;
//商品数量大于1件
if(isNaN(countInputVal) || (countInputVal <= 0))
{
alert('购买的数量必须大于1件');
countInput.val(1);
countInput.change();
}
//商品数量小于库存量
else if(countInputVal >parseInt(obj.store_nums))
{
alert('购买的数量不能大于此商品的库存量');
countInput.val(parseInt(obj.store_nums));
countInput.change();
}
else
{
var diff = parseInt(countInputVal) - parseInt(oldNum);
if(diff == 0)
{
return;
}
//修改按钮状态
toggleSubmit("lock");
var goods_id   = obj.product_id >0 ? obj.product_id : obj.goods_id;
var goods_type = obj.product_id >0 ? "product"      : "goods";
//更新购物车中此商品的数量
$.getJSON("<?php echo IUrl::getHost().IUrl::creatUrl("/simple/joinCart");?>",{"goods_id":goods_id,"type":goods_type,"goods_num":diff,"random":Math.random()},function(content){
if(content.isError == true)
{
alert(content.message);
countInput.val(1);
countInput.change();
//修改按钮状态
toggleSubmit("open");
}
else
{
var goodsId   = [];
var productId = [];
var num       = [];
$('[id^="count_"]').each(function(i)
{
var idValue = $(this).attr('id');
var dataArray = idValue.split("_");
goodsId.push(dataArray[1]);
productId.push(dataArray[2]);
num.push(this.value);
});
countInput.data('oldNum',countInputVal);
$.getJSON("<?php echo IUrl::getHost().IUrl::creatUrl("/simple/promotionRuleAjax");?>",{"goodsId":goodsId,"productId":productId,"num":num,"random":Math.random()},function(content){
if(content.promotion.length >0)
{
$('#cart_prompt .indent').remove();
for(var i = 0;i < content.promotion.length; i++)
{
$('#cart_prompt').append('<p class="indent blue">'+content.promotion[i].plan+'，'+content.promotion[i].info+'</p>');
}
$('#cart_prompt').show();
}
else
{
$('#cart_prompt .indent').remove();
$('#cart_prompt').hide();
}
$('#weight').html(content.weight);
$('#origin_price').html(content.sum);
$('#discount_price').html(content.reduce);
$('#promotion_price').html(content.proReduce);
$('#sum_price').html(content.final_sum);
$('#sum_'+obj.goods_id+'_'+obj.product_id).html((obj.sell_price * countInputVal).toFixed(2));
//修改按钮状态
toggleSubmit('open');
});
}
});
}
}
//增加商品数量
function cart_increase(obj)
{
//库存超量检查
var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
if(parseInt(countInput.val()) + 1 >parseInt(obj.store_nums))
{
alert('购买的数量大于此商品的库存量');
}
else
{
countInput.val(parseInt(countInput.val()) + 1);
countInput.change();
}
}
//减少商品数量
function cart_reduce(obj)
{
//库存超量检查
var countInput = $('#count_'+obj.goods_id+'_'+obj.product_id);
if(parseInt(countInput.val()) - 1 <= 0)
{
alert('购买的数量必须大于1件');
}
else
{
countInput.val(parseInt(countInput.val()) - 1);
countInput.change();
}
}
//检测购买完成量
function check_finish()
{
if($.trim($('.btn_pay').val()) == 'wait')
{
window.setInterval("check_finish()", 400);
}
else
{
window.location.href = '<?php echo IUrl::getHost().IUrl::creatUrl("/simple/cart2");?>';
}
}
//锁定提交
function toggleSubmit(isOpen)
{
isOpen == 'open' ? $('.btn_pay').val('ok') : $('.btn_pay').val('wait');
}
/**
 * 读取购物车
 */
function deposit_cart_get()
{
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/simple/deposit_cart_get");?>',{"random":Math.random()},function(json)
{
if(json.isError == 1)
{
alert('读取购物车失败');
return;
}
//页面刷新
window.location.reload();
});
}
//移除购物车
function removeCartByJSON(obj)
{
var goods_id   = obj.product_id >0 ? obj.product_id : obj.goods_id;
var goods_type = obj.product_id >0 ? "product"      : "goods";
$.getJSON("<?php echo IUrl::getHost().IUrl::creatUrl("/simple/removeCart");?>",{"goods_id":goods_id,"type":goods_type,"random":Math.random()},function()
{
window.location.reload();
});
}
//列表页加入购物车统一接口
function joinCart_list(id)
{
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/simple/getProducts");?>',{"id":id},function(content){
if(!content)
{
joinCart_ajax(id,'goods');
}
else
{
var url = "<?php echo IUrl::getHost().IUrl::creatUrl("/block/goods_list/goods_id/@goods_id@/type/radio/is_products/1");?>";
url = url.replace('@goods_id@',id);
artDialog.open(url,{
id:'selectProduct',
title:'选择货品到购物车',
okVal:'加入购物车',
ok:function(iframeWin, topWin)
{
var goodsList = $(iframeWin.document).find('input[name="id[]"]:checked');
//添加选中的商品
if(goodsList.length == 0)
{
alert('请选择要加入购物车的商品');
return false;
}
var temp = $.parseJSON(goodsList.attr('data'));
//执行处理回调
joinCart_ajax(temp.product_id,'product');
return true;
}
})
}
});
}
</script><link skin="skin" rel="stylesheet" type="text/css" href="<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/jquery.bxSlider/jquery.bxslider.css";?>" /><script skin="skin" type="text/javascript" src="<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/jquery.bxSlider/jquery.bxSlider.min.js";?>"></script><script type="text/javascript">jQuery(function(){
$('#scrollpic').bxSlider({controls:false,minSlides: 5,slideWidth: 180,maxSlides: 5,pager:false});
});
</script><?php require(ITag::createRuntime("incfile/footer"));?></body></html>