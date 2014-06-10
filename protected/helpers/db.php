<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 22.05.14
 * Time: 0:31
 */

namespace Pixelf\Helpers\Db;

require_once dirname(__FILE__).'/../config/main.php';

function get_dbh() {
    static $dbh = null;
    if (empty($dbh)) {
        $dbh =  mysqli_connect(
            \Pixelf\Config\get_config_parameter('db_host'),
            \Pixelf\Config\get_config_parameter('db_user'),
            \Pixelf\Config\get_config_parameter('db_password'),
            \Pixelf\Config\get_config_parameter('db_db')
        );
        mysqli_query($dbh, 'SET NAMES utf8');
        mysqli_query($dbh, 'SET profiling=1');
        mysqli_query($dbh, 'SET profiling_history_size=1');
    }
    return $dbh;
}

function fetch_all($query, $types = null, $params= array(), $key = null) {
    $db_result = query($query, $types, $params);
    $result = [];
    if ($db_result) {
        while ($row = mysqli_fetch_assoc($db_result)) {
            if ($key) {
                $result [$row[$key]] = $row;
            } else {
                $result []= $row;
            }

        }
    }

    return $result;
}

function fetch_column($query, $types = null, $params= array()) {
    $db_result = query($query, $types, $params);
    $result = [];
    if ($db_result) {
        while ($row = mysqli_fetch_assoc($db_result)) {
            $result [] = array_shift($row);
        }
    }

    return $result;
}

function fetch_one($query, $types = null, $params= array()) {
    $db_result = query($query, $types, $params);

    if ($db_result) {
        $row = mysqli_fetch_assoc($db_result);
        return $row;
    }

    return null;
}

function fetch_value($query, $types = null, $params= array()) {
    $row = fetch_one($query, $types, $params);
    if ($row) {
        return array_shift($row);
    }

    return null;
}

function last_insert_id() {
    return mysqli_insert_id(get_dbh());
}

function insert($query, $types = null, $params= array()) {
    query($query, $types, $params);
    return last_insert_id();
}

function query($query, $types = null, $params = array()) {
    $start = microtime(true);

    $result = false;
    if ($types && !empty($params)) {
        $statement = mysqli_prepare(get_dbh(), $query);
        if ($statement !== false) {
            call_user_func_array("mysqli_stmt_bind_param", array_merge(array($statement, $types), \Pixelf\Helpers\ref_values($params)));
            $executeResult = mysqli_stmt_execute($statement);
            if ($executeResult) {
                /** @noinspection PhpVoidFunctionResultUsedInspection */
                $result = \mysqli_stmt_get_result($statement);
            }
        }
    } else {
        $result = mysqli_query(get_dbh(), $query);
    }
    if (($result === false) && (mysqli_errno(get_dbh()))) {
        throw new \Exception('MySQL Error: '.mysqli_error(get_dbh()).'. Query was: '.$query);
    }

    $time = microtime(true) - $start;
    if ($time > 0.5) {
        $query_id = \Pixelf\Helpers\Db\fetch_value('show profiles');
        $profile = \Pixelf\Helpers\Db\fetch_all('show profile for query '.$query_id);
        $data = array_map(function ($row) {
            return $row['Status'].':'."\t".$row['Duration'];
        }, $profile);
        file_put_contents('/tmp/pf-profile.log', str_repeat('=', 40).PHP_EOL.'Query:'."\t".$query.PHP_EOL.implode("\n", $data).PHP_EOL.str_repeat('=', 40).PHP_EOL, FILE_APPEND);
    }

    return $result;
}