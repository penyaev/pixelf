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
    die;
}

function render($view, $data = array()) {
    \Pixelf\Helpers\render_file('main/'.$view.'.twig', $data);
}
