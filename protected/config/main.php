<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 21.05.14
 * Time: 21:41
 */

namespace Pixelf\Config;


function config() {
    return array(
        'baseUrl' => '/pixelf/',
        'default_controller' => 'main',
        'default_action' => 'index',

        'db_host' => '127.0.0.1',
        'db_user' => 'root',
        'db_password' => '1234',
        'db_db' => 'pixel',
    );
}

function get_config_parameter($parameter) {
    $config = config();
    if (isset($config[$parameter]))
        return $config[$parameter];
    return null;
}