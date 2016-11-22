/**
 * 订单对象
 * address:收货地址; delivery:配送方式; payment:支付方式;
 */
function orderFormClass()
{
	//是否为货到付款 0:否; 1:是;
	this.paytype = 0;

	//视图状态模式 默认：edit
	this.addressMod  = 'edit';
	this.deliveryMod = 'edit';
	this.paymentMod  = 'edit';
	this.messageMod  = 'exit';

	//当前正在使用的ID
	this.addressActiveId   = '';
	this.deliveryActiveId  = '';
	this.paymentActiveId   = '';
	this.messageActiveData = '';

	//视图切换按钮ID
	this.addressToggleButton  = 'addressToggleButton';
	this.deliveryToggleButton = 'deliveryToggleButton';
	this.paymentToggleButton  = 'paymentToggleButton';
	this.messageToggleButton  = 'messageToggleButton';

	this.orderAmount  = 0;//订单金额
	this.goodsSum     = 0;//商品金额
	this.deliveryPrice= 0;//运费金额
	this.paymentPrice = 0;//支付金额
	this.taxPrice     = 0;//税金
	this.protectPrice = 0;//保价
	this.ticketPrice  = 0;//代金券

	/**
	 * 算账
	 */
	this.doAccount = function()
	{
		//税金
		this.taxPrice = $('input:checkbox[name="taxes"]:checked').length > 0 ? $('input:checkbox[name="taxes"]:checked').val() : 0;
		//最终金额
		this.orderAmount = parseFloat(this.goodsSum) - parseFloat(this.ticketPrice) + parseFloat(this.deliveryPrice) + parseFloat(this.paymentPrice) + parseFloat(this.taxPrice) + parseFloat(this.protectPrice);

		this.orderAmount = this.orderAmount <=0 ? 0 : this.orderAmount.toFixed(2);

		//刷新DOM数据
		$('#final_sum').text(this.orderAmount);
		$('[name="ticket_value"]').text(this.ticketPrice);
		$('#delivery_fee_show').text(this.deliveryPrice);
		$('#protect_price_value').text(this.protectPrice);
		$('#payment_value').text(this.paymentPrice);
		$('#tax_fee').text(this.taxPrice);
	}

	/**
	 * 初始化JS省份联动菜单
	 */
	this.provinceMenuInit = function()
	{
		createAreaSelect('province',0,'');
		$('[name="city"]').empty();
		$('[name="area"]').empty();
	}

	/**
	 * address初始化
	 * @param defaultAddressId int 默认收货地址的主键索引
	 */
	this.addressInit = function(defaultAddressId)
	{
		if(defaultAddressId)
		{
			this.addressActiveId = defaultAddressId;
			$('input:radio[name="radio_address"][value="'+defaultAddressId+'"]').trigger('click');
			this.addressSave();
		}
		else
		{
			$('input:radio[name="radio_address"][value=""]').trigger('click');
		}
	}

	/**
	 * address选中时
	 * @param jsonData Object 数据对象
	 */
	this.addressSelected = function(jsonData)
	{
		//刷新修改form部分
		$('#address_form input[type="text"]').each(function()
		{
			this.value = jsonData[this.name];
		});

		//js城市联动
		createAreaSelect('province',0,jsonData.province);
		createAreaSelect('city',jsonData.province,jsonData.city);
		createAreaSelect('area',jsonData.city,jsonData.area);

		//刷新展示table部分
		var showTableHtml = template.render('addressShowTemplate',jsonData);
		$('#addressShowBox').html(showTableHtml);

		//清除之前校验效果
		$('.invalid-msg').remove();
		$('#address_form input:text').removeClass('invalid-text');
		$('#address_form select').removeClass('invalid-text');
	}

	/**
	 * 清空地址数据
	 */
	this.addressEmpty = function()
	{
		//刷新修改form部分
		$('#address_form input[type="text"]').each(function()
		{
			this.value = '';
		});

		//初始化js城市联动
		this.provinceMenuInit();

		//刷新展示table部分
		$('#addressShowBox').empty();
	}

	/**
	 * address模式切换
	 */
	this.addressModToggle = function(type)
	{
		//要切换的模式
		var toggleMod = this.addressMod == 'exit' ? 'edit' : 'exit';

		switch(toggleMod)
		{
			case "edit":
			{
				$('#'+this.addressToggleButton).text('[退出]');
			}
			break;

			case "exit":
			{
				//退出性还原收货地址数据
				if(type != 'save' && this.addressActiveId)
				{
					$('input:radio[name="radio_address"][value="'+this.addressActiveId+'"]').trigger('click');
				}
				$('#'+this.addressToggleButton).text('[修改]');
			}
			break;
		}

		//更新模式
		this.addressMod = toggleMod;

		//展示模式
		$('#address_show_box').toggle();

		//修改模式
		$('#address_often').toggle();
		$('#address_form').toggle();
		$('#address_save_button').toggle();
	}

	/**
	 * 进行数据的校验
	 */
	this.addressCheck = function()
	{
		$('#address_form').trigger('submit',function(){return false;});

		//数据格式不正确
		if($('#address_form .invalid-text').length > 0)
		{
			return false;
		}
		return true;
	}

	/**
	 * address保存
	 */
	this.addressSave = function()
	{
		if(this.addressCheck() == false)
		{
			return;
		}

		this.addressActiveId = $('input:radio[name="radio_address"]:checked').val();

		//当保存为临时收货地址时
		if(!this.addressActiveId)
		{
			//新添加的地址
			var jsonData =
			{
				"province_val":$('select[name="province"]>option:selected').text(),
				"city_val":$('select[name="city"]>option:selected').text(),
				"area_val":$('select[name="area"]>option:selected').text()
			};

			//刷新修改form部分
			$('#address_form input[type="text"]').each(function()
			{
				jsonData[this.name] = this.value;
			});

			var showTableHtml = template.render('addressShowTemplate',jsonData);
			$('#addressShowBox').html(showTableHtml);
		}

		this.addressModToggle('save');

		//获取配送数据并且开启配送方式
		var timeHandle = setInterval(function(){
			if($('[name="province"]').val())
			{
				get_delivery();
				clearInterval(timeHandle);
			}
		},500);

		$('#deliveryBox').show('slow');
	}

	/**
	 * delivery模式切换
	 */
	this.deliveryInit = function(defaultDeliveryId)
	{
		if(defaultDeliveryId > 0)
		{
			this.deliveryActiveId = defaultDeliveryId;
			var defaultDeliveryItem = $('input[type="radio"][name="delivery_id"][value="'+defaultDeliveryId+'"]');
			defaultDeliveryItem.trigger('click');

			//默认配送方式
			if($('#paymentBox:hidden').length == 1 && this.paytype == 0)
			{
				this.deliverySave();
			}
		}
	}

	/**
	 * delivery选中
	 * @param jsonData Object 配送方式数对象
	 */
	this.deliverySelected = function(jsonData)
	{
		//刷新table部分
		var deliveryShowHtml = template.render('deliveryShowTemplate',jsonData);
		$('#deliveryShowBox').html(deliveryShowHtml);
	}

	/**
	 * delivery模式切换
	 */
	this.deliveryModToggle = function()
	{
		//要切换的模式
		var toggleMod = this.deliveryMod == 'exit' ? 'edit' : 'exit';

		switch(toggleMod)
		{
			case "edit":
			{
				$('#'+this.deliveryToggleButton).text('[退出]');
			}
			break;

			case "exit":
			{
				if(!this.deliveryActiveId)
				{
					tips('请选择配送方式，并且进行保存');
					return;
				}
				else if($('input:radio[name="delivery_id"][value="'+this.deliveryActiveId+'"]:checked').length == 0)
				{
					//还原配送方式数据
					$('input:radio[name="delivery_id"][value="'+this.deliveryActiveId+'"]').trigger('click');
				}
				$('#'+this.deliveryToggleButton).text('[修改]');
			}
			break;
		}

		//更新模式
		this.deliveryMod = toggleMod;

		//展示模式
		$('#delivery_show_box').toggle();

		//修改模式
		$('#delivery_form').toggle();
		$('#delivery_save_button').toggle();
	}

	/**
	 * delivery保存检查
	 */
	this.deliveryCheck = function()
	{
		if($('input:radio[name="delivery_id"]:checked').length == 0)
		{
			return false;
		}
		return true;
	}

	/**
	 * delivery保存
	 */
	this.deliverySave = function()
	{
		if(this.deliveryCheck() == false)
		{
			tips('请选择配送方式');
			return;
		}

		this.paytype          = $('input:radio[name="delivery_id"]:checked').attr('paytype');
		this.deliveryActiveId = $('input:radio[name="delivery_id"]:checked').val();

		//自提点选择判断
		if( this.paytype == 2 && $('[name="takeself"]').val() == 0)
		{
			tips('请选择自提点');
			return;
		}

		this.deliveryModToggle();

		//在线支付与货到付款
		if(this.paytype == 1)
		{
			$('#paymentBox').hide('slow');
			this.paymentPrice = 0;

			//开启订单金额
			$('#amountBox').show('slow');
		}
		else
		{
			$('#paymentBox').show('slow');
		}
		//计算运费
		get_delivery();

		//计算金额
		this.doAccount();
	}

	/**
	 * payment模式切换
	 */
	this.paymentModToggle = function()
	{
		//要切换的模式
		var toggleMod = this.paymentMod == 'exit' ? 'edit' : 'exit';

		switch(toggleMod)
		{
			case "edit":
			{
				$('#'+this.paymentToggleButton).text('[退出]');
			}
			break;

			case "exit":
			{
				if(!this.paymentActiveId)
				{
					tips('请选择配送方式，并且进行保存');
					return;
				}
				else if($('input:radio[name="payment"][value="'+this.paymentActiveId+'"]:checked').length == 0)
				{
					//还原配送方式数据
					$('input:radio[name="payment"][value="'+this.paymentActiveId+'"]').trigger('click');
				}
				$('#'+this.paymentToggleButton).text('[修改]');
			}
			break;
		}

		//更新模式
		this.paymentMod = toggleMod;

		//展示模式
		$('#payment_show_box').toggle();

		//修改模式
		$('#payment_form').toggle();
		$('#payment_save_button').toggle();
	}

	/**
	 * payment检查
	 * @return boolean
	 */
	this.paymentCheck = function()
	{
		if($('input:radio[name="payment"]:checked').length == 0)
		{
			return false;
		}
		return true;
	}

	/**
	 * payment选择
	 */
	this.paymentSelected = function(jsonData)
	{
		//刷新table部分
		var paymentShowHtml = template.render('paymentShowTemplate',jsonData);
		$('#paymentShowBox').html(paymentShowHtml);
	}

	/**
	 * payment初始化
	 */
	this.paymentInit = function(defaultPaymentId)
	{
		if(defaultPaymentId > 0)
		{
			$('input:radio[name="payment"][value="'+defaultPaymentId+'"]').trigger('click');
			this.paymentSave();
		}
	}

	/**
	 * payment保存
	 */
	this.paymentSave = function()
	{
		if(this.paymentCheck() == false)
		{
			tips('请选择支付方式');
			return;
		}

		this.paymentActiveId = $('input:radio[name="payment"]:checked').val();
		this.paymentModToggle();

		//支付金额
		this.paymentPrice = $('input:radio[name="payment"]:checked').attr('alt');

		//开启订单金额
		$('#amountBox').show('slow');

		//计算金额
		this.doAccount();
	}

	/**
	 * message模式切换
	 */
	this.messageModToggle = function()
	{
		//要切换的模式
		var toggleMod = this.messageMod == 'exit' ? 'edit' : 'exit';

		switch(toggleMod)
		{
			case "edit":
			{
				$('#'+this.messageToggleButton).text('[退出]');
			}
			break;

			case "exit":
			{
				//恢复数据
				$('[name="message"]').val(this.messageActiveData);
				$('#'+this.messageToggleButton).text('[修改]');
			}
			break;
		}

		//更新模式
		this.messageMod = toggleMod;

		//展示模式
		$('#message_show_box').toggle();

		//修改模式
		$('#message_form').toggle();
		$('#message_save_button').toggle();
	}

	/**
	 * 留言保存
	 */
	this.messageSave = function()
	{
		var messageData = $('[name="message"]').val();

		//更新table
		$('#messageShowBox').text(messageData);

		//保存到缓存
		this.messageActiveData = messageData;
		this.messageModToggle();
	}

	/**
	 * 检查表单是否可以提交
	 */
	this.isSubmit = function()
	{
		var saveButtonList = $('label[id$="_save_button"]:visible');
		if(saveButtonList.length > 0)
		{
			saveButtonList.first().trigger('focus');
			return false;
		}
		return true;
	}
}

