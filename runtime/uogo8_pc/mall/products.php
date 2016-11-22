<?php require(ITag::createRuntime("incfile/header_base"));?><title><?php echo $this->config["seo_title"].$this->config["name"];?></title><meta name='keywords' content='<?php echo $this->config[seo_keys];?>'><meta name='description' content='<?php echo $this->config[seo_desc];?>'><link href="<?php echo IUrl::getHost().$this->getWebViewPath()."skin/res/mall.css";?>" media="screen"  rel="stylesheet"  type="text/css"></head><body><?php require(ITag::createRuntime("incfile/header_top"));?><?php require(ITag::createRuntime("incfile/header_nav"));?><?php $breadGuide = goods_class::catRecursion($category);?><style>body{background-color: #fff; }</style><script type="text/javascript"  src="<?php echo IUrl::getHost().$this->getWebViewPath()."skin/js/mall/okzoom.js";?>"></script><script type="text/javascript"  src="<?php echo IUrl::getHost().$this->getWebViewPath()."skin/js/layer/layer.js";?>"></script><aside class="pos area"><span>您当前的位置：</span><a href="#">首页</a><?php foreach($breadGuide as $key =>$item){?><i>&gt;</i><a href="#"><em class="classification"><?php echo isset($item['name'])?$item['name']:"";?></em></a><?php }?><i>&gt;</i><?php echo isset($name)?$name:"";?></aside><article id="detail" class="area clear"><div class="deteilpic l"><em class="you" style=""></em><figure><img id="goodsPhoto"></figure><ul id="goodsPhotos"><?php foreach($photo as $key =>$item){?><li data-src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/400/h/400");?>" data-bigimg="<?php echo IUrl::getHost().IUrl::creatUrl("".$item['img']."");?>"><a href="javascript:void(0)"><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/58/h/58");?>"></a></li><?php }?></ul><script>$(function(){
  var _goodsPhoto = $('#goodsPhoto');
  $("#goodsPhotos>li").on("click",function(){
  var _this = $(this);
  _this.siblings().removeClass();
  _this.addClass("cur");
  //_goodsPhoto.removeAttr("src");
  _goodsPhoto.attr({
  "src":_this.attr("data-src"),
  "data-okimage":_this.attr("data-bigimg")
  })
  }).eq(0).trigger("click");
  
      _goodsPhoto.okzoom({
          width: 200,
  height: 200,
  scaleWidth: 800,
  round: true,
  background: "#fff",
  //backgroundRepeat: "repeat",
  shadow: "0 0 5px #000",
  border: "1px solid #999999"
      });
    });
  </script><div class="panelC"><div class="sc new_shouc"><?php if($rid == $id){?><a href="javascript:void(0);">已收藏</a><?php }else{?><a href="javascript:void(0);" onclick="p.favorite_add(this);"><i></i>收藏</a><?php }?></div><div class="fx"><p href="#"><i></i>分享</p><ol><li class="bds_weibo"><a href="#" target="_blank"><i class="weibo"></i>微博</a></li><li><a href="#" target="_blank"><i class="t"></i>腾讯微博</a></li><li><a href="#" target="_blank"><i class="renren"></i>人人网</a></li><li><a href="#" target="_blank"><i class="qzone"></i>QQ空间</a></li></ol></div><div class="jb"><a href="#" target="_blank"><i></i>举报</a></div></div></div><div class="detailmeta l"><aside class=""><hgroup><div class="posab"></div></hgroup><h4><b><a class="js_utm_params" target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/shop/id/".$seller_id."");?>" utm_content="p_detail_seller_name"><?php echo isset($seller['shopname'])?$seller['shopname']:"";?></a></b><span></span></h4><ol><li><a class="js_utm_params" target="_blank" href="#" utm_content="p_detail_seller_prestige"><label>信誉：</label><em class="leA A34"></em></a></li><li style="display:none;"><label>开店时间：</label><em><?php echo isset($seller["create_time"])?$seller["create_time"]:"";?></em></li><li><a target="_blank" href="#"><label>资质：</label><em class="ps js_common_ps"><i class="l1"></i><i class="l2"></i><i class="l3"></i><em class="info"><sup></sup><p><i class="l1"></i><?php echo isset($this->config["name"])?$this->config["name"]:"";?>认证商家</p><p><i class="l2"></i>已缴纳保证金</p><p><i class="l3"></i>优质售后服务</p></em></em></a></li></ol><a target="_blank" href="#"><dl><dd class="ms"><label>描述</label><br><em class="up"><em class="ps js_common_ps">4.8&nbsp;<b>↑</b><em class="info"><sup></sup>高于同行业平均&nbsp;31.05% </em></em></em></dd><dd class="fw"><label>服务</label><br><em class="up"><em class="ps js_common_ps">4.8&nbsp;<b>↑</b><em class="info"><sup></sup>高于同行业平均&nbsp;30.98% </em></em></em></dd><dd class="fh"><label>发货</label><br><em class="up"><em class="ps js_common_ps">4.8&nbsp;<b>↑</b><em class="info"><sup></sup>高于同行业平均&nbsp;26.81% </em></em></em></dd></dl></a><div class="rk"><a class="js_utm_params" target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/shop/id/".$seller_id."");?>" utm_content="p_detail_seller"><label>进入店铺</label></a><a class="button_store" href="#" onClick="p.favorite_seller_add(<?php echo isset($seller_id)?$seller_id:"";?>);"><label>收藏本店</label></a></div></aside><div class="wrap l clear"><h1><?php echo isset($name)?$name:"";?></h1><h3><?php echo isset($smalldescription)?$smalldescription:"";?></h3></div><div class="panelA "><div class="bg_yel clear bg_haitao"><dl class="price"><dd class="colspan"><span class="r js_xl_span"><i class="fontYaHei">90</i>天销量&nbsp;<i class="red fontYaHei js_xl_i"><?php echo isset($buy_num)?$buy_num:"";?></i>&nbsp;件</span><strong class="red js_price_st">¥<i info="0" id="elem_price">Loading...</i></strong>&nbsp;
          <del class="js_org_price">¥<i info="0" id="elem_price_old">0</i></del><i class="icon discount"></i></dd></dl><dl class="time js_time_dl" style="display:none;"></dl><dl class="jifen js_jifen" style="<?php if($point == 0){?>display:none;<?php }?>"><dt><sub>积分</sub></dt><dd><div class="levelbox"><span class="level level1"><a href="#" target="_blank"><i class="fontArial">Z0</i>最多返<?php echo isset($point)?$point:"";?>积分<em>∨</em></a></span><ul><li class="cur"><a target="_blank" href="#">Z0&nbsp;最多返 <b>12</b>积分</a></li><li><a target="_blank" href="#">Z1&nbsp;最多返 <b>24</b>积分</a></li><li><a target="_blank" href="#">Z2&nbsp;最多返 <b>36</b>积分</a></li><li><a target="_blank" href="#">Z3&nbsp;最多返 <b>36</b>积分</a></li><li><a target="_blank" href="#">Z4&nbsp;最多返 <b>36</b>积分</a></li><li><a target="_blank" href="#">Z5&nbsp;最多返 <b>36</b>积分</a></li></ul></div><a target="_blank" href="#">积分可抵订单金额</a></dd></dl><dl class="jifen js_dc_cuxiao" style="display:none;"><dt><sub class="sxsq">促销</sub></dt><?php foreach(Api::run('getProrule',$seller_id) as $key =>$item){?><dd><?php echo isset($item['info'])?$item['info']:"";?></dd><?php }?></dl><dl class="y js_discount" style="display:none;"><dt><sub>优惠</sub></dt><dd class="js_every_youh"><a class="js_yh js_utm_params first" utm_content="p_detail_discount" target="_blank" href="#" title="本店铺满49元减5元，上不封顶 >">本店铺满<i>49</i>元减<i>5</i>元，上不封顶<em>&nbsp;&gt;</em></a></dd></dl></div><input type="hidden" id="product_id" value=""><dl class="address" id="address"><dt>运费</dt><dd>湖北 黄冈&nbsp;&nbsp;至
          <address><i></i><em class="down">∨</em><div class="diatip city"><a href="#" class="close"></a><ul><?php foreach(Api::run('getAreasListTop') as $key =>$item){?><li><a href="javascript:p.delivery('<?php echo isset($item['area_id'])?$item['area_id']:"";?>','<?php echo isset($item['area_name'])?$item['area_name']:"";?>')"><?php echo isset($item['area_name'])?$item['area_name']:"";?></a></li><?php }?></ul></div></address><em></em></dd></dl><dl class="fuwu"><dt>服务</dt><dd class="delay_deliver_block"><p><i class="icon zhe800jian">折</i>本商品由<?php echo isset($this->config['name'])?$this->config['name']:"";?>买手砍价</p><p><i class="icon i24">24</i>支付成功后，24小时内发货</p></dd></dl><div class="js_reskubox sku_box"><span class="close"></span><h3>请选择您要购买的商品信息：color</h3><?php if($spec_array){?><?php $specArray = JSON::decode($spec_array);?><?php foreach($specArray as $key =>$item){?><dl class="size clear" name="specCols"><dt><?php echo isset($item['name'])?$item['name']:"";?></dt><dd id="specList<?php echo isset($item['id'])?$item['id']:"";?>" class="specList"><ul><?php $specVal=explode(',',trim($item['value'],','))?><?php foreach($specVal as $key =>$spec_value){?><li class="skuli" data-value='{"id":"<?php echo isset($item['id'])?$item['id']:"";?>","type":"<?php echo isset($item['type'])?$item['type']:"";?>","value":"<?php echo isset($spec_value)?$spec_value:"";?>","name":"<?php echo isset($item['name'])?$item['name']:"";?>"}'><a href="javascript:void(0);"><?php if($item['type'] == 1){?><?php echo isset($spec_value)?$spec_value:"";?><?php }else{?><img skin="skin" src="<?php echo IUrl::creatUrl("".$spec_value."");?>" width='33' height='33' /><?php }?></a></li><?php }?></ul></dd></dl><?php }?><?php }?><p class="red attention"></p><dl class="numb clear"><dt>数量</dt><dd><p><i class="decrease no-drop"></i><input type="text" value="1" name="elem_buy_nums" readonly><i class="increase"></i></p><span class="kucun"><lable>库存<i class="js_kucun_i" id="elem_storeNums"><?php echo isset($store_nums)?$store_nums:"";?></i>件</lable></span><span class="red"></span></dd></dl><a href="#" class="sku_box_btn no">确认</a></div><?php if($store_nums <= 0){?><div class="submit"><span class="cart"><input type="button" class="gbtn" value="上架通知我"></span></div><?php }else{?><div class="submit"><span class="s2"><input type="button" value="立即购买" onClick="window.ug.submitBuy(this);" class="gbtn direct_buy_is_bind_mobile"></span><span class="cart"><input type="button" class="gbtn" onClick="window.ug.addCart(this);" value="加入购物车"></span></div><?php }?><div class="panelD"><span class="bt"><a rel="nofollow" target="_blank" href="#"><i>8</i>8天无理由退货</a></span><span class="sq"><a rel="nofollow" target="_blank" href="#"><i></i>退货补贴优惠券</a></span><span class="picc"><i>保</i>该商品由<?php echo isset($this->config['name'])?$this->config['name']:"";?>与中国人保（PICC）联合承保 </span><span class="no_coupon js_no_zhe_coupon" style="display:none"><i></i>本商品不支持使用A类优惠券 </span></div></div></div></article><div class="area js_seller_recommend myrecommend_s" style="display: block;"><ul><?php foreach(Api::run('getCommendList',5,['#com_id#',4,'#shop_id#',$seller_id]) as $key =>$item){?><li><a target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/products/id/".$item['id']."");?>"><sup></sup><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/180/h/180");?>"></a><h3><a target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></a></h3><p><span><i>¥</i><?php echo isset($item['sell_price'])?$item['sell_price']:"";?></span>已售<?php echo isset($item['sale'])?$item['sale']:"";?>件</p></li><?php }?></ul></div><article class="area mainwrap"><aside><div class="asidebox"><div class="nubB bm"><hgroup><span>商家</span></hgroup><p><?php echo isset($seller['shopname'])?$seller['shopname']:"";?></p><ol><li class="l1"><i></i>实力认证商家</li></ol><div class="seller"><span class="store"><a target="_blank" class="js_utm_params" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/shop/id/".$seller_id."");?>" utm_content="p_detail_right_suspension_bar"><em><i></i>&nbsp;进入店铺</em></a></span></div></div></div><div id="categorysalesranking" class="nubC clear hidden" style="display: block;"><hgroup><span>热门商品</span></hgroup><ul><?php foreach(Api::run('getCommendList',10,['#com_id#',2,'#shop_id#',$seller_id]) as $key =>$item){?><li><a target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/products/id/".$item['id']."");?>"><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/180/h/180");?>"></a><p><em>新品上架</em><span><i>￥</i><?php echo isset($item['sell_price'])?$item['sell_price']:"";?></span></p><h6><a target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></a></h6></li><?php }?></ul></div><div class="blank18"></div><div id="salesranking" class="nubA clear hidden" style="display: block;"><hgroup><span>全站热门商品</span></hgroup><ul></ul></div><div class="keywordpanel clear"><hgroup><span>随便看看</span></hgroup><ul><li><a href="#" target="_blank" title="长款修身外套">长款修身外套</a></li><li><a href="#" target="_blank" title="女靴2016">女靴2016</a></li><li><a href="#" target="_blank" title="棉衣加厚外套">棉衣加厚外套</a></li><li><a href="#" target="_blank" title="夹克休闲外套">夹克休闲外套</a></li><li><a href="#" target="_blank" title="加厚裤女">加厚裤女</a></li><li><a href="#" target="_blank" title="2016新款卫衣">2016新款卫衣</a></li><li><a href="#" target="_blank" title="打底女士毛衣">打底女士毛衣</a></li><li><a href="#" target="_blank" title="女士呢大衣">女士呢大衣</a></li><li><a href="#" target="_blank" title="女式大衣外套">女式大衣外套</a></li><li><a href="#" target="_blank" title="女款加绒裤">女款加绒裤</a></li></ul></div><div class="asidebox1" style="top: 40px;"><div class="nubE bm clear"><span class="daiyanren1"></span><hgroup>优购网承诺</hgroup><ul><li class="jy"><a rel="nofollow" target="_blank" href="#"><i></i>人工验货</a></li><li class="as"><a target="_blank" href="#" rel="nofollow"><i></i>按时发货</a></li><li class="bt"><a rel="nofollow" target="_blank" href="#"><i></i>8天无理由退货</a></li><li class="xx"><a rel="nofollow" target="_blank" href="#"><i></i>先行赔付</a></li></ul></div><div class="nubD"><a href="#" target="_blank"><img skin="skin" src="http://i4.tuanimg.com/shop/asset/d/p4_65389.gif"></a></div></div></aside><div id="gonav" class="title"><hgroup class=""><p class="l"><span class="cur js_click_v0"  clickkey="js_infoshow"><a href="javascript:void()">宝贝详情</a></span><span class="pl" clickkey="appraise"><a href="javascript:void()">90天评价 <b>(<?php echo isset($buy_num)?$buy_num:"";?>)</b></a><i></i></span><span class="buyhou js_click_v0"  clickkey="xuzhi"><a href="javascript:void()">售后保障</a></span><span class="proxun js_click_v0"  clickkey="pro_zixun"><a href="javascript:void()">商品咨询</a></span><em class="phone_buy"><a href="#"><i></i>手机购买</a><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("block/qrcode?txt=".$this->config["cururl"]."");?>" width="132" height="132"></em></p><p class="r"><b class="js_float_price">¥<i>29.90</i></b><span class="s2"><a href="#" ptype="cart">加入购物车</a></span></p></hgroup></div><div class="productdetail"><div class="js_infotab clear js_infoshow"><div class="bidu clear"><h2><span><em>●</em>优购网验货</span></h2>该商品已通过优购网品控管理团队验证，可放心购买
        <sup></sup><p class="xian clear"></p></div><h2 class="product_tit product_ps" id="mao_spcs"><span><em>●</em>商品参数</span></h2><ul class="list12 clear"><?php if(isset($brand) && $brand){?><li class="pro_num"><b>品牌</b>：<a href="#" target="_blank"><?php echo isset($brand)?$brand:"";?></a></li><?php }?><?php if(isset($weight) && $weight){?><li class="pro_num"><b>商品毛重</b>：<?php echo isset($weight)?$weight:"";?></li><?php }?><?php if(isset($unit) && $unit){?><li class="pro_num"><b>单位</b>：<?php echo isset($unit)?$unit:"";?></li><?php }?><?php if(($attribute)){?><?php foreach($attribute as $key =>$item){?><li class="pro_num"><b><?php echo isset($item['name'])?$item['name']:"";?></b>：<?php echo isset($item['attribute_value'])?$item['attribute_value']:"";?></li><?php }?><?php }?></ul><h2 class="product_tit product_fhwl" id="mao_fhwl"><span><em>●</em>发货物流</span></h2><ul class="list12 clear"><li class="wuliu"><b>发货：</b>支付成功后，24小时内发货 </li><li class="wuliu"><b>物流：</b>本店全部商品都默认发申通快递，暂不支持其他快递</li></ul><div class="youpinhuiblock hidden" style="display: block;"><div class="youpinhuiblockhd"><a class="js_utm_params" href="#" utm_content="detailpage_banner"><img skin="skin" src="http://i4.tuanimg.com/item/asset/d/products/youpinhui_1.gif"></a></div><ul class="clear"><?php foreach(Api::run('getCommendList',4,['#com_id#',3,'#shop_id#',$seller_id]) as $key =>$item){?><li><a target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/products/id/".$item['id']."");?>"><img skin="skin" src="<?php echo IUrl::getHost().IUrl::creatUrl("/pic/thumb/img/".$item['img']."/w/180/h/180");?>"></a><h6><a target="_blank" href="<?php echo IUrl::getHost().IUrl::creatUrl("/mall/products/id/".$item['id']."");?>" title="<?php echo isset($item['name'])?$item['name']:"";?>"><?php echo isset($item['name'])?$item['name']:"";?></a></h6><p><i>￥</i><?php echo isset($item['sell_price'])?$item['sell_price']:"";?><span class="sold"><span>已售<em><?php echo isset($item['sale'])?$item['sale']:"";?></em>件</span></span></p></li><?php }?></ul></div><h2 class="product_tit product_show" id="mao_show"><span><em>●</em>商品展示</span></h2><div class="info clear js_detail_div"><?php echo isset($content)?$content:"";?></div><div class="shouhoubaoz red_tit" id="mao_shbz"><span>售后保障</span></div><div class="shouhoubao"><ul><li class=" sige "><span class="et"></span><p>8天无理由退货</p><b class="bb"></b></li><li class="sige"><span class="buk"></span><p>免费赠送<br>退货运费补贴卡</p><b class="bb"></b></li><li class="  sige  "><span class="th"></span><p class="tuihuop">商家原因退货，<br>运费由商家承担</p><b class="bb"></b></li><li class=" sige "><span class="twoh"></span><p>承诺24小时发货</p><b></b></li></ul></div><div class="js_appraise_bottom red_tit" id="mao_appraise"><span>90天评价 <em></em></span></div></div><div class="appraise js_infotab clear" id="appraise_posi"><div class="newbox clear"><div class="s1"><span class="l good"><i class="big">96.9</i><i>%</i><br><em>好评率</em></span></div><div class="s2"><span class="text">大家认为</span><div><em>描述相符</em><p class="starBox"><i class="active9"></i></p><em>4.9分</em></div><div><em>服务态度</em><p class="starBox"><i class="active9"></i></p><em>4.9分</em></div><div><em>发货速度</em><p class="starBox"><i class="active9"></i></p><em>4.9分</em></div></div><div class="s3"><p>您可对已购商品进行评价</p><p><a class="btn_p js_utm_params" target="_blank" href="#" utm_content="p_detail_pinglun">发评论拿积分</a></p></div></div><div class="tit"><span class="r"><input class="cfcommentshow js_click_v0" id="comment_cent" checked="checked" type="checkbox" data-flag="4">有内容的评价</span><span class="l">最新评价</span></div></div><div class="xuzhi clear js_infotab" style="display: none"><ul><li><span class="after_sale"><i></i></span><label>无忧售后</label><p>特卖商城全部商品，均由<?php echo isset($this->config['name'])?$this->config['name']:"";?>客服协助处理售后问题，实现售后无忧。</p><p>少发了？发错了？不合适？ 不喜欢？都可以直接在“我的订单”里申请退货/退款，<a href="#" target="_blank">查看如何申请&gt;&gt;</a></p></li><li class="process"><p><span class="tit">退货流程</span><span class="single single1"><em>1.</em>进入我的订单</span><span class="double double2"><em>2.</em>点击 “退货/退款”</span><span class="single single3"><em>3.</em>审核通过，同意退货</span><span class="double double4"><em>4.</em>邮寄商品给卖家<br>填写退货运单号</span><span class="single single5"><em>5.</em>退款完成</span></p></li><li><span class="picc"><i></i></span><label>中国人保承保</label><p>该商品由<?php echo isset($this->config['name'])?$this->config['name']:"";?>与中国人保（PICC）联合承保，请放心购买</p></li><li><span class="et"><i></i></span><label>8天无理由退货</label><p>商品签收8天内，在保证商品完好的前提下，可无理由退货，<a target="_blank" href="#">查看详情&gt;&gt;</a></p></li><li><span class="buk"><i></i></span><label>退货补贴优惠券</label><p>特卖商城下单，账号绑定手机后可获得退货补贴服务。若订单中的商品<b class="red">申请8天无理由退货</b>，在<b class="red">退货成功</b>时您可以获得一张8-10元优惠券作为退货补贴（每个订单仅补贴一次优惠劵），优惠券有效期60天。<a target="_blank" href="#">退货补贴说明&gt;&gt;</a></p></li><li><span class="th"><i></i></span><label>商家原因退货，退货运费由商家承担</label><p>该卖家已缴纳保证金。</p><p>在确认收货15天内，如有商品质量问题、描述不符或未收到货等，您有权申请退款或退货，<b>退货运费由卖家承担。</b></p></li><li><span class="twoh"><i></i></span><label>商家承诺24小时发货，未按时发货的订单可获得补偿</label><p>若此商品卖家未在24小时内发货，您无需投诉，<?php echo isset($this->config['name'])?$this->config['name']:"";?>将直接以优惠券的形式给您补偿。<br>若发货后48小时物流官网没有更新任何物流信息记录，您可以投诉卖家。经<?php echo isset($this->config['name'])?$this->config['name']:"";?>核实买家投诉成立的，<?php echo isset($this->config['name'])?$this->config['name']:"";?>将以优惠券的形式给您补偿。<a target="_blank" href="#">如何投诉&gt;&gt;</a></p></li></ul></div><div class="pro_zixun clear js_infotab" style="display:none"><h2>购买前如有问题，请向客服进行咨询：</h2><ul class="list12 kefu_zixun clear"><li><label>售前：</label><span class="im_icon" data-seller-jid="687086#0" data-clickkey="p_detail_seller_service"><a class="im_btn js_im_click" style="width: 80px;"><i></i><span>在线咨询</span></a></span></li><li><label>售后：</label><span class="im_shouhou"><a class="im_btn js_im_click" style="width: 80px;"><i></i><span>在线咨询</span></a></span></li></ul><ol class="list12 wenda clear"><li><p class="question"><label>Q</label><span>在<?php echo isset($this->config['name'])?$this->config['name']:"";?>购买有哪些保障？</span></p><p class="answer"><label>A</label><span>请放心，<?php echo isset($this->config['name'])?$this->config['name']:"";?>上有四大保障：“八天无理由退货”、“免费赠送退货运费补贴卡”、“商家原因退货，运费由商家承担”、“承诺24小时发货”。</span></p></li><li><p class="question"><label>Q</label><span>若有赠品如何赠送？</span></p><p class="answer"><label>A</label><span>若您买的商品有赠品，请看商品详情业中对赠品的相关介绍，商家会根据自身的情况，对赠送条件、赠送方式及赠送款式有明确的说明。若详情页没有关于赠品的说明，可能就真的没有哦~</span></p></li><li><p class="question"><label>Q</label><span>订单什么时候发货？如何查看物流？</span></p><p class="answer"><label>A</label><span>正常情况下，下单完成系统就会通知入驻商家开始发货，在详情页中能看到商家具体的发货服务类型。发货后，您可以在“个人中心”中查看发货状态并且可以点击“查看物流”了解物流跟踪。由于快递公司无法实时更新信息， 因此您看到的物流情况可能会有延迟，请您耐心等待。</span></p></li><li><p class="question"><label>Q</label><span>我可以指定快递吗？</span></p><p class="answer"><label>A</label><span>非常抱歉，您不可以指定快递公司。为了提高您的购买体验和收货效率，我们已经限定了特卖商家只能发效率较高、服务较好的快递哦。不同商家合作快递可能不一样哦，购买前请参考商品详情页中的说明~</span></p></li><li><p class="question"><label>Q</label><span>下单后可以修改订单吗？</span></p><p class="answer"><label>A</label><span>A：除限时抢和秒杀类活动外，订单状态为“待发货”且在下单30分钟内可修改收货信息，若超过30分钟或者商家已操作发货则不能修改，您可以申请“退款”重新拍下自己喜欢的商品哦~</span></p></li><li><a href="#" target="_blank">查看详细退款政策&gt;&gt;</a></li></ol></div><div class="recommendtitle red_tit"><span>看了此商品的人还看了</span></div><div class="recommend clear"></div><div class="interestbrandtitle red_tit"><span>您可能感兴趣的品牌</span></div><div class="interestbrand clear"></div></div><ul class="m_nav js_navi_ul"><li class="blank"></li><li data-id="mao_examine" class="js_navi_li"><a href="#" class="js_click_v0 cur" clickkey="p_detail_yanhuo"><em>●</em><?php echo isset($this->config['name'])?$this->config['name']:"";?>验货</a></li><li data-id="mao_spcs" class="js_navi_li"><a href="#" class="js_click_v0" clickkey="p_detail_product_detail"><em>●</em>商品参数</a></li><li data-id="mao_fhwl" class="js_navi_li"><a href="#" class="js_click_v0" clickkey="p_detail_delivery_logistics"><em>●</em>发货物流</a></li><li data-id="mao_cm" class="js_navi_li"><a href="#" class="js_click_v0" clickkey="p_detail_product_size"><em>●</em>商品尺码</a></li><li data-id="mao_show" class="js_navi_li"><a href="#" class="js_click_v0" clickkey="p_detail_product_images"><em>●</em>商品展示</a></li><li data-id="mao_shbz" class="js_navi_li"><a href="#" class="js_click_v0" clickkey="p_detail_shouhoubaozhang"><em>●</em>售后保障</a></li><li data-id="mao_appraise" class="js_navi_li"><a href="#" class="js_click_v0" clickkey="p_detail_pinlun"><em>●</em>90天评价</a></li></ul></article><script type="text/javascript">$(function(){
window.ug = {
elem:{},
data:{
isBuy:false,
product_id:0,
price:{
"group_price":"<?php echo isset($group_price)?$group_price:"";?>",
"minSellPrice":"<?php echo isset($minSellPrice)?$minSellPrice:"";?>",
"maxSellPrice":"<?php echo isset($maxSellPrice)?$maxSellPrice:"";?>",
"sell_price":"<?php echo isset($sell_price)?$sell_price:"";?>",
"minMarketPrice":"<?php echo isset($minMarketPrice)?$minMarketPrice:"";?>",
"maxMarketPrice":"<?php echo isset($maxMarketPrice)?$maxMarketPrice:"";?>",
"market_price":"<?php echo isset($market_price)?$market_price:"";?>"
},
store_nums:<?php echo isset($store_nums)?$store_nums:"";?>,
buy_nums:1
},
//初始化
init:function(){
//提取元素
this.elem.elem_storeNums = $('#elem_storeNums');//库存
this.elem.elem_price = $("#elem_price");//当前价格
this.elem.elem_price_old = $("#elem_price_old"); //市场价
this.elem.elem_buy_nums = $("input[name='elem_buy_nums']")//购买数量
this.priceHtml();
//绑定价格按钮事件
var _uo = this;
$(".specList li").on("click",function(){
_uo.SpecSelected(this);
})
$(".decrease").click(function(){
_uo.buyNumsBtn(this,-1);
});
$(".increase").click(function(){
_uo.buyNumsBtn(this,1);
});
var tabBox = $(".productdetail");
$("#gonav p>span").click(function(){
var $this = $(this);
var clickKey = $this.attr("clickkey");
if(clickKey){
$this.siblings().removeClass("cur");
$this.addClass("cur");
tabBox.find(".js_infotab").hide();
tabBox.find(".js_infotab." + clickKey).show();
}
})
},
//生成商品价格
priceHtml:function(){
var data = this.data.price;
if (data.group_price){//会员价：group_price
   
}else{
this.elem.elem_price.html((data.minSellPrice != data.maxSellPrice)?data.minSellPrice +" - "+ data.maxSellPrice:data.sell_price);
this.elem.elem_price_old.html((data.minMarketPrice != data.maxMarketPrice)?data.minMarketPrice +" - "+ data.maxMarketPrice:data.market_price);
}
},
checkSpecSelected:function(){
return ($('[name="specCols"]').length === $('[name="specCols"] .cur').length)?true:false;
},
checkStoreNums:function(){
var storeNums = parseInt($.trim(this.data.store_nums));
(storeNums >0)?this.BuyIsOpen(true):this.BuyIsOpen(false);
},
BuyIsOpen:function(b){
this.data.isBuy = b;
},
buyNumsBtn:function(e,t){
switch(t){
case 1:{
this.data.buy_nums++;
if(this.data.buy_nums>this.data.store_nums){
this.data.buy_nums = this.data.store_nums;
}
}
break;
case -1:{
this.data.buy_nums--;
if(this.data.buy_nums<2){
this.data.buy_nums = 1;
$(e).addClass("no-drop")
}
}
break;
case 0:{
this.data.buy_nums = 1;
$(".decrease").addClass("no-drop")
}
break;
};
(this.data.buy_nums>1) && $(".decrease").removeClass("no-drop");
this.elem.elem_buy_nums.val(this.data.buy_nums);
},
SpecSelected:function(e){
//规格选择
var $this = $(e),
specObj = $.parseJSON($this.attr("data-value"));
$this.siblings().removeClass("cur");
$this.addClass("cur");
//检查规格是否选择符合标准
if(this.checkSpecSelected()){
var specArray = [];
$('[name="specCols"]').each(function(){
specArray.push($(this).find('li.cur').attr('data-value'));
});
var specJSON = '['+specArray.join(",")+']';
var _uo = this;
//获取货品数据并进行渲染
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/mall/getProduct");?>',{"goods_id":"<?php echo isset($id)?$id:"";?>","specJSON":specJSON,"random":Math.random},function(json){
if(json.flag == 'success'){
_uo.data.price = json.data;
_uo.data.store_nums = json.data.store_nums;
_uo.data.product_id = json.data.id;
//货品数据渲染
//$('#data_goodsNo').text(json.data.products_no);
//$('#data_marketPrice').text("￥"+json.data.market_price);
//$('#data_weight').text(json.data.weight);
_uo.displayUI();
}else{
_uo.BuyIsOpen(false)
}
})
};
//alert(this.data.isBuy);
return false;
},
displayUI:function(){
this.priceHtml();
this.elem.elem_storeNums.text(this.data.store_nums);
this.buyNumsBtn(null,0);
//检测库存
this.checkStoreNums();
},
addCart:function(e){
if(!this.checkSpecSelected())return this.tips('请先选择商品的规格');
var buyNums   = parseInt($.trim(this.data.buy_nums)),
price     = parseFloat($.trim(this.elem.elem_price.text())),
type      = parseInt(this.data.product_id)? 'product' : 'goods',
goods_id  = (type == 'product') ? this.data.product_id : <?php echo isset($id)?$id:"";?>;
var _uo = this,_$this = $(e);
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/panel/joinCart");?>',{"goods_id":goods_id,"type":type,"goods_num":buyNums,"random":Math.random},function(content){
if(content.isError == false){
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/panel/showCart");?>',{"random":Math.random},function(json){
//询问框
var html = '购物车有'+ json.count +' 件商品，合计：' + json.sum +' 元'+
''+
'';
layer.confirm(html,{icon: 3, title:'添加成功',btn: ['查看购物车','继续购物']}, function(index){
//do something
layer.close(index);
window.document.location.href = "<?php echo IUrl::getHost().IUrl::creatUrl("/panel/cart");?>";
});
});
_$this.attr('disabled','disabled').val("添加购物车成功");
}else{
_uo.tips(content.message);
}
})
},
submitBuy:function(){
if(!this.checkSpecSelected())return this.tips('请先选择商品的规格');
var buyNums   = parseInt($.trim(this.data.buy_nums)),
price     = parseFloat($.trim(this.elem.elem_price.text())),
type      = parseInt(this.data.product_id)? 'product' : 'goods',
goods_id  = (type == 'product') ? this.data.product_id : <?php echo isset($id)?$id:"";?>;
<?php if($promo){?>//有促销活动（团购，抢购）
var url = '<?php echo IUrl::getHost().IUrl::creatUrl("/panel/cart2/id/@id@/num/@buyNums@/type/@type@/promo/".$promo."/active_id/".$active_id."");?>';
url = url.replace('@id@',goods_id).replace('@buyNums@',buyNums).replace('@type@',type);
<?php }else{?>//普通购买
var url = '<?php echo IUrl::getHost().IUrl::creatUrl("/panel/cart2/id/@id@/num/@buyNums@/type/@type@");?>';
url = url.replace('@id@',goods_id).replace('@buyNums@',buyNums).replace('@type@',type);
<?php }?>//页面跳转
window.location.href = url;
},
tips:function(msg){layer.msg(msg);return false}
};
window.ug.init();
})
</script><?php require(ITag::createRuntime("incfile/footer"));?></body></html>