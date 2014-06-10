<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 22.05.14
 * Time: 22:21
 */

namespace Pixelf\Models\click;
require_once dirname(__FILE__).'/../helpers/db.php';

function insert($site_id, $url_id, $user_id) {
    $start = microtime(true);
    $insert_id = \Pixelf\Helpers\Db\insert('INSERT INTO pixel_log (site_id, url_id, user_id) VALUES (?, ?, ?)', 'iis', array(
        intval($site_id), $url_id, $user_id
    ));
    $time = microtime(true) - $start;
    if ($time > 0.5) {
        $profile = \Pixelf\Helpers\Db\fetch_all('show profile for query 1');
        file_put_contents('/tmp/pf-profile.log', print_r($profile, true), FILE_APPEND);
    }
    return $insert_id;
}

function get_clicks_count() {
    return intval(\Pixelf\Helpers\Db\fetch_value('SELECT COUNT(*) FROM pixel_log'));
}