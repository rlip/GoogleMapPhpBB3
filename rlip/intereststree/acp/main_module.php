<?php
/**
 *
 * @package phpBB Extension - Rlip Intereststree
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rlip\intereststree\acp;

class main_module
{
    var $u_action;

    function main($id, $mode)
    {
        global $config, $request, $template, $user;
        $user->add_lang('acp/common');
        $this->tpl_name = 'intereststree_body';
        $this->page_title = $user->lang('ACP_INTERESTSTREE_TITLE');
        add_form_key('rlip/intereststree');
        if ($request->is_set_post('submit')) {
            if (!check_form_key('rlip/intereststree')) {
                trigger_error('FORM_INVALID');
            }
            $config->set('rlip_intereststree_goodbye', $request->variable('rlip_intereststree_goodbye', 0));
            trigger_error($user->lang('ACP_INTERESTSTREE_SETTING_SAVED') . adm_back_link($this->u_action));
        }
        $template->assign_vars(array(
            'U_ACTION' => $this->u_action,
            'RLIP_INTERESTSTREE_GOODBYE' => $config['rlip_intereststree_goodbye'],
        ));
    }
}