//cart2.html 接口
jQuery(function()
{
	//使用红包按钮
	$('#ticket_a').click(function()
	{
		//第一次打开时生成缓存数据
		if($.trim($('#ticket_show_box').text()) == '')
		{
			for(var index in ticketList)
			{
				var ticketHtml = template.render('ticketTrTemplate',{item:ticketList[index]});
				$('#ticket_show_box').append(ticketHtml);
			}
		}
		$(this).toggleClass('fold');
		$(this).toggleClass('unfold');
		$('#ticket_box').toggle('slow');
	});
})

/**
 * @brief 选择代金券
 * @param seller_id int   商家ID
 * @param val       float 代金券金额
 */
function userTicket(seller_id,val)
{
	//已经选中的商家
	var selectedSellerId = $("[name='ticketUserd']").val();
	var resultTicket = 0;

	if(seller_id == selectedSellerId || seller_id == 0)
	{
		resultTicket = parseFloat(val) >= parseFloat(sellerList[selectedSellerId]) ? parseFloat(sellerList[selectedSellerId]) : parseFloat(val);
		orderFormInstance.ticketPrice = resultTicket;
		orderFormInstance.doAccount();
		return true;
	}
	alert("该代金券不能用于此商家");
	$('[name="ticket_id"]').attr('checked',false);
	return false;
}

