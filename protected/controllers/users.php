<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 26.05.14
 * Time: 14:18
 */

namespace Pixelf\Controllers\users;
require_once dirname(__FILE__).'/../helpers/helpers.php';
require_once dirname(__FILE__).'/../models/user.php';

const USERS_PER_PAGE = 20;
const SITES_PER_PAGE = 50;

function action_stats() {
    $since = intval($_GET['since']);
    if ($since < 0)
        $since += time();
    $users_ids = $_GET['sites_ids'];
    $step = 1;
    $until = time();

    $timeline_keys = array();
    $time = $since;
    while ($time <= $until) {
        $timeline_keys []= intval($time);
        $time += $step;
    }

    $stats = \Pixelf\Models\user\get_users_stats($users_ids, $since);
    $stats_grouped = array_combine($users_ids, array_fill(0, count($users_ids), array_combine($timeline_keys, array_fill(0, count($timeline_keys), 0))));
    foreach($stats as $row) {
        $stats_grouped[$row['user_id']] [strtotime($row['datekey'])] = intval($row['requests']);
    }
    $stats_grouped = array_map(function ($array) {
        return array_values($array);
    }, $stats_grouped);
    echo json_encode($stats_grouped);
}

function action_view() {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;

    $sites = \Pixelf\Models\user\get_user_site_stats($user_id, SITES_PER_PAGE, ($page-1)*SITES_PER_PAGE);
    $counters = \Pixelf\Models\user\get_user_requests_counts($user_id);
    $total_sites = \Pixelf\Models\user\get_user_site_stats_count($user_id);


    $total_pages = ceil($total_sites / SITES_PER_PAGE);

    render('view', array(
        'sites' => $sites,
        'user_id' => $user_id,
        'total_pages' => $total_pages,
        'total_sites' => $total_sites,
        'page' => $page,
        'counters' => $counters,
    ));
}

function render($view, $data = array()) {
    \Pixelf\Helpers\render_file('users/'.$view.'.twig', $data);
}