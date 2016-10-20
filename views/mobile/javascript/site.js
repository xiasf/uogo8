//关闭product购物车弹出的div
function closeCartDiv()
{
	$('#product_myCart').hide('slow');
	$('.submit_join').removeAttr('disabled','');
}

//商品移除购物车
function removeCart(urlVal,goods_id,type)
{
	var goods_id = parseInt(goods_id);

	$.getJSON(urlVal,{goods_id:goods_id,type:type},function(content){
		if(content.isError == false)
		{
			$('[name="mycart_count"]').html(content.data['count']);
			$('[name="mycart_sum"]').html(content.data['sum']);
		}
		else
		{
			alert(content.message);
		}
	});
}

//添加收藏夹
function favorite_add_ajax(urlVal,goods_id,obj)
{
	$.getJSON(urlVal,{goods_id:goods_id,nocache:((new Date()).valueOf())},function(content){
		if(content.isError == false)
		{
			obj.value = content.message;
		}
		else
		{
			alert(content.message);
		}
	});
}

//寄存购物车[ajax]
function deposit_ajax(urlVal)
{
	$.getJSON(urlVal,{is_ajax:'1'},function(content){
		if(content.isError == false)
		{
			alert(content.message);
		}
		else
		{
			alert(content.message);
		}
	});
}

//购物车展示
function showCart(urlVal)
{
	$.getJSON(urlVal,{sign:Math.random()},function(content)
	{
		var cartTemplate = template.render('cartTemplete',{'goodsData':content.data,'goodsCount':content.count,'goodsSum':content.sum});
		$('#div_mycart').html(cartTemplate);
		$('#div_mycart').show();
	});
}

//自动完成
function autoComplete(ajaxUrl,linkUrl,minLimit)
{
	var minLimit = minLimit ? parseInt(minLimit) : 2;
	var maxLimit = 10;
	var keywords = $.trim($('input:text[name="word"]').val());

	//输入的字数通过规定字数
	if(keywords.length >= minLimit && keywords.length <= maxLimit)
	{
		$.getJSON(ajaxUrl,{word:keywords},function(content){

			//清空自动完成数据
			$('.auto_list').empty();

			if(content.isError == false)
			{
				for(var i=0; i < content.data.length; i++)
				{
					var searchUrl = linkUrl.replace('@word@',content.data[i].word);
					$('.auto_list').append('<li onclick="event_link(\''+searchUrl+'\')" style="cursor:pointer"><a href="javascript:void(0)">'+content.data[i].word+'</a>约'+content.data[i].goods_nums+'个结果</li>');
					//鼠标经过效果
					$('.auto_list li').bind("mouseover",
						function()
						{
							$(this).addClass('hover');
						}
					);
					$('.auto_list li').bind("mouseout",
						function()
						{
							$(this).removeClass('hover');
						}
					);
				}
				$('.auto_list').show();
			}
			else
			{
				$('.auto_list').hide();
			}
		});
	}
	else
	{
		$('.auto_list').hide();
	}
}

//输入框
function checkInput(para,textVal)
{
	var inputObj = (typeof(para) == 'object') ? para : $('input:text[name="'+para+'"]');

	if(inputObj.val() == '')
	{
		inputObj.val(textVal);
	}
	else if(inputObj.val() == textVal)
	{
		inputObj.val('');
	}
}

//dom载入成功后开始操作
jQuery(function(){

	$(".top_rig").click(function(){
		$('body,html').animate({scrollTop:0},500); 
		return false; 
	}); 

});


//获取焦点时边框颜色变换
function focusInput(focusClass, normalClass){
	var elements = document.getElementsByClassName("txt");
    for (var i=0; i < elements.length; i++) {
        if (elements[i].type != "button" && elements[i].type != "submit" && elements[i].type != "reset") {
			elements[i].onfocus = function() { this.className = focusClass; };
			elements[i].onblur = function() { this.className = normalClass||''; };
        }
    }
}

//teb切换效果
function cutover(id,etype){
	var tabContainer = $("#"+id).find(".tabContainer");
	var panelContainer = $("#"+id).find(".panelContainer");	
	tabContainer.find("dl>dt").each(function(i,e){
		if (etype=="1"){
			$(e).hover(function(){
				tabContainer.find("dl>dt").removeClass("on");
				$(e).addClass("on");
				panelContainer.children("div").hide();
				panelContainer.children("div").eq(i).show();
			});
		}else{
			$(e).click(function(){
				tabContainer.find("dl>dt").removeClass("on");
				$(e).addClass("on");
				panelContainer.children("div").hide();
				panelContainer.children("div").eq(i).show();
			});
				
		}
	});
};

//公用幻灯片
function roll_pic(flash_box,time){
	var sWidth = $("#sxSliderIndexAd"+flash_box).width(); 
	var len = $("#sxSliderIndexAd"+flash_box+" ul li").length; 
	var index = 0;
	var picTimer;
	
	var btn = "<div class='btnBg'></div><div class='btn'>";
	for(var i=0; i < len; i++) {
		btn += "<span></span>";
	}
	btn += "</div><div class='preNext pre'></div><div class='preNext next'></div>";
	$("#sxSliderIndexAd"+flash_box).append(btn);
	$("#sxSliderIndexAd"+flash_box+" .btnBg").css("opacity",0.5);

	$("#sxSliderIndexAd"+flash_box+" .btn span").css("opacity",0.4).mouseenter(function() {
		index = $("#sxSliderIndexAd"+flash_box+" .btn span").index(this);
		showPics(index);
	}).eq(0).trigger("mouseenter");


	$("#sxSliderIndexAd"+flash_box+" .preNext").css("opacity",0.2).hover(function() {
		$(this).stop(true,false).animate({"opacity":"0.5"},300);
	},function() {
		$(this).stop(true,false).animate({"opacity":"0.2"},300);
	});
	
	$("#sxSliderIndexAd"+flash_box+" .pre").click(function() {
		index -= 1;
		if(index == -1) {index = len - 1;}
		showPics(index);
	});
	
	$("#sxSliderIndexAd"+flash_box+" .next").click(function() {
		index += 1;
		if(index == len) {index = 0;}
		showPics(index);
	});
	
	
	$("#sxSliderIndexAd"+flash_box+" ul").css("width",sWidth * (len));
	$("#sxSliderIndexAd"+flash_box).hover(function() {
		clearInterval(picTimer);
	},function() {
		picTimer = setInterval(function() {
			showPics(index);
			index++;
			if(index == len) {index = 0;}
		},4000); 
	}).trigger("mouseleave");
	
	
	function showPics(index) {
		var nowLeft = -index*sWidth; 
		$("#sxSliderIndexAd"+flash_box+" ul").stop(true,false).animate({"left":nowLeft},300);
		$("#sxSliderIndexAd"+flash_box+" .btn span").stop(true,false).animate({"opacity":"0.4"},300).eq(index).stop(true,false).animate({"opacity":"1"},300);
	}

};


function menulist1() {
	$("#sort_div").toggleClass("on");
}


//加载..
$(document).ready(function(e) {
	
	$(".sort_lists ul li").each(function(index){
		if((index+1)%3==0)
		$(this).css("border-right","none");
	});
	
	
});