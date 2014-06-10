<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 21.05.14
 * Time: 21:37
 */

namespace Pixelf;

use Pixelf\Helpers\HttpException;

date_default_timezone_set('Europe/Moscow');

ini_set('display_errors', 'On');
require_once dirname(__FILE__).'/../protected/helpers/helpers.php';


$request_uri = $_SERVER['REQUEST_URI'];
$relative_request_uri = \Pixelf\Helpers\get_relative_request_uri($request_uri);
list ($controller, $action) = \Pixelf\Helpers\get_route($relative_request_uri);


$start_time = microtime(true);
ob_start();
try {
    $filename = dirname(__FILE__).'/../protected/controllers/'.$controller.'.php';
    if (file_exists($filename))
        require_once $filename;
    else {
        require_once dirname(__FILE__).'/../protected/controllers/main.php';
        throw new HttpException('Controller not found', 404);
    }

    $method_name = '\\Pixelf\\Controllers\\'.$controller.'\\action_'.$action;
    if (!function_exists($method_name))
        throw new HttpException('Action not found', 404);

    $method_name();
} catch(HttpException $e) {
    require_once dirname(__FILE__).'/../protected/controllers/main.php';
    \Pixelf\Controllers\main\error($e->getCode(), $e->getMessage());
} catch (\Exception $e) {
    require_once dirname(__FILE__).'/../protected/controllers/main.php';
    \Pixelf\Controllers\main\error(500, $e->getMessage());
}

$ob = ob_get_clean();
$server_time = microtime(true)-$start_time;
$ob = str_replace('%server_time%', number_format($server_time*1000, 1, '.', ' '), $ob);

echo $ob;