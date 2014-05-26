<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 26.05.14
 * Time: 14:19
 */

namespace Pixelf\Models\user;
require_once dirname(__FILE__).'/../helpers/db.php';


function select_all($offset=0, $limit = 20) {
    return \Pixelf\Helpers\Db\fetch_column('
        SELECT DISTINCT user_id
        FROM pixel_log
        ORDER BY user_id ASC
        LIMIT ?,?
        ', 'ii', array(
        $offset, $limit
    ));
}

function get_user_requests_counts($user_id) {
    return \Pixelf\Helpers\Db\fetch_one('
      SELECT COUNT(url_id) AS requests, COUNT(DISTINCT url_id) AS unique_requests
      FROM pixel_log
      WHERE user_id = ?
      GROUP BY user_id
    ', 's', array($user_id));
}

function get_user_site_stats($user_id, $count = 20, $offset = 0) {
    return \Pixelf\Helpers\Db\fetch_all('
        SELECT pixel_log.site_id, COUNT(url_id) AS requests, COUNT(DISTINCT url_id) AS unique_requests,sites.`request_threshold`,sites.domain,sites.`site_uid`,COUNT(DISTINCT url_id)  >= sites.`request_threshold` AS good_user
        FROM pixel_log
        LEFT JOIN sites ON sites.site_id=pixel_log.site_id
        WHERE user_id=?
        GROUP BY pixel_log.site_id
        ORDER BY good_user DESC,requests DESC, site_id ASC
        '.($count ? 'LIMIT ?,?' : '').'
    ', 's'.($count ? 'ii' : ''), array_merge(array($user_id), $count ? array($offset, $count) : array()), 'site_id');
}

function get_user_site_stats_count($user_id) {
    return intval(\Pixelf\Helpers\Db\fetch_value('
        SELECT COUNT(*) FROM (
            SELECT pixel_log.site_id, COUNT(url_id) AS requests, COUNT(DISTINCT url_id) AS unique_requests,sites.`request_threshold`,sites.domain,sites.`site_uid`,COUNT(DISTINCT url_id)  >= sites.`request_threshold` AS good_user
            FROM pixel_log
            LEFT JOIN sites ON sites.site_id=pixel_log.site_id
            WHERE user_id=?
            GROUP BY pixel_log.site_id
        ) t
    ', 's', array($user_id)));
}

function get_users_stats(array $users_ids, $since) {
    $placeholders = array_fill(0, count($users_ids), '?');

    // uses idx_group4 index
    $result = \Pixelf\Helpers\Db\fetch_all('
            SELECT user_id,COUNT(*) AS requests,(timestamp) AS datekey
            FROM pixel_log
            WHERE user_id IN ('.implode(',', $placeholders).') AND timestamp > FROM_UNIXTIME(?)
            GROUP BY user_id, datekey
    ', str_repeat('s', count($users_ids)).'i', array_merge(
        $users_ids, array($since)
    ));
    return $result;
}

function get_users_for_site($site_id, $only_good = false, $count = 20, $offset = 0) {
    return \Pixelf\Helpers\Db\fetch_all('
            SELECT pixel_log.user_id,COUNT(url_id) AS requests,COUNT(DISTINCT url_id) AS unique_requests,sites.`request_threshold`
            FROM pixel_log
            JOIN sites ON sites.site_id=pixel_log.site_id
            WHERE pixel_log.`site_id`=?
            GROUP BY pixel_log.user_id
            '.($only_good ? 'HAVING unique_requests >= request_threshold' : '').'
            '.($count ? 'LIMIT ?,?' : '').'
    ', 'i'.($count ? 'ii' : ''), array_merge(array($site_id), $count ? array($offset, $count) : array()), 'user_id');
}

function get_users_count_for_site($site_id, $only_good = false) {
    return intval(\Pixelf\Helpers\Db\fetch_value('
        SELECT COUNT(*) FROM (
            SELECT pixel_log.user_id,COUNT(url_id) AS requests,COUNT(DISTINCT url_id) AS unique_requests,sites.`request_threshold`
            FROM pixel_log
            JOIN sites ON sites.site_id=pixel_log.site_id
            WHERE pixel_log.`site_id`=?
            GROUP BY pixel_log.user_id
            '.($only_good ? 'HAVING unique_requests >= request_threshold' : '').'
        ) t
    ', 'i', array($site_id)));
}

function get_users_count() {
    return intval(\Pixelf\Helpers\Db\fetch_value('SELECT COUNT(DISTINCT user_id) AS total_users FROM pixel_log'));
}

function get_user_prefix() {
    return 'pfu-';
}

function get_unique_user_id() {
    return get_user_prefix().substr(sha1(uniqid('', true)), 0, 24);
}