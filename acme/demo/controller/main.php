<?php
/**
 *
 * @package phpBB Extension - Acme Demo
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace acme\demo\controller;

class main
{
    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;

    /**
     * Constructor
     *
     * @param \phpbb\config\config $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\user $user
     */
    public function __construct(\phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
    }

    /**
     * Demo controller for route /demo/{name}
     *
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle($name)
    {
        global $db, $user;

        $sql = 'SELECT username, pf_postal_code
			FROM ' . PROFILE_FIELDS_DATA_TABLE . ' data_tab
			INNER JOIN ' . USERS_TABLE . ' users_tab on data_tab.user_id = users_tab.user_id
			WHERE pf_postal_code IS NOT NULL';
        $result = $db->sql_query($sql);
        $aData = array();
        while ($row = $db->sql_fetchrow($result)) {
            $sCode =  preg_replace('/[^0-9\-]/', '', $row['pf_postal_code']);
            if(strlen($sCode) != 6){
                continue;
            }
            $sUrl = 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyD8PmCFAu-YX0KbsBW0ipItmGeUUYQcgKw&address='. $sCode;
            $json = file_get_contents($sUrl);
            $json = json_decode($json);
            if(empty($json->results)){
                continue;
            }
            $location = $json->results[0]->geometry->location;
            $aData[] = array(
                'username' => $row['username'],
                'location' => $location
            );
        }
        $this->template->assign_var('USERS_DATA', json_encode($aData));
        return $this->helper->render('demo_body.html', $name);
    }
}
