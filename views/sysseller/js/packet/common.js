function pagereload(){
	window.document.location.reload();
}
function pageback(){
	window.history.go(-1);	
}
//全选
window.selectAll = function (nameVal,tc)
{
	var input = jQuery('input:checkbox[name="'+nameVal+'"]');
	if(tc)input.attr('checked',true);else input.removeAttr('checked');
}
/**
 * @brief 获取控件元素值的数组形式
 * @param string nameVal 控件元素的name值
 * @param string sort    控件元素的类型值:checkbox,radio,text,textarea,select
 * @return array
 */
window.getArray = function(nameVal,sort)
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
window.loadding = function(message){var message = message ? message : '正在执行，请稍后...';top.layer.msg(message);}
//window.unloadding = function(){art.dialog({"id":"loadding"}).close();}
window.tips = function(mess){top.layer.msg(mess,{icon:6});}
window.realAlert = window.alert;
window.alert = function(mess,yes){top.layer.alert(mess, {icon: 7},yes);}
window.realConfirm = window.confirm;
window.confirm = function(mess,bnYes,bnNo)
{
	if(bnYes == undefined && bnNo == undefined)
	{
		return eval("window.realConfirm(mess)");
	}
	else
	{
		top.layer.confirm(mess, {
			icon:3,
			btn: ['确定','取消'], //按钮
			shade: false //不显示遮罩
		}, function(index){
			top.layer.close(index);
			if($.isFunction(bnYes))bnYes();else eval(bnYes)
		}, function(){
			if($.isFunction(bnNo))bnNo();else eval(bnNo);
		});


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
	var msg   = '确定要删除吗？注意删除后不可恢复';//提示信息

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

/*//切换验证码
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
*/
/*实现事件页面的连接
function event_link(url)
{
	window.location.href = url;
}*/

//延迟执行
/*function lateCall(t,func)
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

*//**
 * 进行商品筛选
 * @param url string 执行的URL
 * @param callback function 筛选成功后执行的回调函数
 */
function searchGoods(url,callback)
{
	var step = 0;
	//iframe层
	top.layer.open({
		type: 2,
		title: '商品筛选',
		shadeClose: true,
		shade: 0.8,
		area: ['90%', '90%'],
		content: url, //iframe的url
		btn: ['执行','取消'], //按钮
		maxmin: true,
		yes: function(index, layerDom){
			//do somethin
			layerDom = $("iframe",layerDom)[0].contentWindow;
			if(step == 0)
			{
				layerDom.document.forms[0].submit();
				step++;
			}
			else if(step == 1)
			{
				var goodsList = $(layerDom.document).find('input[name="id[]"]:checked');
	
				//添加选中的商品
				if(goodsList.length == 0)
				{
					alert('请选择要添加的商品');
					return false;
				}
				//执行处理回调
				callback(goodsList);
				top.layer.close(index);
			}
			// //如果设定了yes回调，需进行手工关闭
		}
	});
}

/**
 * @brief 商品分类弹出框
 * @param string urlValue 提交地址
 * @param string categoryName 商品分类name值
 */
/*function selectGoodsCategory(urlValue,categoryName)
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
}*/

