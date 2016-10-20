<?php return array (
  'logs' => 
  array (
    'path' => 'backup/logs/log',
    'type' => 'file',
  ),
  'DB' => 
  array (
    'type' => 'mysqli',
    'tablePre' => 'itaohz_',
    'read' => 
    array (
      0 => 
      array (
        'host' => '127.0.0.1:3306',
        'user' => 'root',
        'passwd' => 'qq5752020',
        'name' => 'uogo8',
      ),
    ),
    'write' => 
    array (
      'host' => '127.0.0.1:3306',
      'user' => 'root',
      'passwd' => 'qq5752020',
      'name' => 'uogo8',
    ),
  ),
  'interceptor' => 
  array (
    0 => 'themeroute@onCreateController',
    1 => 'layoutroute@onCreateView',
    2 => 'hookCreateAction@onCreateAction',
    3 => 'hookFinishAction@onFinishAction',
  ),
  'langPath' => 'language',
  'viewPath' => 'views',
  'skinPath' => 'skin',
  'classes' => 'classes.*',
  'rewriteRule' => 'pathinfo',
  'urlRoute' => 
  array (
    's' => 'systemadmin/index',
    'd' => 'sellermain/index',
	'dlogin' => 'systemseller/index',
	'dshop' => 'decorat/index',
    'u' => 'ucenter/index',
    '/' => 'site/index',
	'activity-<id:\\d+>.html' =>'site/activity/',
    'story-<id:\\d+>.html' => 'site/article_story',
    'shop-<id:\\d+>.html' => 'site/home',
    'article.html' => 'site/article',
    'list-<cat:\\d+>.html' => 'site/pro_list',
    'item-<id:\\w+>.html' => 'site/products',
    'tuan.html' => 'site/groupon',
    'tuan-<id:\\w+>-<active_id:\\w+>.html' => 'site/products_tuan',
    'tuan-list.html' => 'site/groupon_list',
    'time-<id:\\w+>-<active_id:\\w+>.html' => 'site/products_time',
    'brand.html' => 'site/brand',
    'brandzone-<id:\\w+>.html' => 'site/brand_zone',
    'login.html' => 'simple/login',
    'help.html' => 'site/help',
    'help-index.html' => 'site/help_list',
    'cart.html' => 'simple/cart',
    'cart-2.html' => 'simple/cart2',
    'cart-3.html' => 'simple/cart3',
    'notice.html' => 'site/notice',
    'search.html' => 'site/search_list',
    'error.html' => 'site/error',
    'notice-<id:\\d+>.html' => 'site/notice_detail',
    'pic-show.html' => 'site/pic_show',
  ),
  'theme' => 
  array (
    'pc' => 
    array (
      'sysdefault' => 'default',
      'pc' => 'default',
      'sysseller' => 'default',
    ),
    'mobile' => 
    array (
      'mobile' => 'default',
      'sysseller' => 'default',
    ),
  ),
  'timezone' => 'Etc/GMT-8',
  'upload' => 'upload',
  'dbbackup' => 'backup/database',
  'safe' => 'session',
  'lang' => 'zh_sc',
  'debug' => 1,     
  'configExt' => 
  array (
    'site_config' => 'config/site_config.php',
  ),
  'encryptKey' => '9f8027db6338899725bd4f5e7f975c44',
)?>
