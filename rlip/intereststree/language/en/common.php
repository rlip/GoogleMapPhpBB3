<?php

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'RLIP_INTERESTS_TREE_NAVI_NAME' => 'Drzewo zainteresowań',
    'RLIP_INTERESTS_TREE_PROPOSAL_NOTIFICATION_ACCEPTED_TEXT' => 'Twoja propozycja została zaakceptowana',
    'RLIP_INTERESTS_TREE_PROPOSAL_NOTIFICATION_REJECTED_TEXT' => 'Twoja propozycja została odrzucona',
    'RLIP_INTERESTS_TREE_PROPOSAL_NOTIFICATION_TITLE' => 'Drzewo zainteresowań'
));
