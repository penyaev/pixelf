<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 26.05.14
 * Time: 16:03
 */

namespace Pixelf\Models\url;
require_once dirname(__FILE__).'/../helpers/db.php';

function insert_or_get_id($url) {
    $url_id = \Pixelf\Helpers\Db\fetch_value('SELECT url_id FROM urls WHERE url_crc=CRC32(?) AND url=?', 'ss', array(
        $url, $url
    ));
    if (empty($url_id)) {
        $url_id = \Pixelf\Helpers\Db\insert('INSERT INTO urls (url_crc, url) VALUES (CRC32(?), ?)', 'ss', array(
            $url, $url
        ));
    }
    return $url_id;
}
