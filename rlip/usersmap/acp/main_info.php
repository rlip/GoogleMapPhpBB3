<?php
/**
*
* @package phpBB Extension - Rlip Usersmap
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rlip\usersmap\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\rlip\usersmap\acp\main_module',
			'title'		=> 'RLIP_USERSMAP_MODULE_NAME',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'RLIP_USERSMAP_SETTINGS_TITLE',
					'auth'	=> 'ext_rlip/usersmap && acl_a_board',
					'cat'	=> array('RLIP_USERSMAP_MODULE_NAME')
				),
			),
		);
	}
}
