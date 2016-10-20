<?php
/**
 * @file paging_class.php
 * @brief 分页处理类
 * @note
 */
/**
 * @brief IPaging 分页处理类
 * @class IPaging
 * @note
 */
class IPaging
{
	private $fields;
	private $dbo;
	private $sql;
	private $rows;
	public $cache;
	public $index;//当前页数
	public $totalpage;//总页数
	public $pagesize;//每页的条数
	public $firstpage;//第一页
	public $lastpage;//最后一页
	public $pagelength;//要展示的页面数

    /**
     * @brief 构造函数
     * @param string $sql 要分页的SQL语句
     * @param int $pagesize 每页的记录
     * @param int $pagelength 展示pageBar的页数
     */
	public function __construct($sql="",$pagesize=20,$pagelength=10)
	{
		$this->pagesize=$pagesize;
		$this->pagelength=$pagelength;
		$this->dbo=IDBFactory::getDB();
		if($sql!="")
		{
			$this->setSql($sql);
		}
	}
    /**
     * @brief 分析要分页的SQl语句
     * @param string $sql SQL语句
     */
	public function setSql($sql)
	{
		$this->sql=$sql;
        if(strpos($sql,'GROUP BY') === false)
        {
	        $endstr = strstr($this->sql,'from');
	        $endstr = preg_replace('/^(.*)order\s+by.+$/i','$1',$endstr);
        	$count=$this->dbo->query("select count(*) as total ".$endstr);
        }
        else
        {
        	$count=$this->dbo->query("select count(*) as total from (".$sql.") as IPaging");
        }

		$this->rows=isset($count[0]['total']) ? $count[0]['total'] : 0;
		$this->firstpage=1;
		$this->totalpage=floor(($this->rows-1)/$this->pagesize)+1;
		$this->lastpage=$this->firstpage+$this->totalpage-1;
		if($this->lastpage>$this->totalpage)$this->lastpage=$this->totalpage;
	}
    /**
     * @brief 得到对应要查询分页的数据内容
     * @param int  $page要查询的页数
     * @return Array 数据
     */
	public function getPage($page)
	{
		$page=intval($page);
		$this->index=$page;
		if($page<=0)$this->index=1;
		if($this->totalpage>0)
		{
			if($page>$this->totalpage)$this->index=$this->totalpage;
			$this->firstpage=$this->index-floor($this->pagelength/2);
			if($this->firstpage<=0)$this->firstpage=1;
			$this->lastpage=$this->firstpage+$this->pagelength-1;
			if($this->lastpage>$this->totalpage)
			{
				$this->lastpage=$this->totalpage;
				$this->firstpage=($this->totalpage-$this->pagelength+1)>1?$this->totalpage-$this->pagelength+1:1;
			}
			$wholeSql = $this->sql." limit ".($this->index-1)*$this->pagesize.",".($this->pagesize);
			if($this->cache)
			{
				$cacheKey = md5($wholeSql);
            	$result   = $this->cache->get($cacheKey);
            	if($result)
            	{
            		return $result;
            	}
            	else
            	{
            		$result = $this->dbo->query($wholeSql);
            		$this->cache->set($cacheKey,$result);
            	}
			}
			else
			{
				$result = $this->dbo->query($this->sql." limit ".($this->index-1)*$this->pagesize.",".($this->pagesize));
			}
			return $result;
		}
		else return array();
	}
    /**
     * @brief 获取当前分页数
	 * @return int 分页数
	 */
	public function getIndex()
	{
		return $this->index;
	}
    /**
     * @brief 获取分页总数
	 * @return int 分页总数
	 */
	public function getTotalPage()
	{
		return $this->totalpage;
	}
    /**
     * @brief 设置展示的分页数量
	 * @return int 分页数量
	 */
	public function setPageLength($legth)
	{
		$this->pagelength=$legth;
	}
    /**
     * @brief 获取展示的分页数量
	 * @return int 分页长度
	 */
	public function getPageLength()
	{
		return $this->pagelength;
	}
    /**
     * @brief 设置每页的数据条数
     * @return int 数据条数
	 */
	public function setPageSize($size)
	{
		$this->pagesize  = $size;
		$this->totalpage = floor(($this->rows-1)/$this->pagesize)+1;
	}
    /**
     * @brief 得到单页要展示的数据条数
     * @return int 数据条数
     */
	public function getPageSize()
	{
		return $this->pagesize;
	}
    /**
     * @brief 当前pageBar的第一页
     * @return int 当前pageBar的第一页
     */
	public function getFirstPage()
	{
		return $this->firstpage;
	}
    /**
     * @brief 当前pageBar最得最后一页的页数
     * @return int 当前pageBar最后一页的页数
     */
	public function getLastPage()
	{
		return $this->lastpage;
	}
    /**
     * @brief 取得pageBar
     * @param string $url URL地址，一般为空！
     * @param string $attrs URL后接参数
     * @return string pageBar的对应HTML代码
     */
	public function getPageBar($url='', $attrs='')
	{
        $attr = '';
        if($attrs != '')
        {
            $ajax_attr = " {$attrs} ";
        }
        $flag = false;
        if($url=='')
        {
            $flag = true;
            $url = IUrl::getUri();
            $url = preg_replace('/page=\d?&/','',$url);
            $url = preg_replace('/(\?|&|\/)page(\/|=).*/i','',$url);
            $mark = '=';
            if(strpos($url,'?') !== false)
                $index = '&page';
            else
                $index = '?page';
        }
        else
        {
            $flag = false;
            $index='';
            $mark='';
        }
		//if($this->totalpage==1)return "";
		$selectpage = "";
        $baseUrl = "{$url}{$index}{$mark}";
        $baseUrl = IFilter::act($baseUrl,'text');

        $attr = str_replace('[page]',1,$attrs);
        $href = $baseUrl.($flag?1:'');
		$tem = "<ul class='pagebar pagination pull-right'>";
		$tem.="<li data-page='first'><a href='{$href}' {$attr}>首页</a></li>";
		
	

        $attr = str_replace('[page]',$this->getIndex()-1,$attrs);
        $href = $baseUrl.($flag?$this->getIndex()-1:'');
		//if($this->firstpage>1)
		$tem.="<li  class='prev' data-page='prev'><a href='{$href}' {$attr}>上页</a></li>";

		for($i=$this->firstpage;$i<=$this->lastpage;$i++)
		{
            $attr = str_replace('[page]',$i,$attrs);
            $href = $baseUrl.($flag?$i:'');
			if($i==$this->index)
			{
				$tem.="<li class='active'><a data-page='{$i}' href='{$href}' {$attr}>{$i}</a></li>";
				$selectpage .= "<option value='{$href}' selected>当前第 {$i} 页</option>";
			}
			else
			{
				$tem.="<li><a data-page='{$i}' href='{$href}' {$attr}>{$i}</a></li>";
				$selectpage .= "<option value='{$href}'>跳转到 {$i} 页</option>";
			}
			
		}
		$tem.="<li class='selectpage'><select name='selectpage' id='selectpage' onchange='window.document.location.href=this.options[this.options.selectedIndex].value'>{$selectpage}</select></li>";
        $attr = str_replace('[page]',$this->getIndex()+1,$attrs);
        $href = $baseUrl.($flag?$this->getIndex()+1:'');
		//if($this->lastpage<$this->totalpage)
		$tem.="<li class='next' data-page='next'><a href='{$href}' {$attr}>下页</a></li>";

		if($this->totalpage==0)$this->index=1;
		$attr = str_replace('[page]',$this->totalpage,$attrs);
		$href = $baseUrl.($flag?$this->totalpage:'');
		$tem .="<li data-page='last'><a href='{$href}' {$attr}>尾页</a></li>";
		$tem .="<li class='info'><span>当前第{$this->index}页/共{$this->totalpage}页</span></li>";
		return $tem."</ul>";
	}
}
?>
