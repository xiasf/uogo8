<?php
/**
 * @file tag_class.php
 * @brief 标签解析类文件
 */
class ITag
{
    //视图路径
	private $viewPath;
	
    /**
     * @brief  解析给定的字符串
     * @param string $str 要解析的字符串
     * @param mixed $path 视图文件的路径
     * @return String 解析处理的字符串
     */
	public function resolve($content,$path=null)
	{
		$this->viewPath = $path;
		//echo $this->viewPath;
		$str = $this->HTMLFORMAT($content);
		$str = preg_replace_callback('/{(\/?)(\$|url|webroot|theme|skin|echo|query|foreach|set|include|file|if|elseif|else|while|for|js)\s*(:?)([^}]*)}/i', array($this,'translate'), $str);
		$str = $this->HTMLDEL($str);
		return $str;
	}
    /**
     * @brief  格式化HTML，做前期的解析准备
     * @param string $str 要解析的字符串
     * @return String 解析处理的字符串
     */
	public function HTMLFORMAT($content)
	{
		$str = $content;
		$str = ereg_replace("/\*[^*]*\*/","",$str); //干掉 /**/ 块注释
		$str = preg_replace("/<\!--.*?-->/si", "", $str); // 干掉 HTML 注释
		//$str = preg_replace("/\/\/.*?\n/si", "", $str); // 干掉 反斜杠注释
		$str = $this->HTMLDEL($str);
		//$pattern = '/<script[\s\S]*?<\/script>/i';// 规格化JS 
		//preg_match_all($pattern,$str,$matches);
		//foreach($matches[0] as $value)
		//{
			//$new_str = ereg_replace("/\}(\s)|(\n)|(\r)|(\t)/s","};",$value); 
			//$new_str = ereg_replace("/(\n)|(\r)\{(\s)|(\n)|(\r)/","{",$new_str);
			//$new_str = ereg_replace(array("\r\n", "\r", "\n","\n\n"),"",$new_str);
			//$new_str = ereg_replace("/(\s)|(\n)|(\r)/","",$new_str);
			//$str = ereg_replace($value,$new_str,$str);
			
		//}
		//var_dump($matches);

		
		$str = $this->getAttrReplace($str);
		
		return $str; 
	}
	public function HTMLDEL($str)
	{
		$str = trim($str);
		$str = ereg_replace("\t","",$str);
		$str = ereg_replace("\r","\n",$str);
		$str = ereg_replace("\n\n","\n",$str);
		$str = preg_replace(
				$search = array(
					'/\>[^\S]+/s',  // 删除标签后面空格
					'/>[^\S ]+\</s',  // 删除标签前面的空格
					'/>(\s)+</s'       // 删除标签多个空格
					
				), 
				array(
					'>',
					'><',
					'><'
				),
				$str
		); //注释
		return trim($str);	
	}
	
