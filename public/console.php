<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 22.05.14
 * Time: 20:58
 */

namespace Pixelf;

date_default_timezone_set('Europe/Moscow');

ini_set('display_errors', 'On');
require_once dirname(__FILE__).'/../protected/helpers/helpers.php';

global $argv;
$controller = $argv[1];
$action = $argv[2];

require_once dirname(__FILE__).'/../protected/controllers/'.$controller.'.php';
$method_name = '\\Pixelf\\Controllers\\'.$controller.'\\action_'.$action;
$method_name();