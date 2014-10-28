<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 08.06.14
 * Time: 19:24
 */

namespace Pixelf\Models\lead;
require_once dirname(__FILE__).'/../helpers/db.php';

function insert($caption, $vk_lead_id, $secret, $site_id, $landing_url = null) {
    \Pixelf\Helpers\Db\query('
        INSERT INTO leads (vk_lead_id, site_id, caption, secret, landing_url) VALUES (?, ?, ?, ?, ?)
    ', 'iisss', array(
        $vk_lead_id, $site_id, $caption, $secret, $landing_url,
    ));
    return \Pixelf\Helpers\Db\last_insert_id();
}

function update($lead_id, $caption, $vk_lead_id, $secret, $site_id, $landing_url = null) {
    return \Pixelf\Helpers\Db\query('
        UPDATE leads SET vk_lead_id=?, site_id=?, caption=?, secret=?, landing_url=? WHERE lead_id=?
    ', 'iisssi', array(
        $vk_lead_id, $site_id, $caption, $secret, $landing_url, $lead_id
    ));
}

function select_all() {
    return \Pixelf\Helpers\Db\fetch_all('SELECT * FROM leads', null, array(), 'lead_id');
}

function get_by_site_id($site_id) {
    return \Pixelf\Helpers\Db\fetch_all('SELECT * FROM leads WHERE site_id=?', 'i', array($site_id), 'lead_id');
}

function get_by_vk_lead_id($vk_lead_id) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM leads WHERE vk_lead_id=? LIMIT 1', 'i', array($vk_lead_id));
}

function get_by_lead_id($lead_id) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM leads WHERE lead_id=? LIMIT 1', 'i', array($lead_id));
}

function get_by_vk_lead_id_and_site_id($vk_lead_id, $site_id) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM leads WHERE vk_lead_id=? AND site_id=? LIMIT 1', 'ii', array($vk_lead_id, $site_id));
}

function delete_by_ids(array $lead_ids) {
    $lead_ids = array_map('intval', $lead_ids);
    return \Pixelf\Helpers\Db\query('
        DELETE FROM leads WHERE lead_id IN ('.implode(', ', $lead_ids).')
    ');
}

function insert_session($vk_sid, $lead_id, $vk_uid, $user_id, $site_id) {
    return \Pixelf\Helpers\Db\insert('
        INSERT INTO sessions    (vk_sid,    lead_id,    vk_uid,  user_id,    site_id)
        VALUES                  (?,         ?,          ?,       ?,          ?)
        ', implode('', array(   's',        'i',        'i',     's',        'i')), array(
                                $vk_sid,    $lead_id,   $vk_uid, $user_id,   $site_id
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
        SELECT lead_id, COUNT(*) AS sessions_total, SUM(finished IS NOT NULL) AS sessions_finished  FROM sessions
        WHERE site_id=?
        GROUP BY lead_id
    ', 'i', array($site_id), 'lead_id');
}
