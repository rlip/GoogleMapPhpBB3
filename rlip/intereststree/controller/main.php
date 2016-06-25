<?php
/**
 *
 * @package phpBB Extension - Rlip Intereststree
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rlip\intereststree\controller;

class main
{
    const MAX_LEVEL = 16;

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
     * Intereststree controller for route /intereststree/{name}
     *
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function handle($name)
    {
        global $db, $user;
        $aUserHasInterests = $this->_getUserHasInterestsData();
        $aInterests = $this->_getInterestsData();
        $aTree = array(
            'label' => 'Drzewo zainteresowań',
            'amount' => pow(2, self::MAX_LEVEL),
            'children' => $this->_createTreeData($aInterests, 0, 1)
        );
        $this->template->assign_var('INTERESTS_DATA', json_encode($aTree));
        return $this->helper->render('intereststree_body.html', $name);
    }

    protected function _getUserHasInterestsData(){
        $sPrefix = explode('_', PROFILE_FIELDS_DATA_TABLE)[0] . '_';
        $sSql = 'SELECT * FROM ' . $sPrefix . 'user_has_interest';
    }

    protected function _getInterestsData(){
        global $db;

        $sPrefix = explode('_', PROFILE_FIELDS_DATA_TABLE)[0] . '_';
        $sSql = 'SELECT * FROM ' . $sPrefix . 'interest';
        $oInterests = $db->sql_query($sSql);
        $aInterests = array();

        while ($aRow = $db->sql_fetchrow($oInterests)) {
            $iParentId = (int)$aRow['interest_parent_id'];
            if (!isset($aInterests[$iParentId])) {
                $aInterests[$iParentId] = array();
            }
            $aInterests[$iParentId][] = $aRow;
        }
        return $aInterests;
    }

    protected function _createTreeData(&$aResult, $iParentId, $iLevel)
    {
        $aCurrentData = array();
        if (!isset($aResult[$iParentId])) {
            return $aCurrentData;
        }
        foreach ($aResult[$iParentId] as $aChildData) {
            $aNodeChildData = array(
                'label' => $aChildData['interest_title'],
                'id' => $aChildData['interest_id'],
                'selection_allowed' => $aChildData['interest_selection_allowed'],
                'amount' => pow(2, self::MAX_LEVEL - ($iLevel * 2))
            );
            $aNodeChildChildData = $this->_createTreeData($aResult, $aChildData['interest_id'], $iLevel + 1);
            if (!empty($aNodeChildChildData)) {
                $aNodeChildData['children'] = $aNodeChildChildData;
            }
            $aCurrentData[] = $aNodeChildData;
        }
        return $aCurrentData;
    }
}
