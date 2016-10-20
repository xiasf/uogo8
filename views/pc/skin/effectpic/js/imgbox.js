var imgbox_img=$('#imgbox').find('img')//获取图片
$(".xgt_meitu_pinterest").on("click",".thumb",function(){
	var a_img=$(this).find("img").attr("src");
	//p_date=$(this).next("p").text()
	imgbox_open(a_img)
	win_w()
})

//$(".xgt_meitu_pinterest").on("click",".thumb_img",function(){
//	alert($(this).attr('src'))
//})
$('#imgback').click(imgbox_close)
$('#img_close').click(imgbox_close)

//imgbox打开
function imgbox_open(a_img){
	imgbox_img.attr('src',a_img);
	//$('#imgbox').find('p').text(p_date);
	compu()
	$('#imgback').css({'display':' block'})
	$('#img_close').css({'display':' block'})
	imgbox_img.animate({
		opacity:'1',
  },500)
}
//imgbox关闭
function imgbox_close(){
	$('#imgback').css({'display':' none'}); 
	$('#img_close').css({'display':' none'});
	imgbox_img.attr('src','');
	imgbox_img.animate({
		opacity:'0',
  });
	
}
//偏移计算
function compu(){
	var realwidth=imgbox_img.width() //图片实际宽度
	var realheight=imgbox_img.height() //图片实际高度
	imgbox_img.css({'max-width':(window.innerWidth*0.9)+'px','max-height':(window.innerHeight*0.9)+'px'})
	$('#img_close').css({'margin-left':(-realwidth/2)+'px','margin-top':(-realheight/2)+'px'})
	imgbox_img.css({'margin-left':(-realwidth/2)+'px','margin-top':(-realheight/2)+'px'})
}
//获取窗口大小
function win_w(){
	$('#imgback').css({'height':window.innerHeight+'px','width':window.innerWidth+'px'})
}
//窗口改变时
$(window).resize(function(){
	compu()
	win_w()
})
