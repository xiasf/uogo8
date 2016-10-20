<?php
/**
 * @file site.php
 * @brief
 * @note
 */
/**
 * @brief Site
 * @class Site
 * @note
 */
class Site_s extends IController
{
    public $layout = '';

	function init()
	{
		CheckRights::checkUserRights();
	}
}