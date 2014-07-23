<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 21.05.14
 * Time: 21:41
 */

namespace Pixelf\Config;

require_once dirname(__FILE__).'/../helpers/helpers.php';

function config() {
    return array(
        'host' => 'localhost',
        'baseUrl' => '/pixelf/',
        'default_controller' => 'main',
        'default_action' => 'index',

        'db_host' => '127.0.0.1',
        'db_user' => 'root',
        'db_password' => '1234',
        'db_db' => 'pixel',

        'db_debug' => false,
        'db_debug_threshold' => 0,
    );
}

function get_config_parameter($parameter, $default = null) {
    return \Pixelf\Helpers\get_value(config(), $parameter, $default);
}