<?php
/**
 * @file report.php
 * @brief 导出excel类库
 * @version 1.0.0
 */
class report
{
	//文件名
	private $fileName = 'user';

	//构造函数
	public function __construct($fileName = '')
	{
		$this->setFileName($fileName);
	}

	//设置要导出的文件名
	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
	}

	//开始下载
	public function toDownload($strTable)
	{
		header("Content-type: application/vnd.ms-excel");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename=".$this->fileName."_".date('Y-m-d').".xls");
		header('Expires:0');
		header('Pragma:public');
		echo '<html><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.$strTable.'</html>';
	}
}