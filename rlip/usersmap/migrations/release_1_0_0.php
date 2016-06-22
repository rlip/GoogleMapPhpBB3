<?php
/**
 *
 * @package phpBB Extension - Rlip Usersmap
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rlip\usersmap\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return isset($this->config['rlip_usersmap_js_key']);
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v310\alpha2');
    }

    public function update_data()
    {
        return array(
            array('config.add', array('rlip_usersmap_js_key', '')),
            array('config.add', array('rlip_usersmap_server_key', '')),

            array('module.add', array(
                'acp',
                'ACP_CAT_DOT_MODS',
                'RLIP_USERSMAP_MODULE_NAME'
            )),
            array('module.add', array(
                'acp',
                'RLIP_USERSMAP_MODULE_NAME',
                array(
                    'module_basename' => '\rlip\usersmap\acp\main_module',
                    'modes' => array('settings'),
                ),
            )),
        );
    }
}
