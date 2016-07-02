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
    const SUM_TO_PROPOSAL_DELETE = -10;

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

    /* @var \phpbb\notification\manager */
    protected $notification_manager;

    /**
     * Constructor
     *
     * @param \phpbb\request\request $request
     * @param \phpbb\config\config $config
     * @param \phpbb\controller\helper $helper
     * @param \phpbb\template\template $template
     * @param \phpbb\user $user
     * @param \phpbb\notification\manager $notification_manager
     */
    public function __construct(\phpbb\request\request $request, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\user $user, \phpbb\notification\manager $notification_manager)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->template = $template;
        $this->user = $user;
        $this->request = $request;
        $this->notification_manager = $notification_manager;
    }

    /**
     * Ustawia poziom zainteresowania
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
     * Czy istnieje zainteresowanie o podanym id
     * @param $iInterestId
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _isInterest($iInterestId)
    {
        global $db;

        $sSql = 'SELECT interest_id FROM ' . $this->_getTablePrefix() . 'inttree_interest WHERE interest_id = ' . $iInterestId;
        $oInterestSelect = $db->sql_query($sSql);
        $aInterestRow = $db->sql_fetchrow($oInterestSelect);
        if (!$aInterestRow) {
            throw new Exception('Zainteresowanie nie zostało odnalezione');
        }
    }

    /**
     * Zwraca zainteresowanie o podanym id
     * @param $iInterestId
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _getInterestById($iInterestId)
    {
        global $db;

        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_interest WHERE interest_id = ' . $iInterestId;
        $oInterestSelect = $db->sql_query($sSql);
        return $db->sql_fetchrow($oInterestSelect);
    }

    /**
     * Czy zalogowano
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _isUser()
    {
        global $db, $user;
        $iUserId = (int)$user->data['user_id'];
        if (!$iUserId) {
            throw new Exception('Dostęp tylko dla zalogowanych!');
        }
        $sSql = 'SELECT user_id FROM ' . $this->_getTablePrefix() . 'users WHERE user_id = ' . $iUserId;
        $oSelect = $db->sql_query($sSql);
        $aRow = $db->sql_fetchrow($oSelect);
        if (!$aRow) {
            throw new Exception('Użytkownik nie został odnaleziony');
        }
    }

    /**
     * Zwraca liczbę propozycji zalogowanego użytkownika
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
     * Rysuje drzewo
     * @throws \phpbb\exception\http_exception
     * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
     */
    public function tree()
    {
        try {
            $this->_isUser();

            $aInterests = $this->_getInterestsWithRate();
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
            return $this->helper->render('intereststree.html', 'Drzewo zainteresowań');
        } catch (Exception $e) {
            throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
        }
    }

    /**
     * Zwraca tablicę id użytkowników mających zainteresowanie w danym nodzie
     * @param array $aTree
     * @return array
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
     * Zwraca zainteresowania zalogowanego użytkownika
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
     * Zwraca zainteresowania wszystkich użytkowników
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
     * Zwraca prefix tabel
     * @return string
     */
    protected function _getTablePrefix()
    {
        return explode('_', PROFILE_FIELDS_DATA_TABLE)[0] . '_';
    }

    /**
     * Zwraca dane zainteresowań
     * @return array
     */
    protected function _getInterestsWithRate()
    {
        global $db;

        $aCurrentUserInterests = $this->_getCurrentUserInterestsData();

        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_interest';
        $oInterests = $db->sql_query($sSql);
        $aInterestsData = array();

        while ($aRow = $db->sql_fetchrow($oInterests)) {
            $iParentId = (int)$aRow['interest_parent_id'];
            if (!isset($aInterestsData[$iParentId])) {
                $aInterestsData[$iParentId] = array();
            }
            $aRow['rate'] = isset($aCurrentUserInterests[$aRow['interest_id']]) ? $aCurrentUserInterests[$aRow['interest_id']] : 0;
            $aInterestsData[$iParentId][] = $aRow;
        }
        return $aInterestsData;
    }

    /**
     * Tworzy drzewo zainteresowań
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

    /**
     * Dodaje propozycję zmian
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addProposal()
    {
        try {
            global $db, $user;
            $iInterestId = (int)$this->request->variable('id', 0);
            $sProposal = $this->request->variable('proposal', '', true);

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
            $sProposal = $db->sql_escape(strip_tags($sProposal));
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

    /**
     * Zwraca html propozycji zmian
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \phpbb\exception\http_exception
     */
    public function getProposalContainer()
    {
        try {
            $this->_isUser();
            $iInterestId = (int)$this->request->variable('id', 0);
            if ($iInterestId != 0) {
                $this->_isInterest($iInterestId);
            }
            $this->template->assign_var('PROPOSAL_ROWS', $this->_getProposalRows($iInterestId));
            return $this->helper->render('proposals.html');
        } catch (Exception $e) {
            throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
        }
    }

    /**
     * Zwraca html listy użytkowników
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \phpbb\exception\http_exception
     */
    public function getUserContainer()
    {
        try {
            $iInterestId = (int)$this->request->variable('id', 0);
            $this->template->assign_var('USER_ROWS', $this->_getUserRows($iInterestId));
            return $this->helper->render('users.html');
        } catch (Exception $e) {
            throw new \phpbb\exception\http_exception(500, 'GENERAL_ERROR');
        }
    }

    /**
     * Zwraca zainteresowania w postaciach do szukania rodziców i do szukania dzieci
     * @return array
     */
    protected function _getInterestsBoth()
    {
        global $db;
        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_interest';
        $oInterests = $db->sql_query($sSql);
        $aInterestsForUp = array();
        $aInterestsForDown = array();
        while ($aRow = $db->sql_fetchrow($oInterests)) {
            $aInterestsForUp[$aRow['interest_id']] = $aRow;
            $iParentId = (int)$aRow['interest_parent_id'];
            if (!isset($aInterestsForDown[$iParentId])) {
                $aInterestsForDown[$iParentId] = array();
            }
            $aInterestsForDown[$iParentId][] = $aRow;
        }
        return array($aInterestsForUp, $aInterestsForDown);
    }

    /**
     * Zwraca wszystkie idiki dzieci $iInterestId oraz $iInterestId
     * @param $iInterestId
     * @param $aInterestsForDown
     * @return array
     */
    protected function _getInterestChildrenIds($iInterestId, &$aInterestsForDown)
    {
        $aReturn = array($iInterestId);
        if (isset($aInterestsForDown[$iInterestId])) {
            foreach ($aInterestsForDown[$iInterestId] as $aChild) {
                $aChildData = $this->_getInterestChildrenIds($aChild['interest_id'], $aInterestsForDown);
                $aReturn = array_merge($aReturn, $aChildData);
            }
        }
        return $aReturn;
    }

    /**
     * Zwraca html propozycji
     * @param $iInterestId
     * @return array
     */
    protected function _getProposalRows($iInterestId)
    {
        global $db, $user;
        $iUserId = (int)$user->data['user_id'];
        list($aInterestsForUp, $aInterestsForDown) = $this->_getInterestsBoth();

        $sSubSelPlus = '(SELECT count(*) FROM ' . $this->_getTablePrefix() . 'inttree_proposal_vote ';
        $sSubSelPlus .= ' WHERE proposalvote_value = 1 AND proposalvote_proposal_id = proposal_id) as sum_plus';
        $sSubSelMinus = '(SELECT count(*) FROM ' . $this->_getTablePrefix() . 'inttree_proposal_vote ';
        $sSubSelMinus .= ' WHERE proposalvote_value = -1 AND proposalvote_proposal_id = proposal_id) as sum_minus';

        $sSql = 'SELECT proposal_id, proposal_interest_id, proposal_text, proposal_created_at, username, proposalvote_value, proposal_interest_id,' . $sSubSelPlus . ', ' . $sSubSelMinus . ' FROM ' . $this->_getTablePrefix() . 'inttree_proposal';
        $sSql .= ' INNER JOIN ' . USERS_TABLE . ' on proposal_user_id = user_id';
        $sSql .= ' LEFT JOIN ' . $this->_getTablePrefix() . 'inttree_proposal_vote on proposalvote_proposal_id = proposal_id AND proposalvote_user_id = ' . $iUserId;
        if ($iInterestId) {
            $aInterestsChildrenIds = $this->_getInterestChildrenIds($iInterestId, $aInterestsForDown);
            $sSql .= ' WHERE proposal_interest_id IN (' . implode(',', $aInterestsChildrenIds) . ')';
        }
        $oUserHasInterestSelect = $db->sql_query($sSql);

        $sReturn = '';
        $sManageButtons = '';
        while ($aRow = $db->sql_fetchrow($oUserHasInterestSelect)) {
            if($this->_hasModPermission()){
                $sManageButtons = '<div class="proposal-mod-action proposal-accept" onclick="intTree.onProposalAccept(this)"></div>';
                $sManageButtons .= '<div class="proposal-mod-action proposal-reject" onclick="intTree.onProposalReject(this)"></div>';
            }
            $sVotedClass = ($aRow['proposalvote_value'] == 1) ? 'voted-plus' : (($aRow['proposalvote_value'] == -1) ? 'voted-minus' : 'no-voted');
            $sReturn .= '<div class="row" data-proposal-id="' . $aRow['proposal_id'] . '">';
            $sReturn .= '<div class="cell-content">';
            $sReturn .= '<div class="interest-path">' .$sManageButtons . $this->_getInterestPatch($aRow['proposal_interest_id'], $aInterestsForUp) . '</div>';
            $sReturn .= '<div class="proposal-text">' . $aRow['proposal_text'] . '</div>';
            $sReturn .= '</div>';
            $sReturn .= '<div class="cell-user">';
            $sReturn .= '<div class="username"><a href="/memberlist.php?mode=viewprofile&un=' . $aRow['username'] . '">' . $aRow['username'] . '</a></div>';
            $sReturn .= '<div class="created-at">' . $aRow['proposal_created_at'] . '</div>';
            $sReturn .= '<div class="vote ' . $sVotedClass . '">';
            $sReturn .= '<span class="vote-plus">+' . $aRow['sum_plus'] . '</span> <span class="vote-minus">-' . $aRow['sum_minus'] . '</span>';
            $sReturn .= ' <button onclick="intTree.onProposalVote(this, 1)">+</button> <button onclick="intTree.onProposalVote(this, 0)">-</button></div>';
            $sReturn .= '</div>';
            $sReturn .= '</div>';
        }
        return $sReturn;
    }

    /**
     * Czy ma uprawnienia do modyfikacji drzewa i akceptowania propozycji
     */
    protected function _hasModPermission(){
        global $auth;
        return $auth->acl_get('m_inttree');
    }

    /**
     * Zwraca html użytkowników
     * @param $iInterestId
     * @return array
     */
    protected function _getUserRows($iInterestId)
    {
        global $db;
        $aInterest = $this->_getInterestById($iInterestId);
        if (!$aInterest || !$aInterest['interest_selection_allowed']) {
            return false;
        }
        list($aInterestsForUp, $aInterestsForDown) = $this->_getInterestsBoth();
        $iInterestChildrenIds = $this->_getInterestChildrenIds($iInterestId, $aInterestsForDown);

        $sSql = 'SELECT userhasinterest_user_id, userhasinterest_interest_id, userhasinterest_rate, username,';
        $sSql .= ' (userhasinterest_rate / (SELECT SUM(userhasinterest_rate) FROM phpbb_inttree_user_has_interest inner_tab WHERE inner_tab.userhasinterest_user_id = outer_tab.userhasinterest_user_id) * 100) AS percent';
        $sSql .= ' FROM ' . $this->_getTablePrefix() . 'inttree_user_has_interest outer_tab';
        $sSql .= ' INNER JOIN ' . USERS_TABLE . ' on userhasinterest_user_id = user_id';
        $sSql .= ' WHERE userhasinterest_interest_id IN(' . implode(',', $iInterestChildrenIds) . ')';
        $sSql .= ' ORDER BY percent DESC';
        $oUserHasInterestSelect = $db->sql_query($sSql);

        $sReturn = '';
        while ($aRow = $db->sql_fetchrow($oUserHasInterestSelect)) {
            $iPercent = round($aRow['percent']);
            $sReturn .= '<div class="row">';
            $sReturn .= '<div class="percent-vote"><div class="c100 p'.$iPercent.' micro orange"><span>'.$iPercent.'%</span><div class="slice"><div class="bar"></div><div class="fill"></div></div></div></div>';
            $sReturn .= '<div class="username"><a href="/memberlist.php?mode=viewprofile&un=' . $aRow['username']. '">' . $aRow['username'] . '</a></div>';
            $sReturn .= '<div class="star"><div class="rateit" data-rateit-value="' . $aRow['userhasinterest_rate'] . '" data-rateit-ispreset="true" data-rateit-readonly="true"></div></div>';
            $sReturn .= '<div class="interest-path">' . $this->_getInterestPatch($aRow['userhasinterest_interest_id'], $aInterestsForUp, $iInterestId) . '</div>';
            $sReturn .= '</div>';
        }
        return $sReturn;
    }

    /**
     * Zwraca ścieżkę zainteresowania
     * @param $iInterestId
     * @param $aInterests
     * @param int $iInterestIdMaxUp
     * @return string
     */
    protected function _getInterestPatch($iInterestId, $aInterests, $iInterestIdMaxUp = 0)
    {
        $aReturn = array();
        $iCurrentInterestId = $iInterestId;
        while ($iCurrentInterestId AND isset($aInterests[$iCurrentInterestId])) {
            $aReturn[] = $aInterests[$iCurrentInterestId]['interest_title'];
            if($iCurrentInterestId == $iInterestIdMaxUp){
                break;
            }
            $iCurrentInterestId = $aInterests[$iCurrentInterestId]['interest_parent_id'];
        }
        if ($iInterestIdMaxUp == 0) {
            $aReturn[] = 'Drzewo zainteresowań';
        }
        return implode(' -> ', array_reverse($aReturn));
    }

    /**
     * Czy propozycja jest w bazie
     * @param $iProposalId
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _isProposal($iProposalId)
    {
        global $db;

        $sSql = 'SELECT proposal_id FROM ' . $this->_getTablePrefix() . 'inttree_proposal WHERE proposal_id = ' . $iProposalId;
        $oSelect = $db->sql_query($sSql);
        $aRow = $db->sql_fetchrow($oSelect);
        if (!$aRow) {
            throw new Exception('Propozycja została odnaleziona');
        }
    }


    /**
     * Czy propozycja jest w bazie
     * @param $iProposalId
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function _getProposalById($iProposalId)
    {
        global $db;

        $sSql = 'SELECT * FROM ' . $this->_getTablePrefix() . 'inttree_proposal WHERE proposal_id = ' . $iProposalId;
        $oSelect = $db->sql_query($sSql);
        return $db->sql_fetchrow($oSelect);
    }

    /**
     * Oddanie głosu na propozycję
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
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

            if ($iSumPlus - $iSumMinus <= self::SUM_TO_PROPOSAL_DELETE) {
                $sSql = 'SELECT proposal_user_id FROM ' . $this->_getTablePrefix() . 'inttree_proposal';
                $sSql .= ' WHERE proposal_id = ' . $iProposalId;
                $oSelect = $db->sql_query($sSql);
                $aRow = $db->sql_fetchrow($oSelect);

                $sSql = 'DELETE FROM ' . $this->_getTablePrefix() . 'inttree_proposal where proposal_id = ' . $iProposalId;
                $db->sql_query($sSql);
                $this->_addRejectedNotification($aRow['proposal_user_id']);
                return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                    'success' => true,
                    'is_deleted' => true,
                ));
            } else {
                return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                    'success' => true,
                    'is_deleted' => false,
                    'count_plus' => $iSumPlus,
                    'count_minus' => $iSumMinus
                ));
            }

        } catch (Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }

    /**
     * Dodaje notyfikacje o odrzuceniu propozycji
     * @param $iToUserId
     */
    protected function _addRejectedNotification($iToUserId){
        $aNotificationData = array(
            'user_id'   => (int) $this->user->data['user_id'],
            'to_user_id'   => $iToUserId,
            'time'   => time(),
            'username'   => $this->user->data['username'],
        );
        $this->notification_manager->add_notifications(array(
            'rlip.intereststree.notification.type.inttree_proposal_rejected',
        ), $aNotificationData);
    }
    /**
     * Dodaje notyfikacje o akceptacji propozycji
     * @param $iToUserId
     */
    protected function _addAcceptedNotification($iToUserId){
        $aNotificationData = array(
            'user_id'   => (int) $this->user->data['user_id'],
            'to_user_id'   => $iToUserId,
            'time'   => time(),
            'username'   => $this->user->data['username'],
        );
        $this->notification_manager->add_notifications(array(
            'rlip.intereststree.notification.type.inttree_proposal_accepted',
        ), $aNotificationData);
    }

    /**
     * Zaakceptowanie lub odrzucenie głosu przez moderatora
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function proposalManage()
    {
        try {
            global $db;
            $iProposalId = (int)$this->request->variable('proposal_id', 0);
            $iIsAccept = (int)$this->request->variable('is_accept', 1);
            $this->_isUser();
            $this->_isProposal($iProposalId);
            if(!$this->_hasModPermission()){
                throw new Exception('Brak uprawnień!');
            }
            $aProposal = $this->_getProposalById($iProposalId);
            $sSql = 'DELETE FROM ' . $this->_getTablePrefix() . 'inttree_proposal where proposal_id = ' . $iProposalId;
            $db->sql_query($sSql);
            if($iIsAccept){
                $this->_addAcceptedNotification($aProposal['proposal_user_id']);
            } else {
                $this->_addRejectedNotification($aProposal['proposal_user_id']);
            }
            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => true
            ));
        } catch (Exception $e) {
            return new \Symfony\Component\HttpFoundation\JsonResponse(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
        }
    }
}
