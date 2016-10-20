<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>微信扫码支付</title>
<meta content="width=device-width, minimum-scale=1,initial-scale=1, maximum-scale=1, user-scalable=1;" id="viewport" name="viewport">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="telephone=no" name="format-detection">
<link type="image/x-icon" href="favicon.ico" rel="icon">
</head>
<body>
<h2>请使用微信扫一扫进行支付</h2>
<div class="main"> <img src="<?php echo $sendData['code_img'];?>" /> </div>
<a href="<?php echo $sendData['url'];?>">已经支付</a>
</body>
</html>