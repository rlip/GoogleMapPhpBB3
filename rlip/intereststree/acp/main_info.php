<?php
/**
*
* @package phpBB Extension - Rlip Intereststree
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rlip\intereststree\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\rlip\intereststree\acp\main_module',
			'title'		=> 'INTERESTS_TREE_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'INTERESTS_TREE_TITLE',
					'auth'	=> 'ext_rlip/intereststree && acl_a_board',
					'cat'	=> array('INTERESTS_TREE_TITLE')
				),
			),
		);
	}
}
