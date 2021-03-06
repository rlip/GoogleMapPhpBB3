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
            array('custom', array(array($this, 'create_tables'))),
        );
    }
    public function create_tables()
    {
        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "postal_code_location` (" .
            " `id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `postal_code` VARCHAR(6) NOT NULL," .
            " `latitude` DECIMAL(10,7) NOT NULL," .
            " `longitude` DECIMAL(10,7) NOT NULL," .
            " PRIMARY KEY (`id`)," .
            " UNIQUE INDEX `postal_code_unique` (`postal_code`)," .
            " INDEX `postal_code` (`postal_code`)" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'postal_code_location',
            ),
        );
    }

}
