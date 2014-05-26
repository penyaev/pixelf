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
    return \Pixelf\Helpers\Db\insert('INSERT INTO pixel_log (site_id, url_id, user_id) VALUES (?, ?, ?)', 'iis', array(
        intval($site_id), $url_id, $user_id
    ));
}

function get_clicks_count() {
    return intval(\Pixelf\Helpers\Db\fetch_value('SELECT COUNT(*) FROM pixel_log'));
}