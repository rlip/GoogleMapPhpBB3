<?php
/**
 *
 * @package phpBB Extension - Rlip Intereststree
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace rlip\intereststree\controller;

use Symfony\Component\Config\Definition\Exception\Exception;

class main
{
    const MAX_LEVEL = 16;
    const MAX_PROPOSAL_COUNTER = 10;

    /* @var \phpbb\config\config */
    protected $config;

    /* @var \phpbb\controller\helper */
    protected $helper;

    /* @var \phpbb\template\template */
    protected $template;

    /* @var \phpbb\user */
    protected $user;

    /* @var \phpbb\request */
    protected $request;

    /**
     * Constructor
     *
     * @param \phpbb\config\config $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\user $user
     */
    public function __construct(\phpbb\request\request $request, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \phpbb\exception\http_exception
     */
    public function setRate()
    {
        try {
            global $db, $user;
            $this->_isUser();

            $iInterestId = (int)$this->request->variable('id', 0);
            $iRate = (int)$this->request->variable('rate', 0);
            $this->_isInterest($iInterestId);
            $iUserId = (int)$user->data['user_id'];

            if (!$iRate) {
                $sSql = 'DELETE FROM ' . $this->_getTablePrefix() . 'inttree_user_has_interest';
                $sSql .= ' WHERE userhasinterest_user_id = ' . $iUserId . ' AND userhasinterest_interest_id = ' . $iInterestId;
            } else {
                $sSql = 'INSERT INTO ' . $this->_getTablePrefix() . 'inttree_user_has_interest (userhasinterest_user_id, userhasinterest_interest_id, userhasinterest_rate)';
                $sSql .= ' VALUES (' . $iUserId . ',' . $iInterestId . ',' . $iRate . ') ON DUPLICATE KEY UPDATE userhasinterest_rate=' . $iRate;
            }
            $db->sql_query($sSql);

            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => true,
            ));

        } catch (Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * @param $iInterestId
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _isInterest($iInterestId)
    {
        global $db;

        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_interest WHERE interest_id = ' . $iInterestId;
        $oInterestSelect = $db->sql_query($sSql);
        $aInterestRow = $db->sql_fetchrow($oInterestSelect);
        if (!$aInterestRow) {
            throw new Exception('Zainteresowanie nie zostało odnalezione');
        }
    }

    /**
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _isUser()
    {
        global $db, $user;
        $iUserId = (int)$user->data['user_id'];
        if (!$iUserId) {
            throw new Exception('Dostęp tylko dla zalogowanych!');
        }
        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'users WHERE user_id = ' . $iUserId;
        $oInterestSelect = $db->sql_query($sSql);
        $aInterestRow = $db->sql_fetchrow($oInterestSelect);
        if (!$aInterestRow) {
            throw new Exception('Użytkownik nie został odnaleziony');
        }
    }

    /**
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _getUserProposalCounter()
    {
        global $db, $user;
        $iUserId = (int)$user->data['user_id'];
        if (!$iUserId) {
            throw new Exception('Dostęp tylko dla zalogowanych!');
        }
        $sSql = 'SELECT count(*) as "proposal_counter" FROM ' . $this->_getTablePrefix() . 'inttree_proposal WHERE proposal_user_id = ' . $iUserId;
        $oSelect = $db->sql_query($sSql);
        $aRow = $db->sql_fetchrow($oSelect);
        return $aRow['proposal_counter'];
    }

    /**
     * Intereststree controller for route /intereststree/{name}
     *
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function tree()
    {
        try {
            $this->_isUser();

            $aInterests = $this->_getInterestsData();
            $aAllUsersData = $this->_getAllUserInterestsData();

            $aTree = array(
                'label' => 'Drzewo zainteresowań',
                'amount' => pow(2, self::MAX_LEVEL),
                'selectionAllowed' => 0,
                'level' => 0,
                'id' => 0,
                'usersCounter' => 0,
                'usersInNode' => array(),
                'children' => $this->_createTreeData($aInterests, 0, 1, $aAllUsersData)
            );

            $this->_getUsersCounter($aTree);

            $this->template->assign_var('INTERESTS_DATA', json_encode($aTree));
            return $this->helper->render('intereststree_body.html', 'Drzewo zainteresowań');
        } catch (Exception $e) {
            throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
        }
    }

    /**
     * @param array $aTree
     */
    protected function _getUsersCounter(array &$aTree)
    {
        $aUsersInNodeWithChildren = $aTree['usersInNode'];
        if (!isset($aTree['children'])) {
            $aTree['usersInNodeWithChildren'] = $aUsersInNodeWithChildren;
            $aTree['usersInNodeWithChildrenCounter'] = count($aUsersInNodeWithChildren);
            return $aUsersInNodeWithChildren;
        }
        foreach ($aTree['children'] as &$aChild) {
            $aChildUsers = $this->_getUsersCounter($aChild);
            $aUsersInNodeWithChildren = array_merge($aUsersInNodeWithChildren, $aChildUsers);
        }
        $aUsersInNodeWithChildrenUnique = array_unique($aUsersInNodeWithChildren);
        $aTree['usersInNodeWithChildren'] = $aUsersInNodeWithChildrenUnique;
        $aTree['usersInNodeWithChildrenCounter'] = count($aUsersInNodeWithChildrenUnique);
        return $aUsersInNodeWithChildrenUnique;
    }

    /**
     * @return array
     */
    protected function _getCurrentUserInterestsData()
    {
        global $db, $user;
        $iUserId = (int)$user->data['user_id'];
        $sSql = 'SELECT userhasinterest_interest_id, userhasinterest_rate FROM ' . $this->_getTablePrefix() . 'inttree_user_has_interest WHERE userhasinterest_user_id = ' . $iUserId;
        $oUserHasInterestSelect = $db->sql_query($sSql);
        $aData = array();
        while ($aRow = $db->sql_fetchrow($oUserHasInterestSelect)) {
            $aData[$aRow['userhasinterest_interest_id']] = $aRow['userhasinterest_rate'];
        }
        return $aData;
    }

    /**
     * @return array
     */
    protected function _getAllUserInterestsData()
    {
        global $db;
        $sSql = 'SELECT userhasinterest_interest_id, userhasinterest_user_id FROM ' . $this->_getTablePrefix() . 'inttree_user_has_interest';
        $oUserHasInterestSelect = $db->sql_query($sSql);
        $aData = array();
        while ($aRow = $db->sql_fetchrow($oUserHasInterestSelect)) {
            if (!isset($aData[$aRow['userhasinterest_interest_id']])) {
                $aData[$aRow['userhasinterest_interest_id']] = array();
            }
            $aData[$aRow['userhasinterest_interest_id']][] = $aRow['userhasinterest_user_id'];
        }
        return $aData;
    }

    /**
     * @return string
     */
    protected function _getTablePrefix()
    {
        return explode('_', PROFILE_FIELDS_DATA_TABLE)[0] . '_';
    }

    /**
     * @return array
     */
    protected function _getInterestsData()
    {
        global $db;

        $aCurrentUserInterests = $this->_getCurrentUserInterestsData();

        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_interest';
        $oInterests = $db->sql_query($sSql);
        $aInterests = array();

        while ($aRow = $db->sql_fetchrow($oInterests)) {
            $iParentId = (int)$aRow['interest_parent_id'];
            if (!isset($aInterests[$iParentId])) {
                $aInterests[$iParentId] = array();
            }
            $aRow['rate'] = isset($aCurrentUserInterests[$aRow['interest_id']]) ? $aCurrentUserInterests[$aRow['interest_id']] : 0;
            $aInterests[$iParentId][] = $aRow;
        }
        return $aInterests;
    }

    /**
     * @param array $aResult
     * @param $iParentId
     * @param $iLevel
     * @param array $aAllUsersData
     * @return array
     */
    protected function _createTreeData(array &$aResult, $iParentId, $iLevel, array &$aAllUsersData)
    {
        $aCurrentData = array();
        if (!isset($aResult[$iParentId])) {
            return $aCurrentData;
        }
        foreach ($aResult[$iParentId] as $aChildData) {
            $aNodeChildData = array(
                'label' => $aChildData['interest_title'],
                'id' => $aChildData['interest_id'],
                'selectionAllowed' => (int)$aChildData['interest_selection_allowed'],
                'amount' => pow(2, self::MAX_LEVEL - ($iLevel * 2)),
                'rate' => $aChildData['rate'],
                'usersInNode' => isset($aAllUsersData[$aChildData['interest_id']]) ? $aAllUsersData[$aChildData['interest_id']] : array()
            );
            $aNodeChildChildData = $this->_createTreeData($aResult, $aChildData['interest_id'], $iLevel + 1, $aAllUsersData);
            if (!empty($aNodeChildChildData)) {
                $aNodeChildData['children'] = $aNodeChildChildData;
            }
            $aCurrentData[] = $aNodeChildData;
        }
        return $aCurrentData;
    }

    public function addProposal()
    {
        try {
            global $db, $user;
            $iInterestId = (int)$this->request->variable('id', 0);
            $sProposal = trim($this->request->variable('proposal', ''));

            $this->_isUser();
            if ($iInterestId != 0) {
                $this->_isInterest($iInterestId);
            } else {
                $iInterestId = 'NULL';
            }
            if (!$sProposal) {
                throw new Exception('Propozycja nie może być pusta');
            }
            $sProposal = substr($sProposal, 0, 511);
            $iProposalCounter = $this->_getUserProposalCounter();
            if ($iProposalCounter >= self::MAX_PROPOSAL_COUNTER) {
                throw new Exception('Dopóki nie zostaną rozpatrzone Twoje poprzednie propozycje nie możesz składać nowych :)');
            }
            $iUserId = (int)$user->data['user_id'];
            $sProposal = mysql_real_escape_string(strip_tags($sProposal));
            $sSql = 'INSERT INTO ' . $this->_getTablePrefix() . 'inttree_proposal (proposal_user_id, proposal_interest_id, proposal_text, proposal_created_at)';
            $sSql .= ' VALUES (' . $iUserId . ',' . $iInterestId . ',"' . $sProposal . '", "' . date('Y-m-d H:i:s', time()) . '")';
            $db->sql_query($sSql);

            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => true,
                'message' => 'Propozycja (' . ($iProposalCounter + 1) . ' z ' . self::MAX_PROPOSAL_COUNTER . ' możliwych) została wysłana'
            ));

        } catch (Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    public function getProposalContainer()
    {
        try {
            $iInterestId = (int)$this->request->variable('id', 0);
            if ($iInterestId != 0) {
                $this->_isInterest($iInterestId);
            }

            $this->template->assign_var('PROPOSAL_ROWS', $this->_getProposalTbody($iInterestId));
            return $this->helper->render('proposals.html');
        } catch (Exception $e) {
            throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
        }
    }

    /**
     * @return array
     */
    protected function _getProposalTbody($iInterestId)
    {
        global $db;
        $sSubSelPlus = '(SELECT count(*) FROM ' . $this->_getTablePrefix() . 'inttree_proposal_vote ';
        $sSubSelPlus .= ' WHERE proposalvote_value = 1 AND proposalvote_proposal_id = proposal_id) as sum_plus';
        $sSubSelMinus = '(SELECT count(*) FROM ' . $this->_getTablePrefix() . 'inttree_proposal_vote ';
        $sSubSelMinus .= ' WHERE proposalvote_value = -1 AND proposalvote_proposal_id = proposal_id) as sum_minus';

        $sSql = 'SELECT proposal_id, proposal_text, proposal_created_at, username, proposalvote_value, ' . $sSubSelPlus . ', ' . $sSubSelMinus . ' FROM ' . $this->_getTablePrefix() . 'inttree_proposal';
        $sSql .= ' INNER JOIN ' . USERS_TABLE . ' on proposal_user_id = user_id';
        $sSql .= ' LEFT JOIN ' . $this->_getTablePrefix() . 'inttree_proposal_vote on proposalvote_proposal_id = proposal_id';
        $sSql .= ' WHERE proposal_interest_id ' . (($iInterestId == 0) ? 'IS NULL' : '= ' . $iInterestId);
        $oUserHasInterestSelect = $db->sql_query($sSql);

        $sReturn = '';
        while ($aRow = $db->sql_fetchrow($oUserHasInterestSelect)) {
            $sVotedClass = ($aRow['proposalvote_value'] == 1) ? 'voted-plus' : (($aRow['proposalvote_value'] == -1) ? 'voted-minus' : 'no-voted');
            $sReturn .= '<div class="row" data-proposal-id="' . $aRow['proposal_id'] . '">';
            $sReturn .= '<div class="cell-content">' . $aRow['proposal_text'] . '</div>';
            $sReturn .= '<div class="cell-user">';
            $sReturn .= '<div class="username">' . $aRow['username'] . '</div>';
            $sReturn .= '<div class="created-at">' . $aRow['proposal_created_at'] . '</div>';
            $sReturn .= '<div class="vote ' . $sVotedClass . '">';
            $sReturn .= '<span class="vote-plus">+' . $aRow['sum_plus'] . '</span> <span class="vote-minus">-' . $aRow['sum_minus'] . '</span>';
            $sReturn .= ' <button onclick="intTree.onProposalVote(this, 1)">+</button> <button onclick="intTree.onProposalVote(this, 0)">-</button></div>';
            $sReturn .= '</div>';
            $sReturn .= '</div>';
        }
        return $sReturn;
    }

    protected function _isProposal($iProposalId)
    {
        global $db;

        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_proposal WHERE proposal_id = ' . $iProposalId;
        $oSelect = $db->sql_query($sSql);
        $aRow = $db->sql_fetchrow($oSelect);
        if (!$aRow) {
            throw new Exception('Propozycja została odnaleziona');
        }
    }

    public function proposalVote()
    {
        try {
            global $db, $user;
            $iProposalId = (int)$this->request->variable('proposal_id', 0);
            $iIsPlus = (int)$this->request->variable('is_plus', 1);

            $this->_isProposal($iProposalId);
            $this->_isUser();
            $iUserId = (int)$user->data['user_id'];
            $iValue = $iIsPlus ? 1 : -1;

            $sSql = 'INSERT INTO ' . $this->_getTablePrefix() . 'inttree_proposal_vote (proposalvote_user_id, proposalvote_proposal_id, proposalvote_value)';
            $sSql .= ' VALUES (' . $iUserId . ',' . $iProposalId . ',' . $iValue . ') ON DUPLICATE KEY UPDATE proposalvote_value=' . $iValue;
            $db->sql_query($sSql);

            $sSql = 'SELECT count(*) as sum_plus FROM ' . $this->_getTablePrefix() . 'inttree_proposal_vote ';
            $sSql .= ' WHERE proposalvote_value = 1 AND proposalvote_proposal_id = ' . $iProposalId;
            $oSelect = $db->sql_query($sSql);
            $aRow = $db->sql_fetchrow($oSelect);
            $iSumPlus = $aRow['sum_plus'];

            $sSql = 'SELECT count(*) as sum_minus FROM ' . $this->_getTablePrefix() . 'inttree_proposal_vote ';
            $sSql .= ' WHERE proposalvote_value = -1 AND proposalvote_proposal_id = ' . $iProposalId;
            $oSelect = $db->sql_query($sSql);
            $aRow = $db->sql_fetchrow($oSelect);
            $iSumMinus = $aRow['sum_minus'];

            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => true,
                'count_plus' => $iSumPlus,
                'count_minus' => $iSumMinus
            ));

        } catch (Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }
}
