<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 21.05.14
 * Time: 21:37
 */

namespace Pixelf;

date_default_timezone_set('Europe/Moscow');

ini_set('display_errors', 'On');
require_once dirname(__FILE__).'/../protected/helpers/helpers.php';


$request_uri = $_SERVER['REQUEST_URI'];
$relative_request_uri = \Pixelf\Helpers\get_relative_request_uri($request_uri);
list ($controller, $action) = \Pixelf\Helpers\get_route($relative_request_uri);

$filename = dirname(__FILE__).'/../protected/controllers/'.$controller.'.php';
if (file_exists($filename))
    require_once $filename;
else {
    require_once dirname(__FILE__).'/../protected/controllers/main.php';
    \Pixelf\Controllers\main\error(404, 'Not found');
}

$method_name = '\\Pixelf\\Controllers\\'.$controller.'\\action_'.$action;
if (!function_exists($method_name)) {
    require_once dirname(__FILE__).'/../protected/controllers/main.php';
    \Pixelf\Controllers\main\error(404, 'Not found');
}

ob_start();
try {
    $start_time = microtime(true);
    $method_name();
    $server_time = microtime(true)-$start_time;
} catch (\Exception $e) {
    require_once dirname(__FILE__).'/../protected/controllers/main.php';
    \Pixelf\Controllers\main\error(500, $e->getMessage());
}

$ob = ob_get_clean();
$ob = str_replace('%server_time%', number_format($server_time*1000, 1), $ob);
echo $ob;