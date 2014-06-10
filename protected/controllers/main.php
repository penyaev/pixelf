<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 21.05.14
 * Time: 22:05
 */

namespace Pixelf\Controllers\main;
require_once dirname(__FILE__).'/../helpers/helpers.php';
require_once dirname(__FILE__).'/../models/site.php';
require_once dirname(__FILE__).'/../models/click.php';

function action_index() {
    $clicks_count = \Pixelf\Models\click\get_clicks_count();
    render('index', array(
        'clicks_count' => $clicks_count,
    ));
}

function error($code = 500, $message = '') {
    header('HTTP/1.0 '.$code.' Error');
    render('error', array(
        'code' => $code,
        'message' => $message,
    ));
}

function render($view, $data = array()) {
    \Pixelf\Helpers\render_file('main/'.$view.'.twig', $data);
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
    $result = array('0' => array_values($stats_grouped));

    $stats = \Pixelf\Models\site\get_tw_stats($since);
    $stats_grouped = array_combine($timeline_keys, count($timeline_keys) ? array_fill(0, count($timeline_keys), 0) : array());
    foreach($stats as $row) {
        $stats_grouped [strtotime($row['datekey'])] = intval($row['requests']);
    }
    $result['1'] = array_values($stats_grouped);

    echo json_encode($result);
}

