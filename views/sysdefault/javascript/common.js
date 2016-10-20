//清理缓存
function clearCache()
{
loadding('请稍候，系统正在清理缓存文件...');
jQuery.get('/system/clearCache',function(content)
{
unloadding();
var content = $.trim(content);
if(content == 1)
art.dialog.tips('清理成功！', 1.5);
else
art.dialog.tips('清理失败！', 1.5);
});
}

//全选
function selectAll(nameVal,obj)
{
	//获取复选框的form对象
	var formObj = $("form:has(:checkbox[name='"+nameVal+"'])");

	//根据form缓存数据判断批量全选方式
	if(formObj.data('selectType')=='' || formObj.data('selectType')==undefined)
	{
		$("input:checkbox[name='"+nameVal+"']:not(:checked)").attr('checked',true);
		formObj.data('selectType','all');
	}
	else
	{
		$("input:checkbox[name='"+nameVal+"']").attr('checked',false);
		formObj.data('selectType','');
	}
	if(obj){var objced = $(obj).attr('checked');$(obj).attr('checked') = !objced; }
}
/**
 * @brief 获取控件元素值的数组形式
 * @param string nameVal 控件元素的name值
 * @param string sort    控件元素的类型值:checkbox,radio,text,textarea,select
 * @return array
 */
function getArray(nameVal,sort)
{
	//要ajax的json数据
	var jsonData = new Array;

	switch(sort)
	{
		case "checkbox":
		$('input:checkbox[name="'+nameVal+'"]:checked').each(
			function(i)
			{
				jsonData[i] = $(this).val();
			}
		);
		break;
	}
	return jsonData;
}
window.loadding = function(message){var message = message ? message : '正在执行，请稍后...';art.dialog({"id":"loadding","lock":true,"fixed":true,"drag":false}).content(message);}
window.unloadding = function(){art.dialog({"id":"loadding"}).close();}
window.tips = function(mess){art.dialog.tips(mess);}
window.realAlert = window.alert;
//window.alert = function(mess){art.dialog.alert(mess);}
window.realConfirm = window.confirm;
window.confirm = function(mess,bnYes,bnNo)
{
	if(bnYes == undefined && bnNo == undefined)
	{
		return eval("window.realConfirm(mess)");
	}
	else
	{
		art.dialog.confirm(
			mess,
			function(){eval(bnYes)},
			function(){eval(bnNo)}
		);
	}
}
/**
 * @brief 删除操作
 * @param object conf
	   msg :提示信息;
	   form:要提交的表单名称;
	   link:要跳转的链接地址;
 */
function delModel(conf)
{
	var ok = null;            //执行操作
	var msg   = '确定要删除么？';//提示信息

	if(conf)
	{
		if(conf.form)
			var ok = 'formSubmit("'+conf.form+'")';
		else if(conf.link)
			var ok = 'window.location.href="'+conf.link+'"';

		if(conf.msg)
			var msg   = conf.msg;
	}
	if(ok==null && document.forms.length >= 1)
		var ok = 'document.forms[0].submit();';

	if(ok!=null)
		window.confirm(msg,ok);
	else
		alert('删除操作缺少参数');
}

//根据表单的name值提交
function formSubmit(formName)
{
	$('form[name="'+formName+'"]').submit();
}

//根据checkbox的name值检测checkbox是否选中
function checkboxCheck(boxName,errMsg)
{
	if($('input[name="'+boxName+'"]:checked').length < 1)
	{
		alert(errMsg);
		return false;
	}
	return true;
}

//倒计时
var countdown=function()
{
	var _self=this;
	this.handle={};
	this.parent={'second':'minute','minute':'hour','hour':""};
	this.add=function(id)
	{
		_self.handle.id=setInterval(function(){_self.work(id,'second');},1000);
	};
	this.work=function(id,type)
	{
		if(type=="")
		{
			return false;
		}

		var e = document.getElementById("cd_"+type+"_"+id);
		var value=parseInt(e.innerHTML);
		if( value == 0 && _self.work( id,_self.parent[type] )==false )
		{
			clearInterval(_self.handle.id);
			return false;
		}
		else
		{
			e.innerHTML = (value==0?59:(value-1));
			return true;
		}
	};
};

//切换验证码
function changeCaptcha(urlVal)
{
	var radom = Math.random();
	if( urlVal.indexOf("?") == -1 )
	{
		urlVal = urlVal+'/'+radom;
	}
	else
	{
		urlVal = urlVal + '&random'+radom;
	}
	$('#captchaImg').attr('src',urlVal);
}

/*实现事件页面的连接*/
function event_link(url)
{
	window.location.href = url;
}

//延迟执行
function lateCall(t,func)
{
	var _self = this;
	this.handle = null;
	this.func = func;
	this.t=t;

	this.execute = function()
	{
		_self.func();
		_self.stop();
	}

	this.stop=function()
	{
		clearInterval(_self.handle);
	}

	this.start=function()
	{
		_self.handle = setInterval(_self.execute,_self.t);
	}
}

/**
 * 进行商品筛选
 * @param url string 执行的URL
 * @param callback function 筛选成功后执行的回调函数
 */
function searchGoods(url,callback)
{
	var step = 0;
	art.dialog.open(url,
	{
		"id":"searchGoods",
		"title":"商品筛选",
		"okVal":"执行",
		"button":
		[{
			"name":"后退",
			"callback":function(iframeWin,topWin)
			{
				if(step > 0)
				{
					iframeWin.window.history.go(-1);
					this.size(1,1);
					step--;
				}
				return false;
			}
		}],
		"ok":function(iframeWin,topWin)
		{
			if(step == 0)
			{
				iframeWin.document.forms[0].submit();
				step++;
				return false;
			}
			else if(step == 1)
			{
				var goodsList = $(iframeWin.document).find('input[name="id[]"]:checked');

				//添加选中的商品
				if(goodsList.length == 0)
				{
					alert('请选择要添加的商品');
					return false;
				}
				//执行处理回调
				callback(goodsList);
				return true;
			}
		}
	});
}

