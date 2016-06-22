<?php
/**
 *
 * @package phpBB Extension - Rlip Usersmap
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rlip\usersmap\acp;

class main_module
{
    var $u_action;

    function main($id, $mode)
    {
        global $config, $request, $template, $user;

        $user->add_lang('acp/common');
        $this->tpl_name = 'settings';
        $this->page_title = $user->lang('RLIP_USERSMAP_MODULE_NAME');
        add_form_key('rlip/usersmap');

        if ($request->is_set_post('submit')) {
            if (!check_form_key('rlip/usersmap')) {
                trigger_error('FORM_INVALID');
            }
            $a= $request->variable('rlip_usersmap_server_key', 0);
            $config->set('rlip_usersmap_js_key', $request->variable('rlip_usersmap_js_key', ''));
            $config->set('rlip_usersmap_server_key', $request->variable('rlip_usersmap_server_key', ''));

            trigger_error($user->lang('RLIP_USERSMAP_SETTING_SAVED') . adm_back_link($this->u_action));
        }

        $template->assign_vars(array(
            'U_ACTION' => $this->u_action,
            'RLIP_USERSMAP_JS_KEY' => $config['rlip_usersmap_js_key'],
            'RLIP_USERSMAP_SERVER_KEY' => $config['rlip_usersmap_server_key'],
        ));
    }
}
