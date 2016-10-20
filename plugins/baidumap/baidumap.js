/**
 * Baidu地图v2版本API jQuery插件封装
 */
document.write('<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=f8zPF65vLZ0yb0U2I7w5inja"></script>');
(function($) { 

    $.fn.baidumap = function(options) {
		/*
		 * 默认参数设置
		 */
		this.defaults = {
			lng : 114.887887, // 中心点 经度
			lat : 30.436029, // 中心点  纬度
			address:"",//地址
			zoom : 15, // 缩放级别
			suid:"",//如果有那么启动搜索
			marker:"请按住此标记拖动到您商铺的准确坐标！",//标记内容。为空时不启用
			markerdragend:null,//标记回调
			enableScrollWheelZoom : true, // 启用鼠标滚轮缩放
			enableNavigationControl : true, // 启用平移缩放控件
			enableOverviewMapControl : true, // 启用缩略地图控件
			enableScaleControl : true, // 比例尺控件
			enableMapTypeControl : true, // 切换地图类型的控件
			enableCopyrightControl : true,// 版权信息控件
			enablePanoramaCoverageLayer : true,//全景控件
			copyrightContent : '<a href=\'http://www.itaohz.com\' style=\'font-size:11px;color:red;text-decoration:none;\' target=\'_blank\'>www.itaohz.com</a>',// 版权信息
			copyrightAnchor : BMAP_ANCHOR_TOP_LEFT // 版权信息显示的位置
		};


        var opts = this.options = $.extend({}, this.defaults, options);
		//创建地图
        var map = this.map =  new BMap.Map(this.attr("id"));
        var point = new BMap.Point(opts.lng, opts.lat); // 创建中心点坐标
        map.centerAndZoom(point, opts.zoom); // 初始化地图，设置中心点坐标和地图级别
		//初始化控件
        if (opts.enableScrollWheelZoom) {
            map.enableScrollWheelZoom();
        }
        if (opts.enableNavigationControl) {
            map.addControl(new BMap.NavigationControl());
        }
        if (opts.enableOverviewMapControl) {
            map.addControl(new BMap.OverviewMapControl());
        }
        if (opts.enableScaleControl) {
            map.addControl(new BMap.ScaleControl());
        }
        if (opts.enableMapTypeControl) {
            map.addControl(new BMap.MapTypeControl());
        }
        if (opts.enableCopyrightControl) {
            var cr = new BMap.CopyrightControl({
                anchor : BMAP_ANCHOR_TOP_LEFT
            });
            map.addControl(cr);
            var bs = map.getBounds();
            cr.addCopyright({
                id : 1,
                content : opts.copyrightContent,
                bounds : bs
            });
        };
		if(opts.enablePanoramaCoverageLayer){
			//全景控件
			map.addTileLayer(new BMap.PanoramaCoverageLayer());
			var stCtrl = new BMap.PanoramaControl(); //构造全景控件
			stCtrl.setOffset(new BMap.Size(20, 60));
			map.addControl(stCtrl);//添加全景控件
		};
		if(opts.suid){
			var ac = new BMap.Autocomplete(    //建立一个自动完成的对象
				{"input" : opts.suid,"location" : map
			});
		};
		
		//创建标记锚点
		this.markerTo = function(points){
			if(!opts.marker)return false;
			map.clearOverlays();
			var label = new BMap.Label(opts.marker, {'offset': new BMap.Size(20,-10)});
			//label.setStyle({backgroundColor:"white", color:"red", fontSize : "12px" });
			var marker = new BMap.Marker(points);
			marker.setLabel(label);
			map.addOverlay(marker);
			marker.enableDragging();
			var geo = new BMap.Geocoder(); 
			marker.addEventListener('dragend', function(e){
				var point = marker.getPosition();
				geo.getLocation(point, function(addr){
					var val = {lng: point.lng, lat: point.lat, address: addr.address};
					opts.lng = point.lng;opts.lat = point.lat;opts.address = addr.address;
					$.isFunction(opts.markerdragend) && opts.markerdragend(val);
				});
			});
		};
		/**
		 * 定位到指定坐标
		 * @param x
		 *            纬度
		 * @param y
		 *            经度
		 * @param zoom
		 *            缩放级别
		 */
		this.panTo = function(x, y, zoom) {
			var map = this.map,m= this;
			//map.clearOverlays();
			if (parseInt(x) == 0) {
				return;
			}
			if (x != "") {
				var point = new BMap.Point(x, y);
				setTimeout(function(){
					map.centerAndZoom(point, parseInt(zoom));
					m.markerTo(point);
				}, 100);
			}
		};


		/**
		 * 在地图上搜索地点，并且标记
		 * @param city
		 *            城市名称
		 * @param address
		 *            详细地址
		 */
		this.search = function(city, address) {
			var map = this.map,m = this;
			// 创建地址解析器实例
			var myGeo = new BMap.Geocoder();
			// 将地址解析结果显示在地图上，并调整地图视野
			myGeo.getPoint(address, function(point) {
				if (point) {
					// 跳动的动画
					//resultMarker.setAnimation(BMAP_ANIMATION_BOUNCE);
					map.centerAndZoom(point, m.options.zoom);
					setTimeout(function(){m.markerTo(point);}, 100);
				}
			}, city);
		};
		/**
		 * 添加右键菜单
		 * 
		 * @param menuOptions
		 *            菜单配置项，值为对象数组，每个对象有text,callback两个参数，text值为‘-’时表示菜单项的分隔符<br/>
		 *            exp. [{text:'',callback:function(point){}}]
		 * @returns BMap.ContextMenu
		 */
		this.addContextMenu = function(menuOptions) {
			var contextMenu = new BMap.ContextMenu();
			for ( var i = 0; i < menuOptions.length; i++) {
				if (menuOptions[i].text == "-") {
					// 添加分隔线
					contextMenu.addSeparator();
				} else {
					contextMenu.addItem(new BMap.MenuItem(menuOptions[i].text,
							menuOptions[i].callback));
				}
			}
			this.addContextMenu(contextMenu);
			return contextMenu;
		};

		/**
		 * 新建一个信息窗口
		 * 
		 * @param content
		 *            支持html的内容
		 * @returns BMap.InfoWindow
		 */
		this.createInfoWindow = function(content) {
			var map = this.map;
		 //创建检索信息窗口对象
/*			var searchInfoWindow = null;
			searchInfoWindow = new BMapLib.SearchInfoWindow(map, content, {
					title  : "百度大厦",      //标题
					width  : 290,             //宽度
					height : 105,              //高度
					panel  : "panel",         //检索结果面板
					enableAutoPan : true,     //自动平移
					searchTypes   :[
						BMAPLIB_TAB_SEARCH,   //周边检索
						BMAPLIB_TAB_TO_HERE  //到这里去
						//BMAPLIB_TAB_FROM_HERE //从这里出发
					]
				});
			var marker = new BMap.Marker(opts.); //创建marker对象
			marker.enableDragging(); //marker可拖拽
			marker.addEventListener("click", function(e){
				searchInfoWindow.open(marker);
			})
			map.addOverlay(marker); //在地图中添加marker			
*/			return new BMap.InfoWindow(content);
		};
		//定位到
		this.rePos = function(options){
			var t = this;
			//坐标不为空时按坐标显示
			if (options.lng>0 && options.lat>0){t.panTo(options.lng,options.lat,options.zoom);}
			//否则按地址显示
			else if (options.address != ""){t.search(options.city,options.address);}
/*			//否则按城市显示
			else if (options.city != ""){map.setCenter(options.city);}
*/			//都为空按IP定位
			else{
				//创建一个获取本地城市位置的实例
				var myCity = new BMap.LocalCity();
				//获取城市
				myCity.get(function(result){t.search(result.name,result.name);});
			}
			
		};
		this.rePos(opts);
		map.addEventListener("tilesloaded",
		function(){
			
		});
        return this;
    };
})(jQuery);