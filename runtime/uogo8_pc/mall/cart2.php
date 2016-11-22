<?php require(ITag::createRuntime("incfile/header_base"));?><title><?php echo $this->config["seo_title"].$this->config["name"];?></title><meta name='keywords' content='<?php echo $this->config[seo_keys];?>'><meta name='description' content='<?php echo $this->config[seo_desc];?>'><link href="<?php echo IUrl::getHost().$this->getWebViewPath()."skin/res/mall.css";?>" media="screen"  rel="stylesheet"  type="text/css"></head><body><?php require(ITag::createRuntime("incfile/header_top"));?><?php require(ITag::createRuntime("incfile/header_nav"));?><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate.js"></script><script type="text/javascript" charset="UTF-8" src="/runtime/_systemjs/artTemplate/artTemplate-plugin.js"></script><script skin="skin" type='text/javascript' src='<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/artTemplate/area_select.js";?>'></script><script skin="skin" type='text/javascript' src='<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/orderFormClass.js";?>'></script><script src="<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/citypicker/city.picker.data.js";?>"></script><script src="<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/citypicker/city.picker.min.js";?>"></script><link href="<?php echo IUrl::getHost().$this->getWebViewPath()."javascript/citypicker/city.picker.css";?>" rel="stylesheet"><script type='text/javascript'>//创建订单表单实例
orderFormInstance = new orderFormClass();
sellerList = <?php echo JSON::encode($this->seller);?>;
ticketList = <?php echo JSON::encode($this->prop);?>;
//DOM加载完毕
jQuery(function(){
//初始化地域联动JS模板
template.compile("areaTemplate",areaTemplate);
//收货地址数据
orderFormInstance.addressInit("<?php echo $this->defaultAddressId;?>");
//配送方式初始化
orderFormInstance.deliveryInit("<?php echo isset($this->custom['delivery'])?$this->custom['delivery']:"";?>");
//自提点初始化
$('[name="takeself"]').val(<?php echo isset($this->custom['takeself'])?$this->custom['takeself']:"";?>);
//支付方式
orderFormInstance.paymentInit("<?php echo isset($this->custom['payment'])?$this->custom['payment']:"";?>");
//商品价格
orderFormInstance.goodsSum = "<?php echo $this->final_sum;?>";
});
/**
 * 生成地域js联动下拉框
 * @param name
 * @param parent_id
 * @param select_id
 */
