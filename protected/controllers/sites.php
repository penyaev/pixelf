<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 23.05.14
 * Time: 0:26
 */



namespace Pixelf\Controllers\sites;
require_once dirname(__FILE__).'/../controllers/main.php';
require_once dirname(__FILE__).'/../helpers/helpers.php';
require_once dirname(__FILE__).'/../models/site.php';
require_once dirname(__FILE__).'/../models/user.php';
require_once dirname(__FILE__).'/../models/lead.php';

const SITES_PER_PAGE = 20;
const USERS_PER_PAGE = 50;

function action_index() {
    $sites = \Pixelf\Models\site\select_all(0, 20000);
    render('index', array(
        'sites' => $sites,
    ));
}

function action_multi() {
    $sites_ids = isset($_GET['sites_ids']) ? $_GET['sites_ids'] : array();
    $sites_ids = array_map(function ($site_id) {
        return intval($site_id);
    }, $sites_ids);

    $sites = \Pixelf\Models\site\select_by_id_array($sites_ids);
    $sites_counters = \Pixelf\Models\site\get_sites_requests_counts($sites_ids);

    render('multi', array(
        'sites' => $sites,
        'counters' => $sites_counters,
    ));
}

function action_edit() {
    $site = array();
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['site_id'])) {
            $site = \Pixelf\Models\site\select_by_id(intval($_GET['site_id']));
        } else {
            $site['site_uid'] = \Pixelf\Models\site\generate_site_uid();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['site_id'])) {
            \Pixelf\Models\site\update_by_id(intval($_POST['site_id']), $_POST['domain'], $_POST['site_uid'], $_POST['request_threshold']);
        } else {
            $_POST['site_id'] = \Pixelf\Models\site\insert($_POST['domain'], $_POST['site_uid'], $_POST['request_threshold']);
        }

        $leads = isset($_POST['leads']) ? $_POST['leads'] : array();
        $existing_leads = array_keys(\Pixelf\Models\lead\get_by_site_id($_POST['site_id']));
        $saved_leads = array();
        foreach($leads as $lead_info) {
            if (empty($lead_info['vk_lead_id'])) {
                $lead_info['vk_lead_id'] = null;
            }
            if (empty($lead_info['lead_id'])) {
                $saved_leads []= \Pixelf\Models\lead\insert($lead_info['caption'], $lead_info['vk_lead_id'], $lead_info['secret'], $_POST['site_id'], $lead_info['landing_url']);
            } else {
                \Pixelf\Models\lead\update(intval($lead_info['lead_id']), $lead_info['caption'], $lead_info['vk_lead_id'], $lead_info['secret'], $_POST['site_id'], $lead_info['landing_url']);
                $saved_leads []= intval($lead_info['lead_id']);
            }
        }

        $leads_to_remove = array_diff($existing_leads, $saved_leads);
        if (!empty($leads_to_remove))
            \Pixelf\Models\lead\delete_by_ids($leads_to_remove);

        \Pixelf\Helpers\redirect('sites/view', array('site_id' => $_POST['site_id']));
    }

    if (isset($site['site_id']))
        $leads = \Pixelf\Models\lead\get_by_site_id(intval($site['site_id']));
    else
        $leads = array();
    render('edit', array(
        'site' => $site,
        'leads' => $leads,
    ));
}

function action_view() {
    $site = \Pixelf\Models\site\select_by_id(intval($_GET['site_id']));
    if (empty($site)) {
        \Pixelf\Controllers\main\error(404, 'Site not found');
    }
    $stats = \Pixelf\Models\site\get_sites_requests_counts(array(intval($site['site_id'])));

    $leads = \Pixelf\Models\lead\get_by_site_id($site['site_id']);
    $sessions_stats = \Pixelf\Models\lead\get_sessions_stats_by_site_id($site['site_id']);

    render('view', array(
        'site' => $site,
        'stats' => $stats,
        'sessions_stats' => $sessions_stats,
        'leads' => $leads,
    ));
}

function action_stats() {
    $since = intval($_GET['since']);
    if ($since < 0)
        $since += time();
    $sites_ids = $_GET['sites_ids'];
    $step = 1;
    $until = time();

    $timeline_keys = array();
    $time = $since;
    while ($time <= $until) {
        $timeline_keys []= intval($time);
        $time += $step;
    }

    $stats = \Pixelf\Models\site\get_sites_stats($sites_ids, $since);
    $stats_grouped = array_combine($sites_ids, array_fill(0, count($sites_ids), array_combine($timeline_keys, array_fill(0, count($timeline_keys), 0))));
    foreach($stats as $row) {
        $stats_grouped[intval($row['site_id'])] [strtotime($row['datekey'])] = intval($row['requests']);
    }
    $stats_grouped = array_map(function ($array) {
        return array_values($array);
    }, $stats_grouped);
    echo json_encode($stats_grouped);
}

function action_viewusers() {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $site_id = isset($_GET['site_id']) ? intval($_GET['site_id']) : null;
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'all';

    $site = \Pixelf\Models\site\select_by_id(intval($_GET['site_id']));
    $users = \Pixelf\Models\user\get_users_for_site($site_id, $mode === 'good', USERS_PER_PAGE, ($page-1)*USERS_PER_PAGE);

    $total_users = \Pixelf\Models\user\get_users_count_for_site($site_id, $mode === 'good');
    $total_pages = ceil($total_users / USERS_PER_PAGE);

    render('viewusers', array(
        'users' => $users,
        'site' => $site,
        'mode' => $mode,
        'total_pages' => $total_pages,
        'total_users' => $total_users,
        'page' => $page,
    ));
}

function render($view, $data = array()) {
    \Pixelf\Helpers\render_file('sites/'.$view.'.twig', $data);
}