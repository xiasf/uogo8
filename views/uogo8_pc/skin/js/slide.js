(function($){
    $.fn.ckSlide = function(opts){
        opts = $.extend({}, $.fn.ckSlide.opts, opts);
        this.each(function(){
            var slidewrap = $(this).find('.slider_main');
            var slide = slidewrap.find('li.slider_panel');
            var count = slide.length;
            var that = this;
            var index = 0;
            var time = null;
			var fade_indexBtn = "";
            
			for (var i=0;i<count;i++)
			{
			fade_indexBtn+= "<b></b>";
			}
			$(this).find('.fade_indexBtn').append(fade_indexBtn);
			
            $(this).find('.fade_indexBtn>b').each(function(cindex){
                $(this).on('click.slidebox', function(){
                    change.call(that, cindex);
                    index = cindex;
                });
            });
            
            // focus clean auto play  ui-switchable-panel-selected
            $(this).on('mouseover', function(){
                if(opts.autoPlay){
                    clearInterval(time);
                }
                //$(this).find('.ctrl-slide').css({opacity:0.6});
            });
            //  leave
            $(this).on('mouseleave', function(){
                if(opts.autoPlay){
                    startAtuoPlay();
                }
                //$(this).find('.ctrl-slide').css({opacity:0.15});
            });
			change.call(that,index);
            startAtuoPlay();
			
            // auto play
            function startAtuoPlay(){
				
                if(opts.autoPlay){
                    time  = setInterval(function(){
                        
                        if(index >= count - 1){
                            index = 0;
                        }else{
                            index++;
                        }
                        change.call(that,index);
                    }, 5000);
                }
            }

        });
    };   
    function change(show){
		
		$(this).find('.slider_main>li.slider_panel').animate({opacity:0}).eq(show).animate({opacity:1});
		$(this).find('.fade_indexBtn>b').removeClass('indexSelected');
		$(this).find('.fade_indexBtn>b').eq(show).addClass('indexSelected');
    }
    $.fn.ckSlide.opts = {
        autoPlay: true
    };
})(jQuery);