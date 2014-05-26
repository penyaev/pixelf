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

function insert($query, $types = null, $params= array()) {
    query($query, $types, $params);
    return mysqli_insert_id(get_dbh());
}

function query($query, $types = null, $params = array()) {
    $result = false;
    if ($types && !empty($params)) {
        $statement = mysqli_prepare(get_dbh(), $query);
        if ($statement !== false) {
            call_user_func_array("mysqli_stmt_bind_param", array_merge(array($statement, $types), \Pixelf\Helpers\ref_values($params)));
            $executeResult = mysqli_stmt_execute($statement);
            if ($executeResult) {
                /** @noinspection PhpVoidFunctionResultUsedInspection */
                $result = mysqli_stmt_get_result($statement);
            }
        }
    } else {
        $result = mysqli_query(get_dbh(), $query);
    }
    if (($result === false) && (mysqli_errno(get_dbh()))) {
        throw new \Exception('MySQL Error: '.mysqli_error(get_dbh()).'. Query was: '.$query);
    }
    return $result;
}