    /**
     * @brief 处理设定的每一个标签
     * @param array $matches
     * @return String php代码
     */
	public function translate($matches)
	{
		if($matches[1]!=='/')
		{
			switch($matches[2].$matches[3])
			{
				case '$':
                {
                    $str = trim($matches[4]);
                    $first = $str[0];
					if($first != '.' && $first != '(')
					{
						if( strpos($str,'(') !== false || (strpos($str,'[') === false && strpos($str,'->') !== false) )
						{
							return '<?php echo $'.$str.';?>';
						}
						else
						{
							return '<?php echo isset($'.$str.')?$'.$str.':"";?>';
						}
					}
                    else return $matches[0];
                }
				case 'echo:': return '<?php echo '.rtrim($matches[4],';/').';?>';
                case 'js:': return IJSPackage::load($matches[4]);
				case 'url:':
				{
					$matches[4] = $this->varReplace($matches[4]);
					return '<?php echo IUrl::getHost().IUrl::creatUrl("'.$matches[4].'");?>';
				}
                case 'webroot:':
                {
                	$matches[4] = $this->varReplace($matches[4]);
                	return '<?php echo IUrl::creatUrl("'.$matches[4].'");?>';
                }
                case 'theme:': return '<?php echo IUrl::getHost().$this->getWebViewPath()."'.$matches[4].'";?>';//   $this->config["url"].
                case 'skin:': return '<?php echo IUrl::getHost().$this->getWebSkinPath()."'.$matches[4].'";?>';
				case 'if:': return '<?php if('.$matches[4].'){?>';
				case 'elseif:': return '<?php }elseif('.$matches[4].'){?>';
				case 'else:': return '<?php }else{'.$matches[4].'?>';
				case 'set:':
                {
                    return '<?php '.$matches[4].'?>';
                }
				case 'while:': return '<?php while('.$matches[4].'){?>';
				case 'foreach:':
				{
					$attr = $this->getAttrs($matches[4]);
					if(!isset($attr['items'])) $attr['items'] = '$items';
					else $attr['items'] = $attr['items'];
					if(!isset($attr['key'])) $attr['key'] = '$key';
					else $attr['key'] = $attr['key'];
					if(!isset($attr['item'])) $attr['item'] = '$item';
					else $attr['item'] = $attr['item'];

					return '<?php foreach('.$attr['items'].' as '.$attr['key'].' => '.$attr['item'].'){?>';
				}
				case 'for:':
				{
					$attr = $this->getAttrs($matches[4]);
					if(!isset($attr['item'])) $attr['item'] = '$i';
					else $attr['item'] = $attr['item'];
					if(!isset($attr['from'])) $attr['from'] = 0;

                    if(!isset($attr['upto']) && !isset($attr['downto'])) $attr['upto'] = 10;
                    if(isset($attr['upto']))
                    {
                        $op = '<=';
                        $end = $attr['upto'];
                        if($attr['upto']<$attr['from']) $attr['upto'] = $attr['from'];
                        if(!isset($attr['step'])) $attr['step'] = 1;
                    }
                    else
                    {
                        $op = '>=';
                        $end = $attr['downto'];
                        if($attr['downto']>$attr['from'])$attr['downto'] = $attr['from'];
                        if(!isset($attr['step'])) $attr['step'] = -1;
                    }
					return '<?php for('.$attr['item'].' = '.$attr['from'].' ; '.$attr['item'].$op.$end.' ; '.$attr['item'].' = '.$attr['item'].'+'.$attr['step'].'){?>';
				}
				case 'query:':
				{
					$endchart=substr(trim($matches[4]),-1);
					$attrs = $this->getAttrs(rtrim($matches[4],'/'));
                    if(!isset($attrs['id']))
                    {
                    	$id = '$query';
                    }
                    else
                    {
                    	$id = $attrs['id'];
                    }

                    if(!isset($attrs['items']))
                    {
                    	$items = '$items';
                    }
                    else
                    {
                    	$items = $attrs['items'];
                    }
					$tem = "$id".' = new IQuery("'.$attrs['name'].'");';
					//实现属性中符号表达式的问题
					$old_char=array(' eq ',' l ',' g ',' le ',' ge ', ' neq ');
					$new_char=array(' = ' ,' < ',' > ',' <= ',' >= ', ' !=  ');
					foreach($attrs as $k => $v)
					{
						if($k != 'name' && $k != 'id' && $k != 'items' && $k != 'item')
						{
							$v    = preg_replace('%(\$\w+\->\w+\[[\'|\"]\w+[\'|\"]\])%','{$1}',$v);//对变量处理增加花括号
							$tem .= "{$id}->".$k.' = "'.str_replace($old_char,$new_char,$v).'";';
						}
					}
					$tem .= $items.' = '.$id.'->find();';
					if(!isset($attrs['key']))
					{
						$attrs['key'] = '$key';
					}
					else
					{
						$attrs['key'] = $attrs['key'];
					}
					if(!isset($attrs['item']))
					{
						$attrs['item'] = '$item';
					}
					else
					{
						$attrs['item'] = $attrs['item'];
					}
					if($endchart=='/')
					{
						return '<?php '.$tem.'?>';
					}
					else
					{
						return '<?php '.$tem.' foreach('.$items.' as '.$attrs['key'].' => '.$attrs['item'].'){?>';
					}
				}
				case 'file:':
				case 'include:':
				{
					$fileName = trim($matches[4],"/ ");
					return '<?php require(ITag::createRuntime("'.$fileName.'"));?>';
				}

				default:
				{
					 return $matches[0];
				}
			}
		}
		else
		{
			if($matches[2] =='code') return '?>';
			else return '<?php }?>';
		}
	}
    /**
     * @brief 分析标签属性
     * @param string $str
     * @return array以数组的形式返回属性值
     */
	public function getAttrs($str)
	{
		preg_match_all('/\w+\s*=(?:[^=]+?)(?=(\S+\s*=)|$)/i', trim($str), $attrs);
		$attr = array();
		foreach($attrs[0] as $value)
		{
			$tem = explode('=',$value);
			$attr[trim($tem[0])] = trim($tem[1]);
		}
		return $attr;
	}

