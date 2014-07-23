<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 22.05.14
 * Time: 0:31
 */

namespace Pixelf\Helpers\Db;

require_once dirname(__FILE__).'/../config/main.php';

$query_log = array();
$debug_lock = true;

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
        $debug_enabled = \Pixelf\Config\get_config_parameter('db_debug', false);
        if ($debug_enabled) {
            mysqli_query($dbh, 'SET profiling=1');
            mysqli_query($dbh, 'SET profiling_history_size=1');
        }
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

function insert($query, $types = null, $params= array(), $disable_debug = false) {
    global $debug_lock;
    $old_debug_lock = $debug_lock;

    if ($disable_debug) {
        $debug_lock = false;
    }

    query($query, $types, $params);
    $debug_lock = $old_debug_lock;

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
    global $debug_lock;
    $debug_enabled = $debug_lock && \Pixelf\Config\get_config_parameter('db_debug', false);
    if ($debug_enabled) {
        $debug_lock = false;
        global $query_log;
        $threshold = floatval(\Pixelf\Config\get_config_parameter('db_debug_threshold', 0));
        if ($time > $threshold) {
            $query_id = \Pixelf\Helpers\Db\fetch_value('show profiles');
            $profile = \Pixelf\Helpers\Db\fetch_all('show profile for query '.$query_id);
            usort($profile, function ($row1, $row2) {
                $delta = floatval($row1['Duration']) - floatval($row2['Duration']);
                if (abs($delta) < 0.000001)
                    return 0;
                if ($delta > 0)
                    return -1;
                return +1;
            });
            $query_log []= array(
                'query' => $query,
                'params' => $params,
                'time' => $time,
                'profiling_info' => $profile,
            );
        }
        $debug_lock = true;
    }

    return $result;
}

function get_query_log() {
    global $query_log;
    usort($query_log, function ($row1, $row2) {
        $delta = $row1['time'] - $row2['time'];
        if (abs($delta) < 0.000001)
            return 0;
        if ($delta > 0)
            return -1;
        return +1;
    });
    return $query_log;
}
