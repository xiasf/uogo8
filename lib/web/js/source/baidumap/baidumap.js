/**
 * Baidu地图v2版本API jQuery插件封装
 */
(function($) {
    $.fn.baidumap = function(options) {
        var opts = $.extend({}, $.fn.baidumap.defaults, options);
        $.fn.baidumap.options = opts;
        var map = new BMap.Map(this.attr("id"));
        var point = new BMap.Point(opts.x, opts.y); // 创建中心点坐标
        map.centerAndZoom(point, opts.zoom); // 初始化地图，设置中心点坐标和地图级别
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
                anchor : BMAP_ANCHOR_TOP_RIGHT
            });
            map.addControl(cr);
            var bs = map.getBounds();
            cr.addCopyright({
                id : 1,
                content : opts.copyrightContent,
                bounds : bs
            });
        }
        $.fn.baidumap.map = map;
        return $.fn.baidumap;
    };
    /*
     * 默认参数设置
     */
    $.fn.baidumap.defaults = {
        x : 108.79643, // 中心点 x坐标
        y : 34.261848, // 中心点y坐标
        zoom : 5, // 缩放级别
        enableScrollWheelZoom : true, // 启用鼠标滚轮缩放
        enableNavigationControl : true, // 启用平移缩放控件
        enableOverviewMapControl : false, // 启用缩略地图控件
        enableScaleControl : true, // 比例尺控件
        enableMapTypeControl : false, // 切换地图类型的控件
        enableCopyrightControl : false,// 版权信息空间
        copyrightContent : '<a href=\'http://www.xxxsoft.com\' style=\'font-size:11px;color:red;text-decoration:none;\' target=\'_blank\'>xxxsoft.com</a>',// 版权信息
        copyrightAnchor : BMAP_ANCHOR_TOP_RIGHT // 版权信息显示的位置
    };
    /**
     * 定位到指定坐标
     * 
     * @param x
     *            纬度
     * @param y
     *            经度
     * @param zoom
     *            缩放级别
     */
    $.fn.baidumap.panTo = function(x, y, zoom) {
        var map = $.fn.baidumap.map;
        map.clearOverlays();
        if (parseInt(x) == 0) {
            return;
        }
        if (x != "") {
            var temp_point = new BMap.Point(x, y);
            map.centerAndZoom(temp_point, parseInt(zoom));
        }
    };
    /**
     * 在地图上搜索地点，并且标记
     * 
     * @param city
     *            城市名称
     * @param address
     *            详细地址
     */
    $.fn.baidumap.search = function(city, address) {
        var map = $.fn.baidumap.map;
        map.clearOverlays();
        // 创建地址解析器实例
        var myGeo = new BMap.Geocoder();
        // 将地址解析结果显示在地图上，并调整地图视野
        myGeo.getPoint(address, function(point) {
            if (point) {
                var resultMarker = new BMap.Marker(point);
                // 跳动的动画
                resultMarker.setAnimation(BMAP_ANIMATION_BOUNCE);
                map.centerAndZoom(point, 16);
                map.addOverlay(resultMarker);
            }
        }, city);
    };
    /**
     * 定位的指定行政区域
     * 
     * @param org
     *            行政区域名称（支持省、市、县三级）
     */
    $.fn.baidumap.location = function(org) {
        var map = $.fn.baidumap.map;
        var bdary = new BMap.Boundary();
        bdary.get(org, function(rs) { // 获取行政区域
            map.clearOverlays(); // 清除地图覆盖物
            var count = rs.boundaries.length; // 行政区域的点有多少个
            var plys = new Array(count);
            // bugfix: 在地图上不相连的某个区划，返回的数组中，默认从小范围到大范围，so, i--优先显示大范围
            for ( var i = count - 1; i >= 0; i--) {
                var ply = new BMap.Polygon(rs.boundaries[i], {
                    strokeWeight : 2,
                    strokeColor : "#ff0000",
                    fillOpacity : 0
                }); // 建立多边形覆盖物
                plys[i] = ply;
                map.addOverlay(ply); // 添加覆盖物
                map.setViewport(ply.getPath()); // 调整视野
            }
        });
        // 1秒后清除覆盖物
        setTimeout('$.fn.baidumap.map.clearOverlays()', 1000);
    };
    /**
     * 添加右键菜单
     * 
     * @param menuOptions
     *            菜单配置项，值为对象数组，每个对象有text,callback两个参数，text值为‘-’时表示菜单项的分隔符<br/>
     *            exp. [{text:'',callback:function(point){}}]
     * @returns BMap.ContextMenu
     */
    $.fn.baidumap.addContextMenu = function(menuOptions) {
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
        $.fn.baidumap.map.addContextMenu(contextMenu);
        return contextMenu;
    };
    /**
     * 新建一个图标
     * 
     * @param options
     *            图标属性 exp .{url:图标路径,width:宽度,height:高度}
     * @returns BMap.Icon
     */
    $.fn.baidumap.createIcon = function(options) {
        return new BMap.Icon(options.url, new BMap.Size(options.width,
                options.height));
    };
    /**
     * 新建一个文本标注
     * 
     * @param options
     *            标注的属性 exp. {title:标题,offsetX:横向偏移量,offsetY:纵向偏移量,style:css样式}
     * @returns BMap.Label
     */
    $.fn.baidumap.createLabel = function(options) {
        var label = new BMap.Label(options.title);
        if (options.offsetX) {
            label.setOffset(new BMap.Size(options.offsetX, options.offsetY));
        }
        if (options.style) {
            label.setStyle(options.style);
        }
        return label;
    };
    /**
     * 新建一个信息窗口
     * 
     * @param content
     *            支持html的内容
     * @returns BMap.InfoWindow
     */
    $.fn.baidumap.createInfoWindow = function(content) {
        return new BMap.InfoWindow(content);
    };
    /**
     * 新建一个图形标记
     * 
     * @param options
     *            标记的属性 exp.{point:位置 BMap.Point值,icon: 图标BMap.Icon值,
     *            label:图标的文本说明}
     * 
     * @returns BMap.Marker
     */
    $.fn.baidumap.createMarker = function(options) {
        var marker = new BMap.Marker(options.point);
        if (options.icon) {
            marker.setIcon(options.icon);
        }
        if (options.label) {
            marker.setLabel(options.label);
        }
        $.fn.baidumap.map.addOverlay(marker);
        return marker;
    };
})(jQuery);