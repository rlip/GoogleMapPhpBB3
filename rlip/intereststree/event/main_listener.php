<?php
/**
 *
 * @package phpBB Extension - Rlip Intereststree
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rlip\intereststree\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */
class main_listener implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(
            'core.user_setup' => 'load_language_on_setup',
            'core.permissions' => 'permission_inttree',
            'core.page_header' => 'add_page_header_link',
        );
    }

    /* @var \phpbb\template\template */
    protected $template;
    /** @var \phpbb\db\driver\driver_interface */
    protected $db;
    /** @var \phpbb\user */
    protected $user;
    protected $root_path;
    protected $phpEx;
    /** @var \phpbb\auth\auth */
    protected $auth;
    /** @var \phpbb\controller\helper */
    protected $helper;

    /**
     * Constructor
     *
     * @param \phpbb\controller\helper $helper Controller helper object
     * @param \phpbb\template|\phpbb\template\template $template Template object
     * @param \phpbb\db\driver\driver_interface $db
     * @param \phpbb\user $user
     * @param $root_path
     * @param $phpEx
     * @param \phpbb\auth\auth $auth
     */
    public function __construct(\phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, $root_path, $phpEx, \phpbb\auth\auth $auth)
    {
        $this->helper = $helper;
        $this->template = $template;
        $this->db = $db;
        $this->user = $user;
        $this->root_path = $root_path;
        $this->phpEx = $phpEx;
        $this->auth = $auth;
    }

    public function load_language_on_setup($event)
    {
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = array(
            'ext_name' => 'rlip/intereststree',
            'lang_set' => 'common',
        );
        $event['lang_set_ext'] = $lang_set_ext;
    }

    /**
     * Czy ma uprawnienia do modyfikacji drzewa i akceptowania propozycji
     */
    protected function _hasModPermission(){
        global $auth;
        return $auth->acl_get('m_inttree');
    }


    public function add_page_header_link($event)
    {
        $this->template->assign_vars(array(
            'U_INTERESTSTREE_PAGE' => $this->helper->route('rlip_intereststree_controller_tree'),
            'U_INTERESTSTREE_MANAGE_PAGE' => $this->helper->route('rlip_intereststree_controller_treemanage'),
            'HAS_MOD_PERMISSION' => $this->_hasModPermission()
        ));
    }

    public function permission_inttree($event)
    {
        $permissions = $event['permissions'];
        $permissions['m_inttree'] = array('lang' => 'RLIP_INTERESTS_TREE_PROPOSAL_ACL_MANAGE', 'cat' => 'misc');
        $event['permissions'] = $permissions;
    }
}