/**
 * @brief 商品分类弹出框
 * @param string urlValue 提交地址
 * @param string categoryName 商品分类name值
 */
function selectGoodsCategory(urlValue,categoryName)
{
	//根据表单里面的name值生成分类ID数据
	var result = [];
	$('[name="'+categoryName+'"]').each(function()
	{
		result.push(this.value);
	});
	art.dialog.data('categoryValue',result);

	art.dialog.open(urlValue,{
		title:'选择商品分类',
		okVal:'确定',
		ok:function(iframeWin, topWin)
		{
			var categoryObject = [];
			var categoryWhole  = art.dialog.data('categoryWhole');
			var categoryValue  = art.dialog.data('categoryValue');
			for(var item in categoryWhole)
			{
				item = categoryWhole[item];
				if(jQuery.inArray(item['id'],categoryValue) != -1)
				{
					categoryObject.push(item);
				}
			}
			createGoodsCategory(categoryObject);
		},
		cancel:function()
		{
			return true;
		}
	})
}

//生成商品分类
function createGoodsCategory(categoryObj)
{
	if(!categoryObj)
	{
		return;
	}

	$('#__categoryBox').empty();
	for(var item in categoryObj)
	{
		item = categoryObj[item];
		var goodsCategoryHtml = template.render('categoryButtonTemplate',{'templateData':item});
		$('#__categoryBox').append(goodsCategoryHtml);
	}
}
/**
 * @brief 商品品牌弹出框
 * @param string urlValue 提交地址
 * @param string categoryName 商品分类name值
 */
function selectGoodsBrand(urlValue,brandName)
{
	//根据表单里面的name值生成分类ID数据
	var result = [];
	$('[name="'+brandName+'"]').each(function()
	{
		result.push(this.value);
	});
	art.dialog.data('brandValue',result);

	art.dialog.open(urlValue,{
		title:'选择商品分类',
		okVal:'确定',
		ok:function(iframeWin, topWin)
		{
			var categoryObject = [];
			var categoryWhole  = art.dialog.data('brandWhole');
			var categoryValue  = art.dialog.data('brandValue');
			for(var item in categoryWhole)
			{
				item = categoryWhole[item];
				if(jQuery.inArray(item['id'],categoryValue) != -1)
				{
					categoryObject.push(item);
				}
			}
			createGoodsBrand(categoryObject);
		},
		cancel:function()
		{
			return true;
		}
	})
}
//生成商品品牌
function createGoodsBrand(categoryObj)
{
	if(!categoryObj)
	{
		return;
	}

	$('#__brandBox').empty();
	for(var item in categoryObj)
	{
		item = categoryObj[item];
		var goodsCategoryHtml = template.render('brandButtonTemplate',{'templateData':item});
		$('#__brandBox').append(goodsCategoryHtml);
	}
}

jQuery(function(){
	//高度自适应
	initLayout();
	$(window).resize(function()
	{
		initLayout();
	});
	function initLayout()
	{
		var h1 = document.documentElement.clientHeight - $("#header").outerHeight(true) - $("#info_bar").height();
		var h2 = h1 - $(".headbar").height() - $(".pages_bar").height() - 30;
		$('#admin_left').height(h1);
		$('#admin_right .content').height(h2);
	}
	//一级菜单切换
	$("#menu ul li:first-child").addClass("first");
	$("#menu ul li:last-child").addClass("last");
	$("[name='menu']>li").click(function(){
		$(this).siblings().removeClass("selected");
        $(this).addClass("selected");
	});
	//二级菜单展示效果
	$("ul.submenu>li>span").toggle(
		function(){
			$(this).next().css("display","none");
			$(this).addClass("selected");
		},
		function(){
			$(this).next().css("display","");
			$(this).removeClass("selected");
		}
	);
	//文字滚动显示
	$("#tips a:not(:first)").css("display","none");
	var tips_l=$("#tips a:last");
	var tips_f=$("#tips a:first");
	setInterval(function()
	{
		if($("#tips").children().length	!= 1){
			if(tips_l.is(":visible")){
				tips_f.fadeIn(500);
				tips_l.hide()
			}else{
				$("#tips a:visible").addClass("now");
				$("#tips a.now").next().fadeIn(500);
				$("#tips a.now").hide().removeClass("now");
			}
		}
	},3000);
});

function initMenu(data,current,url)
{
	for(i in data)
	{
		if(data[i]['current'])
		{
			$('#menu ul').append('<li class="selected"><a href="#">'+data[i]['title']+'</a></li>');
            var list = data[i]['list'];
            var item = '';
            for(j in list)
            {
                item = '<li><span>'+j+'</span><ul name="menu">';
                for(k in list[j])
                {
                    if( list[j][k].urlPathinfo == current ) item +='<li class="selected"><a href="'+k+'">'+list[j][k].name+'</a></li>';
                    else item +='<li><a href="'+k+'">'+list[j][k].name+'</a></li>';
                }
                $('.submenu').append(item+'</ul></li>');
            }
		}
		else
		{
			$('#menu ul').append('<li><a href="'+data[i]['link']+'" hidefocus = "true">'+data[i]['title']+'</a></li>');
		}
	}
}
