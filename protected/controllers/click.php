<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 22.05.14
 * Time: 1:01
 */

namespace Pixelf\Controllers\click;
require_once dirname(__FILE__).'/../helpers/helpers.php';
require_once dirname(__FILE__).'/../helpers/leads.php';
require_once dirname(__FILE__).'/../models/click.php';
require_once dirname(__FILE__).'/../models/site.php';
require_once dirname(__FILE__).'/../models/url.php';
require_once dirname(__FILE__).'/../models/user.php';
require_once dirname(__FILE__).'/../models/lead.php';

const MAY_2024 = 1716367119; // наступит не скоро

function action_store() {
    ignore_user_abort(true);

    $user_id = isset($_GET['pf_user_id']) ? $_GET['pf_user_id'] : null; // оставлено для отладки, в бою надо брать только из кук
    if (empty($user_id))
        $user_id = isset($_COOKIE['pf_user_id']) ? $_COOKIE['pf_user_id'] : null;
    if (empty($user_id)) {
        $user_id = \Pixelf\models\user\get_unique_user_id();
        setcookie('pf_user_id', $user_id, MAY_2024);
    }

    $site_uid = isset($_GET['site_uid']) ? $_GET['site_uid'] : 0;
    $site_id = \Pixelf\models\site\select_id_by_uid($site_uid);
    if (empty($site_id)) {
        header("HTTP/1.0 404 Not found");
        die;
    }

    header("HTTP/1.0 204 No Content");
    flush(); // disconnect user and continue request processing

    $url = isset($_GET['url']) ? $_GET['url'] : null; // оставлено для отладки, в бою надо брать только из реферера
    if (empty($url)) {
        $url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'unknown';
    }

    $url_id = \Pixelf\models\url\insert_or_get_id($url);
    \Pixelf\Models\Click\insert($site_id, $url_id, $user_id);

    $session_id = handle_lead_session($url, $user_id, $site_id);
    if (empty($session_id)) {
        $session_id = \Pixelf\Models\lead\get_open_session($user_id, $site_id); // пытаемся получить открытую сессию
    }

    if (!empty($session_id)) { // если есть открытая сессия
        if (\Pixelf\Models\user\is_good($user_id, $site_id)) { // если юзер удовлетворяет условиям
            \Pixelf\Helpers\leads\complete_lead($session_id);
        }
    }
}

function handle_lead_session($url, $user_id, $site_id) {
    $url_query = parse_url($url, PHP_URL_QUERY);
    $url_variables = array();
    parse_str($url_query, $url_variables);

    $vk_lead_id = \Pixelf\Helpers\get_value($url_variables, 'vk_lead_id');
    if (empty($vk_lead_id))
        return false;

    $vk_sid = \Pixelf\Helpers\get_value($url_variables, 'vk_sid');
    $vk_uid = \Pixelf\Helpers\get_value($url_variables, 'vk_uid');
    $vk_hash = \Pixelf\Helpers\get_value($url_variables, 'vk_hash');

    $lead = \Pixelf\Models\lead\get_by_lead_id($vk_lead_id);
    if (empty($lead))
        return false;

    if (!\Pixelf\Helpers\leads\validate_hash($vk_hash, $vk_sid, $vk_lead_id, $vk_uid, $lead['secret']))
        return false;

    $session_id = \Pixelf\Models\lead\insert_session($vk_sid, $vk_lead_id, $vk_uid, $user_id, $site_id);

    if (!empty($session_id))
        return $session_id;
    else
        return false;
}

function render($view, $data) {
    \Pixelf\Helpers\render_file('click/'.$view.'.twig', $data);
}
