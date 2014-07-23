<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 08.06.14
 * Time: 19:24
 */

namespace Pixelf\Models\lead;
require_once dirname(__FILE__).'/../helpers/db.php';

function insert_or_update($caption, $vk_lead_id, $secret, $site_id, $landing_url = null) {
    \Pixelf\Helpers\Db\query('
        INSERT INTO leads (vk_lead_id, site_id, caption, secret, landing_url) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE site_id=?, caption=?, secret=?, landing_url=?
    ', 'iisssisss', array(
        $vk_lead_id, $site_id, $caption, $secret, $landing_url,
        $site_id, $caption, $secret, $landing_url,
    ));
    return \Pixelf\Helpers\Db\last_insert_id();
}

function select_all() {
    return \Pixelf\Helpers\Db\fetch_all('SELECT * FROM leads', null, array(), 'vk_lead_id');
}

function get_by_site_id($site_id) {
    return \Pixelf\Helpers\Db\fetch_all('SELECT * FROM leads WHERE site_id=?', 'i', array($site_id), 'vk_lead_id');
}

function get_by_lead_id($lead_id) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM leads WHERE vk_lead_id=? LIMIT 1', 'i', array($lead_id));
}

function delete_by_ids(array $vk_lead_ids) {
    $vk_lead_ids = array_map('intval', $vk_lead_ids);
    return \Pixelf\Helpers\Db\query('
        DELETE FROM leads WHERE vk_lead_id IN ('.implode(', ', $vk_lead_ids).')
    ');
}

function insert_session($vk_sid, $vk_lead_id, $vk_uid, $user_id, $site_id) {
    return \Pixelf\Helpers\Db\insert('
        INSERT INTO sessions    (vk_sid,    vk_lead_id, vk_uid, user_id,    site_id)
        VALUES                  (?,         ?,          ?,      ?,          ?)
        ', implode('', array(   's',        'i',        'i',    's',        'i')), array(
                                $vk_sid,    $vk_lead_id,$vk_uid,$user_id,   $site_id
    ), true);
}

function get_open_session($user_id, $site_id) {
    return \Pixelf\Helpers\Db\fetch_value('
        SELECT session_id FROM sessions
        WHERE site_id=? AND user_id=? AND finished IS NULL
        ORDER BY created DESC
        LIMIT 1
    ', 'is', array($site_id, $user_id));
}

function close_session($session_id) {
    return \Pixelf\Helpers\Db\query('UPDATE sessions SET finished=NOW() WHERE session_id=?', 'i', array($session_id));
}

function get_session_by_session_id($session_id) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM sessions WHERE session_id=?', 'i', array($session_id));
}

function get_sessions_by_site_id($site_id) {
    return \Pixelf\Helpers\Db\fetch_all('SELECT * FROM sessions WHERE site_id=?', 'i', array($site_id));
}

function get_sessions_stats_by_site_id($site_id) {
    return \Pixelf\Helpers\Db\fetch_all('
        SELECT vk_lead_id, COUNT(*) AS sessions_total, SUM(finished IS NOT NULL) AS sessions_finished  FROM sessions
        WHERE site_id=?
        GROUP BY vk_lead_id
    ', 'i', array($site_id), 'vk_lead_id');
}