    /**
     * @brief 变量替换操作
     * @param string $str
     * @return string
     */
	public function varReplace($str)
	{
		return preg_replace(array("#(\\$.*?(?=$|\/))#","#(\\$\w+)\[(\w+)\]#"),array("\".$1.\"","$1['$2']"),$str);
	}
	/**
	 * @brief 根据模板文件生成可以执行的PHP脚本
	 * @param string $fileParam 文件参数
	 * @param boolean $isLayout 模板是否带有布局
	 */
	public static function createRuntime($fileParam,$isLayout = false)
	{
		
		$pathInfo = explode("/",$fileParam);
		switch(count($pathInfo))
		{
			case 1:
			{
				$ctrlId   = IWeb::$app->getController()->getId();
				$actionId = $pathInfo[0];
			}
			break;

			case 2:
			{
				$ctrlId   = $pathInfo[0];
				$actionId = $pathInfo[1];
			}
			break;

			default:
			{
				throw new IException("模板标签 【include】或【file】 参数路径不规范");
			}
		}

		$ctrlObj_ord = IWeb::$app->getController();	// 旧的实例

		$ctrlObj = IWeb::$app->createController($ctrlId);
		$ctrlObj->user = $ctrlObj_ord->user;	// 附加旧的信息
		// $ctrlObj->init();


		// print_r($ctrlObj_ord->user);
		// $ctrlObj = new merge_objs($ctrlObj, $ctrlObj_ord);
		// $ctrlObj = new merge_objs($ctrlObj_ord, $ctrlObj);

		if($isLayout == false)
		{
			$ctrlObj->layout = "";
		}
		$actionObj = new IViewAction($ctrlObj,$actionId);
		$viewFile  = $actionObj->resolveView();
		if(is_file($viewFile.$ctrlObj->extend))
		{
			return $ctrlObj->render($viewFile,null,true);
		}
		else
		{
			throw new IException("模板标签 【include】或【file】 路径 {$fileParam} 不存在");
		}
	}


    /**
     * @brief 解析内置节点标签 目前主要是{theme:}{skin:}路径
     * @param string $str
     * @return string
     */
	public function getAttrReplace($content)
	{
		$pattern = '/<(link|script|img)\s+(.+?)\s*\>/i';// <(link|script)\s+(.+?)\s*?(\/|<\/script)\>
		$find = preg_match_all($pattern,$content, $matches);
		while (--$find >= 0) {
			
            $newTag = $this->parseAttrsTotag($matches[2][$find],$matches[1][$find]);
			$content = str_replace($matches[0][$find],$newTag,$content);
        }	
		
		return $content;
	}
	public function viewAutoPath($file){
		//$curDir = dirname($this->viewPath);
		$file = str_replace("../","",$file);
		return "{theme:" . $file ."}";
	}
    /**
     * @brief 解析标签属性
     * @param string $str
     * @return 返回替换值
     */
	public function parseAttrsTotag($str,$strtype)
	{
		$attr = array();
		if(!strstr(trim($str), '://') && !strstr(trim($str), '{') && !strstr(trim($str), '+')){
			preg_match_all('/\w+\s*=(?:[^=]+?)(?=(\S+\s*=)|$)/i', trim($str), $attrs);

			foreach($attrs[0] as $value)
			{	
				$tem = explode('=',$value);
				$tmpValue = str_replace("'","",$tem[1]);
				$tmpValue = str_replace('"',"",$tmpValue);
				$tmpValue = trim($tmpValue);
				switch(strtolower(trim($tem[0])))
				{
					case 'href':
					{
						$attr[$tem[0]] = '"' . $this->viewAutoPath($tmpValue) . '"';
						break;
					}
					case 'src':
					{
						$attr[$tem[0]] = '"' . $this->viewAutoPath($tmpValue) . '"';
						break;
					}
					default:
					{
						$attr[$tem[0]] = $tem[1];
					}
				}
			}
		}
		else
		{
			$attr['skin'] = '"skin" '.trim($str); 
		}
		$tag = '<'.$strtype;
		while(list($k,$v) = each($attr)){ 
			$tag .= ' '. $k .'='.$v; 
		}
		$tag .=">";
		return $tag;
	}

	
}

class merge_objs{
       private $classArr = array();
       function __construct() {
           $this ->classArr = func_get_args();
       }
        function __get($s) {
            foreach($this->classArr as $c) {
                if (property_exists($c, $s)) {
                    return $c -> $s;
                }
            }
        }
        function __call($fn,$args) {
            foreach($this->classArr as $c) {
                if (method_exists($c, $fn)) {
                    return call_user_func_array(array($c,$fn),$args);
                }
            }
        }
    }