function createAreaSelect(name,parent_id,select_id)
{
//生成地区
$.getJSON("<?php echo IUrl::getHost().IUrl::creatUrl("/block/area_child");?>",{"aid":parent_id,"random":Math.random()},function(json)
{
$('[name="'+name+'"]').html(template.render('areaTemplate',{"select_id":select_id,"data":json}));
});
}
//[address]保存到常用的收货地址
function address_save()
{
if(orderFormInstance.addressCheck())
{
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/simple/address_add_pc");?>',$('form[name="order_form"]').serialize(),function(content){
if(content.data)
{
var addressLiHtml = template.render('addressLiTemplate',{"item":content.data});
$('.addr_list').prepend(addressLiHtml);
$('input:radio[name="radio_address"]:first').trigger('click');
}
orderFormInstance.addressSave();
});
}
}
//[delivery]根据省份地区ajax获取配送方式
function get_delivery()
{
var province = $('[name="province"]').val();
var delivery = $('[name="delivery_id"]:checked').val();
var addregion = $('[name="addregion"]').val();
province = addregion.split('/')[0];
if(!province || !delivery)
{
return;
}
var goodsId   = [];
var productId = [];
var num       = [];
$('[id^="deliveryFeeBox_"]').each(function(i)
{
var idValue = $(this).attr('id');
var dataArray = idValue.split("_");
goodsId.push(dataArray[1]);
productId.push(dataArray[2]);
num.push(dataArray[3]);
});
$.getJSON("<?php echo IUrl::getHost().IUrl::creatUrl("/block/order_delivery");?>",{"province":province,"distribution":delivery,"goodsId":goodsId,"productId":productId,"num":num},function(content){
//地区无法送达
if(content.if_delivery == 1)
{
$("#deliveryPrice").html('您选择地区部分商品无法送达');
alert('您选择地区部分商品无法送达');
}
else
{
$("#deliveryPrice").html('￥'+content.price);
orderFormInstance.protectPrice  = parseFloat(content.protect_price);
orderFormInstance.deliveryPrice = parseFloat(content.price);
orderFormInstance.doAccount();
}
});
}
//选择自提点
function selectTakeself(deliveryId)
{
art.dialog.open("<?php echo IUrl::getHost().IUrl::creatUrl("/block/takeself");?>",{
title:'选择自提点',
okVal:'选择',
ok:function(iframeWin, topWin)
{
var takeselfJson = $(iframeWin.document).find('[name="takeselfItem"]:checked').val();
if(!takeselfJson)
{
alert('请选择自提点');
return false;
}
var json = $.parseJSON(takeselfJson);
$('#takeself'+deliveryId).empty();
$('[name="takeself"]').val(json.id);
$('#takeself'+deliveryId).html(template.render('takeselfTemplate',{"item":json}));
return true;
}
});
}
</script><div class="wrapper clearfix"><div class="position mt_10"><span>您当前的位置：</span><a href="<?php echo IUrl::getHost().IUrl::creatUrl("");?>">首页</a>» 填写核对订单信息</div><div class="flow_step_no2 flow_cart"><ul><li class="step_1">1、查看购物车</li><li class="step_2">2、填写核对订单信息</li><li class="step_3">3、成功提交订单</li></ul></div><form action='<?php echo IUrl::getHost().IUrl::creatUrl("/simple/cart3");?>' method='post' name='order_form' callback='orderFormInstance.isSubmit();'><input type='hidden' name='timeKey' value='<?php echo time();?>' /><input type='hidden' name='direct_gid' value='<?php echo $this->gid;?>' /><input type='hidden' name='direct_type' value='<?php echo $this->type;?>' /><input type='hidden' name='direct_num' value='<?php echo $this->num;?>' /><input type='hidden' name='direct_promo' value='<?php echo $this->promo;?>' /><input type='hidden' name='direct_active_id' value='<?php echo $this->active_id;?>' /><input type='hidden' name='takeself' value='0' /><div class="cart_box m_10"><div class="title">填写核对订单信息</div><div class="cont"><div class="wrap_box"><h3><span class="orange">收货人信息</span><a class="normal f12" href="javascript:void(0)" id="addressToggleButton" onclick="orderFormInstance.addressModToggle();">[退出]</a></h3><table class="form_table" id="address_show_box" style='display:none'><col width="120" /><col /><tbody id="addressShowBox"></tbody><script type='text/html'  id='addressShowTemplate'><tr><th>收货人姓名：</th><td><%=accept_name%></td></tr><tr><th>地区：</th><td><%=addregion%></td></tr><tr><th>详细地址：</th><td><%=address%></td></tr><tr><th>手机号码：</th><td><%=mobile%></td></tr></script></table><div class="prompt_4 m_10" id='address_often'><strong>常用收货地址</strong><ul class="addr_list"><?php foreach($this->addressList as $key =>$item){?><li><label><input class="radio" name="radio_address" type="radio" value="<?php echo isset($item['id'])?$item['id']:"";?>" onclick='orderFormInstance.addressSelected(<?php echo JSON::encode($item);?>);' /><?php echo isset($item['accept_name'])?$item['accept_name']:"";?>&nbsp;&nbsp;&nbsp;&nbsp;<?php echo isset($item['addregion'])?$item['addregion']:"";?><?php echo isset($item['address'])?$item['address']:"";?></label></li><?php }?><li><label><input type='radio' name='radio_address' onclick='orderFormInstance.addressEmpty();' value='' />其他收货地址</label></li></ul><script type='text/html'  id='addressLiTemplate'><li><label><input class="radio" name="radio_address" type="radio" value="<%=item['id']%>" onclick='orderFormInstance.addressSelected(<%=jsonToString(item)%>);' /><%=item['accept_name']%>&nbsp;&nbsp;&nbsp;&nbsp;<%=item['addregion']%><%=item['address']%></label></li></script></div><div class="box" id='address_form'><table class="form_table"><col width="90px" /><col /><tbody><tr><th>收货人姓名：</th><td><input class="normal" type="text" name="accept_name" pattern='required' alt='收件人姓名不能为空' /><span>(*) 收货人的姓名</span></td></tr><tr><th>地区：</th><td style="overflow: auto;"><div style="position: absolute;"><input id="city-picker" class="normal" readonly type="text" value="" name="addregion" data-toggle="city-picker"></div></td></tr><tr><th>详细地址：</th><td><input class="normal" name='address' type="text" alt='格式不正确' pattern='required' /><span>(*) 收货地址</span></td></tr><tr><th>手机号码：</th><td><input class="middle" name='mobile' type="text" pattern='mobi' alt='格式不正确' /><span>(*) 收货人的手机号，用于接收发货通知短信及送货前确认</span></td></tr></tbody></table></div><label class="btn_orange3" id='address_save_button'><input type="button" value="保存收货人地址" onclick="address_save();" /></label></div><div class="wrap_box" id='deliveryBox' style='display:none'><h3><span class="orange">配送方式</span><a class="normal f12" href="javascript:void(0)" id='deliveryToggleButton' onclick="orderFormInstance.deliveryModToggle();">[关闭]</a></h3><table class="form_table" id="delivery_show_box" style='display:none'><col width="120px" /><col /><tbody id="deliveryShowBox"></tbody><script type='delivery'  id='deliveryShowTemplate'><tr><th>配送方式：</th><td><%=name%></td></tr><tr><th>运费：</th><td id="deliveryPrice"></td></tr></script></table><table width="100%" class="border_table m_10" id='delivery_form'><col width="180px" /><col /><tbody><?php $deliveryData = Api::run('getDeliveryList')?><?php foreach($deliveryData as $key =>$item){?><tr><th><label><input type="radio" name="delivery_id" value="<?php echo isset($item['id'])?$item['id']:"";?>" paytype="<?php echo isset($item['type'])?$item['type']:"";?>" onclick='orderFormInstance.deliverySelected(<?php echo JSON::encode($item);?>);' /><?php echo isset($item['name'])?$item['name']:"";?></label></th><td><?php echo isset($item['description'])?$item['description']:"";?><?php if($item['type'] == 2){?><a href="javascript:selectTakeself(<?php echo isset($item['id'])?$item['id']:"";?>);"><span class="red">选择自提点</span></a><span id="takeself<?php echo isset($item['id'])?$item['id']:"";?>"></span><?php }?></td></tr><?php }?></tbody><script type='text/html'  id='takeselfTemplate'><%=item['province_str']%><%=item['city_str']%><%=item['area_str']%><%=item['address']%><%=item['name']%><%=item['phone']%><%=item['mobile']%></script><tfoot><th>指定送货时间：</th><td><label class='attr'><input type='radio' name='accept_time' checked="checked" value='任意' />任意</label><label class='attr'><input type='radio' name='accept_time' value='周一到周五' />周一到周五</label><label class='attr'><input type='radio' name='accept_time' value='周末' />周末</label></td></tfoot></table><label class="btn_orange3" id="delivery_save_button"><input type="button" onclick="orderFormInstance.deliverySave();" value="保存配送方式" /></label></div><div class="wrap_box" id='paymentBox' style='display:none'><h3><span class="orange">支付方式</span><a class="normal f12" href="javascript:void(0)" id='paymentToggleButton' onclick="orderFormInstance.paymentModToggle();">[关闭]</a></h3><table width="100%" class="border_table" id='payment_form'><colgroup><col width="200px" /><col /></colgroup><?php $paymentList=Api::run('getPaymentList')?><?php foreach($paymentList as $key =>$item){?><?php $paymentPrice = CountSum::getGoodsPaymentPrice($item['id'],$this->sum);?><tr><th><label><input class="radio" name="payment" alt="<?php echo isset($paymentPrice)?$paymentPrice:"";?>" onclick='orderFormInstance.paymentSelected(<?php echo JSON::encode($item);?>);' title="<?php echo isset($item['name'])?$item['name']:"";?>" type="radio" value="<?php echo isset($item['id'])?$item['id']:"";?>" /><?php echo isset($item['name'])?$item['name']:"";?></label></th><td><?php echo isset($item['note'])?$item['note']:"";?>支付手续费：￥<?php echo isset($paymentPrice)?$paymentPrice:"";?></td></tr><?php }?></table><table class="form_table" id="payment_show_box" style='display:none'><col width="120px" /><col /><tbody id="paymentShowBox"></tbody></table><script type='text/html'  id='paymentShowTemplate'><tr><th>支付方式：</th><td><%=name%></td></tr></script><label class="btn_orange3" id='payment_save_button'><input type="button" onclick="orderFormInstance.paymentSave();" value="保存支付方式" /></label></div><div class="wrap_box"><h3><span class="orange">订单附言</span><a class="normal f12" href="javascript:void(0)" id='messageToggleButton' onclick="orderFormInstance.messageModToggle();">[修改]</a></h3><table width="100%" class="border_table" id='message_show_box'><col width="120px" /><col /><tbody><tr><th>订单附言：</th><td id="messageShowBox"></td></tr></tbody></table><table width="100%" class="form_table" id='message_form' style='display:none'><col width="120px" /><col /><tr><th>订单附言：</th><td><input class="normal" type="text" name='message' /></td></tr></table><label class="btn_orange3" id='message_save_button' style='display:none'><input type="button" onclick="orderFormInstance.messageSave();" value="保存订单附言" /></label></div><div class="wrap_box"><h3><span class="orange">购买的商品</span></h3><div class="cart_prompt f14 t_l m_10" <?php if(empty($this->promotion)){?>style="display:none"<?php }?>><p class="m_10 gray"><b class="orange">恭喜，</b>您的订单已经满足了以下优惠活动！</p><?php foreach($this->promotion as $key =>$item){?><p class="indent blue"><?php echo isset($item['plan'])?$item['plan']:"";?>，<?php echo isset($item['info'])?$item['info']:"";?></p><?php }?></div><table width="100%" class="cart_table t_c"><colgroup><col width="115px" /><col /><col width="80px" /><col width="80px" /><col width="80px" /><col width="80px" /><col width="80px" /></colgroup><thead><tr><th>图片</th><th>商品名称</th><th>赠送积分</th><th>单价</th><th>优惠</th><th>数量</th><th class="last">小计</th></tr></thead><tbody><?php foreach($this->goodsList as $key =>$item){?><tr><td><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/66/h/66");?>" width="66px" height="66px" alt="<?php echo isset($item['name'])?$item['name']:"";?>" title="<?php echo isset($item['name'])?$item['name']:"";?>" /></td><td class="t_l"><a href="<?php echo IUrl::getHost().IUrl::creatUrl("/site/products/id/".$item['goods_id']."");?>" class="blue"><?php echo isset($item['name'])?$item['name']:"";?></a><?php if(isset($item['spec_array'])){?><p><?php $spec_array=Block::show_spec($item['spec_array']);?><?php foreach($spec_array as $specName =>$specValue){?><?php echo isset($specName)?$specName:"";?>：<?php echo isset($specValue)?$specValue:"";?>&nbsp&nbsp
<?php }?></p><?php }?></td><td><?php echo isset($item['point'])?$item['point']:"";?></td><td><b>￥<?php echo isset($item['sell_price'])?$item['sell_price']:"";?></b></td><td>减￥<?php echo isset($item['reduce'])?$item['reduce']:"";?></td><td><?php echo isset($item['count'])?$item['count']:"";?></td><td id="deliveryFeeBox_<?php echo isset($item['goods_id'])?$item['goods_id']:"";?>_<?php echo isset($item['product_id'])?$item['product_id']:"";?>_<?php echo isset($item['count'])?$item['count']:"";?>"><b id="red2">￥<?php echo number_format($item['sum'], 2, '.', '');?></b></td></tr><?php }?></tbody></table></div></div></div><div class="cart_box" id='amountBox' style='display:none'><div class="cont_2"><strong>结算信息</strong><div class="pink_box"><p class="f14 t_l"><?php if($this->final_sum != $this->sum){?>优惠后总金额<?php }else{?>商品总金额<?php }?>：<b><?php echo $this->final_sum;?></b>- 代金券：<b name='ticket_value'>0</b>+ 税金：<b id='tax_fee'>0</b>+ 运费总计：<b id='delivery_fee_show'>0</b>+ 保价：<b id='protect_price_value'>0</b>+ 支付手续费：<b id='payment_value'>0</b></p><a href="javascript:void(0)" id="ticket_a" class="fold" hidefocus><b class="orange">使用代金券</b></a><div class="cart_box t_l gray" style='display:none' id='ticket_box'><div class="cont"><table width="100%" class="list_table m_10"><colgroup><col width="220px" /><col /><col width="250px" /></colgroup><caption class="t_l" style="padding-bottom:10px;"><b>请选择代金券，并应用于商家</b><select name="ticketUserd" onchange="selectSeller();"><?php foreach($this->sellerList as $key =>$item){?><option value="<?php echo isset($item['id'])?$item['id']:"";?>"><?php echo isset($item['true_name'])?$item['true_name']:"";?></option><?php }?></select><span class='red2'>(注：代金券仅能抵扣商品金额)</span>：
</caption><tbody id='ticket_show_box'></tbody><script type='text/html'  id='ticketTrTemplate'><tr><td class="t_l"><label><input class="radio" name="ticket_id" onclick="userTicket(<%=item.seller_id%>,<%=item.value%>);" type="radio" value="<%=item.id%>" /><%=item.name%></label></td><td class="t_l">编号：<%=item.card_name%></td><td class="t_r">优惠：<span class="red2">￥<b><%=item.value%></b></span></td></tr></script><tr><td><label class="btn_gray_m"><input type="button" onclick="cancel_ticket();" value="取消代金券" /></label></td><td colspan=2>有实体代金券？
卡号：<input type='text' class='gray_m' id='ticket_num' />密码：<input type='password' class='gray_m' id='ticket_pwd' /><label class="btn_gray_m"><input type="button" onclick="add_ticket('<?php echo IUrl::getHost().IUrl::creatUrl("/block/add_download_ticket");?>');" value="添加" /></label></td></tr></table><p class="t_r">使用了代金券 可以优惠 <b class="red2" name='ticket_value'>0</b>元</p></div></div></div><hr class="dashed" /><div class="pink_box gray m_10"><table width="100%" class="form_table t_l"><colgroup><col width="220px" /><col /><col width="250px" /></colgroup><tr><td>是否需要发票？(税金:￥<?php echo $this->goodsTax;?>) <input class="radio" onclick="orderFormInstance.doAccount();$('#tax_title').toggle();" name="taxes" type="checkbox" value="<?php echo $this->goodsTax;?>" /></td><td><label id="tax_title" class='attr' style='display:none'>发票抬头：<input type='text' class='normal' name='tax_title' /></label></td><td class="t_r"><b class="price f14">应付总额：<span class="red2">￥<b id='final_sum'><?php echo $this->final_sum;?></b></span>元</b></td></tr></table></div><p class="m_10 t_r"><input type="submit" class="submit_order" /></p></div></div></form></div><?php require(ITag::createRuntime("incfile/footer"));?></body></html>