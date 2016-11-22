jQuery(function($){
		var m = function(options){
			
			
		var $list = $(options.filelist),
        // 优化retina, 在retina下这个值是2
        ratio = window.devicePixelRatio || 1,

        // 缩略图大小
        thumbnailWidth = 100 * ratio,
        thumbnailHeight = 100 * ratio,

        // Web Uploader实例
        uploader,
		
		uploaderconfig = {
			// 自动上传。
			auto: true,
			// swf文件路径
			swf: '',//BASE_URL + '/js/Uploader.swf',
			// 文件接收服务端。
			server: '',
			// 选择文件的按钮。可选。
			// 内部根据当前运行是创建，可能是input元素，也可能是flash.
			pick: '#filePicker',
			// 只允许选择文件，可选。
			accept: {
				title: 'Images',
				extensions: 'gif,jpg,jpeg,bmp,png',
				mimeTypes: 'image/*'
			},
			resize: false
	
		};
		uploaderconfig.pick = this;
		uploaderconfig.server = options.uploadurl;//alert(options.filelist)

		this.options = $.extend(uploaderconfig, options);

		if($list.length==0)$list = $('<ul class="uploader-list" id="'+options.filelist+'"></ul>').insertAfter(this);
		// 初始化Web Uploader
		uploader = WebUploader.create(uploaderconfig);
		// 当有文件添加进来的时候
		uploader.on( 'fileQueued', function( file ) {
			options.isone && $list.empty();
			var $li = $(
					'<div id="' + file.id + '" class="file-item thumbnail">' +
						'<img>' +
					'</div>'
					),
				$img = $li.find('img');
				
			$list.append( $li );
	
			// 创建缩略图
			uploader.makeThumb( file, function( error, src ) {
				if ( error ) {
					$img.replaceWith('<span>不能预览</span>');
					return;
				}
	
				$img.attr( 'src', src );
			}, thumbnailWidth, thumbnailHeight );
		});
	
		// 文件上传过程中创建进度条实时显示。
		uploader.on( 'uploadProgress', function( file, percentage ) {
			var $li = $( '#'+file.id ),
				$percent = $li.find('.progress span');
	
			// 避免重复创建
			if ( !$percent.length ) {
				$percent = $('<p class="progress"><span></span></p>')
						.appendTo( $li )
						.find('span');
			}
	
			$percent.css( 'width', percentage * 100 + '%' );
		});
	
		// 文件上传成功，给item添加成功class, 用样式标记上传成功。
		uploader.on( 'uploadSuccess', function( file,result ) {
			var $li = $( '#'+file.id );
			if(result.flag == 1){
			$li.addClass('file-item-done')
				.children("img")
				.attr({
					"src":"/"+result.img,"alt":result.img,"data-url":result.img
				});
				options.uploaded(result)
			}else{
				$('<div class="error">错误：'+ result.flag +'</div>').appendTo( $li )
			}
		});
	
		// 文件上传失败，现实上传出错。
		uploader.on( 'uploadError', function( file ) {
			var $li = $( '#'+file.id ),
				$error = $li.find('div.error');
	
			// 避免重复创建
			if ( !$error.length ) {
				$error = $('<div class="error"></div>').appendTo( $li );
			}
	
			$error.text('上传失败');
		});
	
		// 完成上传完了，成功或者失败，先删除进度条。
		uploader.on( 'uploadComplete', function( file ) {
			$( '#'+file.id ).find('.progress').remove();
		});
	};
	$.fn.uploader = function(opt){
		var options = $.extend({filelist:'',uploadurl:"",uploaded:function(){},auto:true,isone:true},opt),i = 0;
		return this.each(function(i,d){
			!options.filelist &&  (options.filelist = "filelist-" + i++);
			return m.apply(this, [options]);
		});		
	};
});