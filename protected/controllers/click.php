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

function action_fill_sites() {
    $total = 1000;
    $word_list = explode("\n", file_get_contents('google-10000-english.txt'));
    $domains_list = explode("\n", file_get_contents('tlds-alpha-by-domain.txt'));

    for ($i=1; $i <= $total; $i++) {
        $site_uid = \Pixelf\models\site\generate_site_uid();

        $domain_name = $word_list[array_rand($word_list)];
        $tld = strtolower($domains_list[array_rand($domains_list)]);
        $domain = $domain_name.'.'.$tld;
        \Pixelf\Models\site\insert($domain, $site_uid, rand(3,6));
    }
}

function action_load() {
    echo 'Start load simulation (random delays between requests)'.PHP_EOL;

    $sites = \Pixelf\models\site\select_all(0, 20000);
    if (empty($sites)) {
        echo 'Please create some sites first with fill_sites';
        die;
    }
    $delay_min = 12;
    $delay_max = 50;

    $start_time = microtime(true);
    $stats_step = 1000;
    for ($i=1;; $i++) {
        if (($i % $stats_step) == 0) {
            $time = microtime(true)-$start_time;
            $avg_rps = number_format($stats_step/$time, 2);
            $start_time = microtime(true);
            echo $stats_step.' requests done (total '.$i.') within last '.number_format($time, 1).' seconds, avg rps='.$avg_rps.PHP_EOL;
        }

        $site_uid = $sites[array_rand($sites)]['site_uid'];
        $url = 'url'.rand(1,100000);
        $user_id = 'pfu-'.rand(1,5000);

        $click_url = 'http://'.\Pixelf\Config\get_config_parameter('host').\Pixelf\Helpers\create_url('click/store', array(
                'pf_user_id' => $user_id,
                'site_uid' => $site_uid,
                'url' => $url,
            ));
        @file_get_contents($click_url);

        usleep(rand($delay_min, $delay_max)*100);
    }
    echo 'done'.PHP_EOL;
}

function render($view, $data) {
    \Pixelf\Helpers\render_file('click/'.$view.'.twig', $data);
}

function action_overallstats() {
    $since = intval($_GET['since']);
    if ($since < 0)
        $since += time();
    $step = 1;
    $until = time();

    $timeline_keys = array();
    $time = $since;
    while ($time <= $until) {
        $timeline_keys []= intval($time);
        $time += $step;
    }

    $stats = \Pixelf\Models\site\get_overall_stats($since);
    $stats_grouped = array_combine($timeline_keys, count($timeline_keys) ? array_fill(0, count($timeline_keys), 0) : array());
    foreach($stats as $row) {
        $stats_grouped [strtotime($row['datekey'])] = intval($row['requests']);
    }
    echo json_encode(array('0' => array_values($stats_grouped)));
}