<?php require(ITag::createRuntime("incfile/header_base"));?><title><?php echo $this->config["seo_title"];?></title><meta name='keywords' content='<?php echo $this->config[seo_keys];?>'><meta name='description' content='<?php echo $this->config[seo_desc];?>'><link href="<?php echo IUrl::getHost().$this->getWebViewPath()."skin/res/site.css";?>" media="screen"  rel="stylesheet"  type="text/css"></head><body><?php require(ITag::createRuntime("incfile/header_top"));?><?php require(ITag::createRuntime("incfile/header_nav"));?><?php 
$seo_data    = array();
$site_config = new Config('site_config');
$site_config = $site_config->getInfo();
$seo_data['title'] = "用户登录_".$site_config['name'];
seo::set($seo_data);
?><div class="wrapper clearfix"><div class="wrap_box"><h3 class="notice">已注册用户，请登录</h3><p class="tips">欢迎来到我们的网站，如果您已是本站会员请登录</p><div class="content_login"><div class="coLoginBody"><div class="coLogin_title"><p><span>请登录</span>还没有账号？<a href="<?php echo IUrl::getHost().IUrl::creatUrl("simple/reg");?>">立即注册</a></p></div><form action='<?php echo IUrl::getHost().IUrl::creatUrl("/simple/login_act");?>' method='post'><input type="hidden" name='callback' /><table class="form_table f_l" width="515" style="margin-left:50px;"><colgroup><col width="100px"><col></colgroup><tbody><tr><th>用户名/邮箱：</th><td><input class="gray" name="login_info" value="" pattern="required" alt="填写用户名" type="text"></td></tr><tr><th>密码：</th><td><input class="gray" name="password" pattern="^\S{6,32}$" alt="填写密码" type="password"></td></tr><tr class="low"><td></td><td><label class="attr"><input class="radio" name="remember" value="1" type="checkbox">记住登录名</label><label class="attr"><a class="link pwd" href="<?php echo IUrl::getHost().IUrl::creatUrl("/simple/find_password");?>">忘记密码</a></label></td></tr><tr><td></td><td><input class="submit_login" value="登录" type="submit"></td></tr></tbody></table></form></div><div class="coLogin_bottom"><p>您也可以使用合作网站账号登录</p><ul><?php foreach(Api::run('getOauthList') as $key =>$item){?><a href="javascript:oauthlogin('<?php echo isset($item['id'])?$item['id']:"";?>');" id="imgLink"><img skin="skin" src='<?php echo IUrl::creatUrl("".$item['logo']."");?>' /></a><?php }?></ul></div></div></div></div><script type='text/javascript'><?php $callback = IReq::get('callback') ? IFilter::clearUrl(IReq::get('callback')) :IUrl::getRefRoute()?><?php if($this->message){?>alert('<?php echo $this->message;?>')<?php }?>//DOM加载结束
$(function(){
//回调地址设置
$('input[name="callback"]').val("<?php echo isset($callback)?$callback:"";?>");
$('.reg_btn').attr('href',"<?php echo IUrl::getHost().IUrl::creatUrl("/simple/reg?callback=".$callback."");?>");
$(".form_table input").focus(function(){$(this).addClass('current');}).blur(function(){$(this).removeClass('current');})
});
//多平台登录
function oauthlogin(oauth_id)
{
$.getJSON('<?php echo IUrl::getHost().IUrl::creatUrl("/simple/oauth_login");?>',{"id":oauth_id,"callback":"<?php echo isset($callback)?$callback:"";?>"},function(content){
if(content.isError == false)
{
window.location.href = content.url;
}
else
{
alert(content.message);
}
});
}
//下一步操作
function next_step()
{
var step_val = $('[name="next_step"]:checked').val();
if(step_val == 'acount')
{
<?php $url = rtrim($callback,"/")."/tourist/yes"?>window.location.href = '<?php echo IUrl::getHost().IUrl::creatUrl("".$url."");?>';
}
else if(step_val == 'reg')
{
window.location.href = '<?php echo IUrl::getHost().IUrl::creatUrl("/simple/reg?callback=".$callback."");?>';
}
}
</script><?php require(ITag::createRuntime("incfile/footer"));?></body></html>