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
        $i2MonthsBefore = time() - (60 * 60 * 24 * 30 * 2);
        $sql = 'SELECT username, pf_postal_code, latitude, longitude
			FROM ' . PROFILE_FIELDS_DATA_TABLE . ' data_tab
			INNER JOIN ' . USERS_TABLE . ' users_tab on data_tab.user_id = users_tab.user_id
			LEFT JOIN phpbb_postal_code_location location_tab on location_tab.postal_code = data_tab.pf_postal_code
			WHERE pf_postal_code IS NOT NULL AND pf_postal_code != "" AND user_inactive_reason = 0 AND user_lastvisit > ' . $i2MonthsBefore . '
			GROUP BY username';
        //  @todo!
        $sql = 'SELECT username, pf_postal_code
			FROM ' . PROFILE_FIELDS_DATA_TABLE . ' data_tab
			INNER JOIN ' . USERS_TABLE . ' users_tab on data_tab.user_id = users_tab.user_id
			WHERE pf_postal_code IS NOT NULL AND pf_postal_code != "" AND user_inactive_reason = 0 AND user_lastvisit > ' . $i2MonthsBefore . '
			GROUP BY username';

        $result = $db->sql_query($sql);
        $aData = array();

        while ($row = $db->sql_fetchrow($result)) {
            $sCode = preg_replace('/[^0-9\-]/', '', $row['pf_postal_code']);
            if (strlen($sCode) != 6) {
                continue;
            }
            if (!isset($row['latitude']) || !isset($row['longitude']) || !$row['latitude'] || !$row['longitude']) {
                $sUrl = 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyCuVMJO4EO6d_zXVzm-V3_1-9c24TBU9Ps&address=' . $sCode . '%20Poland';
                $json = @file_get_contents($sUrl);
                if (!$json) {
                    continue;
                }
                $json = json_decode($json);
                if (empty($json->results)) {
                    continue;
                }
                $location = $json->results[0]->geometry->location;
                $fLatitude = $location->lat;
                $fLongitude = $location->lng;
                $sql = 'INSERT IGNORE INTO phpbb_postal_code_location ' . $db->sql_build_array('INSERT', array(
                        'postal_code' => $sCode,
                        'latitude' => $fLatitude,
                        'longitude' => $fLongitude
                    ));
//  @todo!              $db->sql_query($sql);
            } else {
                $fLatitude = floatval($row['latitude']);
                $fLongitude = floatval($row['longitude']);
            }
            $oLocation = new \stdClass();
            do {
                $sKey = 'loc_' . $fLatitude . '_' . $fLongitude;
                $oLocation->lat = $fLatitude;
                $oLocation->lng = $fLongitude;
                $fLongitude = $fLongitude + 0.05;
            } while (isset($aData[$sKey]));
            $aData[$sKey] = array(
                'username' => $row['username'],
                'location' => $oLocation
            );
        }
        $this->template->assign_var('USERS_DATA', json_encode(array_values($aData)));
        return $this->helper->render('demo_body.html', $name);
    }
}
