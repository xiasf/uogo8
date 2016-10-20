<?php
/**
 * 上传附件和上传视频
 * User: Jinqn
 * Date: 14-04-09
 * Time: 上午10:17
 */
include "Uploader.class.php";

/* 上传配置 */
$base64 = "upload";
switch (htmlspecialchars($_GET['action'])) {
    case 'uploadimage':
        $config = array(
            "pathFormat" => $CONFIG['imagePathFormat'],
            "maxSize" => $CONFIG['imageMaxSize'],
            "allowFiles" => $CONFIG['imageAllowFiles']
        );
        $fieldName = $CONFIG['imageFieldName'];
        break;
    case 'uploadscrawl':
        $config = array(
            "pathFormat" => $CONFIG['scrawlPathFormat'],
            "maxSize" => $CONFIG['scrawlMaxSize'],
            "allowFiles" => $CONFIG['scrawlAllowFiles'],
            "oriName" => "scrawl.png"
        );
        $fieldName = $CONFIG['scrawlFieldName'];
        $base64 = "base64";
        break;
    case 'uploadvideo':
        $config = array(
            "pathFormat" => $CONFIG['videoPathFormat'],
            "maxSize" => $CONFIG['videoMaxSize'],
            "allowFiles" => $CONFIG['videoAllowFiles']
        );
        $fieldName = $CONFIG['videoFieldName'];
        break;
    case 'uploadfile':
    default:
        $config = array(
            "pathFormat" => $CONFIG['filePathFormat'],
            "maxSize" => $CONFIG['fileMaxSize'],
            "allowFiles" => $CONFIG['fileAllowFiles']
        );
        $fieldName = $CONFIG['fileFieldName'];
        break;
}

/* 生成上传实例对象并完成上传 */
$up = new Uploader($fieldName, $config, $base64);

/**
 * 得到上传文件所对应的各个参数,数组结构
 * array(
 *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
 *     "url" => "",            //返回的地址
 *     "title" => "",          //新文件名
 *     "original" => "",       //原始文件名
 *     "type" => ""            //文件类型
 *     "size" => "",           //文件大小
 * )
 */

$a = $up->getFileInfo();

if ($_GET['fw']) {
    $file = $_SERVER['DOCUMENT_ROOT'] . $a['url'];
    $sy = $_SERVER['DOCUMENT_ROOT'].'/plugins/sy/sy.png';
    // $ttf = $_SERVER['DOCUMENT_ROOT'] . '/plugins/sy/ttfs/ww.ttf';

    // include $_SERVER['DOCUMENT_ROOT'].'/classes/Image.class.php';
    include "Image.class.php";

    $_image = new Image();
    $image = new Image();

    $_image->open($sy);
    $image->open($file);

    list($_width, $_height) = $_image->size();
    list($width, $height) = $image->size();

    $h = ($height - $_height) / 2;

    $start_width = 0;
    $arr = array();
    while ($start_width <= $width) {
        $arr[] = array($start_width, $h);
        $image->water($sy, array($start_width, $h), 100);
        $start_width += $_width + 10;
    }

    // $image->text('淘黄州 www.itaohz.com',$ttf,20,'#ffffff',array($width - 330,$height - 35))->save($file);
    $image->save($file);
}


/* 返回数据 */
return json_encode($up->getFileInfo());
