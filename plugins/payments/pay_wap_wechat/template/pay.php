<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>微信支付</title>
<meta content="width=device-width, minimum-scale=1,initial-scale=1, maximum-scale=1, user-scalable=1;" id="viewport" name="viewport">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="telephone=no" name="format-detection">
<link type="image/x-icon" href="favicon.ico" rel="icon">
<head>
<script type='text/javascript'>
function onBridgeReady()
{
	WeixinJSBridge.invoke
	(
		'getBrandWCPayRequest',
		{
			"appId" : "<?php echo $return['appId'];?>",//公众号名称，由商户传入
			"timeStamp":"<?php echo $return['timeStamp'];?>",//时间戳，自1970年以来的秒数
			"nonceStr" : "<?php echo $return['nonceStr'];?>", //随机串
			"package" : "<?php echo $return['package'];?>",//生成预付款订单
			"signType" : "<?php echo $return['signType'];?>",//微信签名方式:
			"paySign" : "<?php echo $return['paySign'];?>" //微信签名
		},
		function(res)
		{
			// 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。
			if(res.err_msg == "get_brand_wcpay_request:ok")
			{
				window.location.href="<?php echo $return['successUrl'];?>";
			}
			else
			{
				alert(res.err_msg);
				window.location.href="<?php echo $return['failUrl'];?>";
			}
		}
	);
}

if(typeof WeixinJSBridge == "undefined")
{
	if( document.addEventListener )
	{
		document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
	}
	else if (document.attachEvent)
	{
		document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
		document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
	}
}
else
{
	onBridgeReady();
}
</script>
</head>

<body>
正在启动微信支付环境......
</body>
</html>