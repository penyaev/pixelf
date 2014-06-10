<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 23.05.14
 * Time: 1:10
 */

namespace Pixelf\Models\site;
require_once dirname(__FILE__).'/../helpers/db.php';

function insert($domain, $site_uid, $request_threshold) {
    return \Pixelf\Helpers\Db\insert('INSERT INTO sites (domain, site_uid, request_threshold) VALUES (?, ?, ?)', 'ssi', array(
        $domain, $site_uid, intval($request_threshold)
    ));
}

function select_all($offset=0, $limit = 20) {
    return \Pixelf\Helpers\Db\fetch_all('SELECT * FROM sites ORDER BY site_id DESC LIMIT ?,?', 'ii', array(
        $offset, $limit
    ), 'site_id');
}

function get_sites_count() {
    return \Pixelf\Helpers\Db\fetch_value('SELECT COUNT(*) FROM sites');
}

function select_by_id($site_id) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM sites WHERE site_id=?', 'i', array($site_id));
}

function select_by_id_array(array $sites_ids) {
    $placeholders = array_fill(0, count($sites_ids), '?');
    return \Pixelf\Helpers\Db\fetch_all('
        SELECT *
        FROM sites
        WHERE site_id IN ('.implode(',', $placeholders).')
        ', str_repeat('i', count($sites_ids)), $sites_ids, 'site_id');
}

function select_by_uid($site_uid) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM sites WHERE site_uid=?', 's', array($site_uid));
}

function select_id_by_uid($site_uid) {
    return intval(\Pixelf\Helpers\Db\fetch_value('SELECT site_id FROM sites WHERE site_uid=?', 's', array($site_uid)));
}

function select_by_domain($domain) {
    return \Pixelf\Helpers\Db\fetch_one('SELECT * FROM sites WHERE domain=?', 's', array($domain));
}

function update_by_id($site_id, $domain, $site_uid, $request_threshold) {
    return \Pixelf\Helpers\Db\query('UPDATE sites SET domain=?, site_uid=?,request_threshold=? WHERE site_id=?', 'ssii', array(
        $domain, $site_uid, intval($request_threshold), intval($site_id)
    ));
}

function get_site_prefix() {
    return 'pfs-';
}
function generate_site_uid() {
    return get_site_prefix().substr(sha1(uniqid()), 0, 12);
}

function get_sites_requests_counts(array $site_ids, $since = null) {
    $placeholders = array_fill(0, count($site_ids), '?');

    // uses idx_group index
    return \Pixelf\Helpers\Db\fetch_all('
        SELECT site_id,SUM(requests) AS total_requests,SUM(good_user) AS good_users,COUNT(*) AS total_users,request_threshold FROM (
            SELECT  s.site_id,s.user_id,COUNT(*) AS requests,COUNT(DISTINCT url_id)>=sites.`request_threshold` AS good_user,sites.request_threshold
            FROM pixel_log s
            JOIN sites ON sites.site_id=s.site_id
            WHERE s.site_id IN ('.implode(',', $placeholders).')
            GROUP BY s.site_id,user_id
        ) t GROUP BY site_id
    ', str_repeat('i', count($site_ids)).($since ? 'i' : ''),
        array_merge($site_ids, $since ? array($since) : array())
    , 'site_id');
}

function get_sites_stats(array $site_ids, $since) {
    $placeholders = array_fill(0, count($site_ids), '?');

    // uses idx_group2 index
    $result = \Pixelf\Helpers\Db\fetch_all('
            SELECT site_id,COUNT(*) AS requests,(timestamp) AS datekey
            FROM pixel_log
            WHERE site_id IN ('.implode(',', $placeholders).') AND timestamp > FROM_UNIXTIME(?)
            GROUP BY site_id, datekey
    ', str_repeat('i', count($site_ids)).'i', array_merge(
        $site_ids, array($since)
    ));
    return $result;
}

function get_overall_stats($since) {
    // uses idx_group3 index
    $result = \Pixelf\Helpers\Db\fetch_all('
        SELECT COUNT(*) AS requests,(timestamp) AS datekey
        FROM pixel_log
        WHERE timestamp > FROM_UNIXTIME(?)
        GROUP BY datekey
    ', 'i', array($since));
    return $result;
}

function get_tw_stats($since) {
    $result = \Pixelf\Helpers\Db\fetch_all('
        SELECT SUM(count) AS requests,(timestamp) AS datekey
        FROM time_waits
        WHERE timestamp > FROM_UNIXTIME(?)
        GROUP BY datekey
    ', 'i', array($since));
    return $result;
}