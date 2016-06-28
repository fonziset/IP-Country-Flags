<?php
/**
*
* @package phpBB Extension - IPCF 1.0.0-(IP Country Flag)
* @copyright (c) 2005 - 2008 - 2016 3Di (Marco T.)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace threedi\ipcf;

/**
* @ignore
*/

class ext extends \phpbb\extension\base
{
	public function is_enableable()
	{
		$config = $this->container->get('config');
		return version_compare($config['version'], '3.1.9', '>=');
	}
}