//取消红包
function cancel_ticket()
{
	$('#ticket_a').trigger('click');
	$('[name="ticket_id"]').attr('checked',false);
	orderFormInstance.ticketPrice = 0;
	orderFormInstance.doAccount();
}

//选择商家
function selectSeller()
{
	$('[name="ticket_id"]:checked').trigger('click');
}

//添加代金券
function add_ticket(getUrl)
{
	var ticket_num = $('#ticket_num').val();
	var ticket_pwd = $('#ticket_pwd').val();

	if(ticket_num == '' || ticket_pwd == '')
	{
		alert('请填写卡号和密码');
		return '';
	}

	$.getJSON(getUrl,{"ticket_num":ticket_num,"ticket_pwd":ticket_pwd},function(content){
		if(content.isError == false)
		{
			is_success = true;
			$('[name="ticket_id"]').each(
				function()
				{
					if($(this).val() == content.data.id)
					{
						alert('代金券已经存在，不要重复添加');
						is_success = false;
					}
				}
			);

			if(is_success)
			{
				var ticketHtml = template.render('ticketTrTemplate',{item:content.data});
				$('#ticket_show_box').append(ticketHtml);
				$('[name="ticket_id"]').attr('checked',true);
				$('[name="ticket_id"]:last').trigger('click');
			}
			$('#ticket_num').val('');
			$('#ticket_pwd').val('');
		}
		else
		{
			alert(content.message);
		}
	});
}