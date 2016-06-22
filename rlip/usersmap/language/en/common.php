<?php
if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = array();
}

$lang = array_merge($lang, array(
    'RLIP_USERSMAP_NAVI_NAME' => 'Mapa',
    'RLIP_USERSMAP_MODULE_NAME' => 'Moduł mapy użytkowników',
    'RLIP_USERSMAP_SETTINGS_TITLE' => 'Ustawienia',
    'RLIP_USERSMAP_SETTINGS_SERVER_KEY' => 'Klucz serwera mapy google',
    'RLIP_USERSMAP_SETTINGS_JS_KEY' => 'Klucz java script mapy google',
    'RLIP_USERSMAP_SETTING_SAVED' => 'Ustawienia zostały zapisane',
));
