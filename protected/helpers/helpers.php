<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 21.05.14
 * Time: 21:44
 */

namespace Pixelf\Helpers;

require_once dirname(__FILE__).'/../config/main.php';

/**
 * @param $request_uri string
 * @return string
 */
function get_relative_request_uri($request_uri) {
    $base_url = \Pixelf\Config\get_config_parameter('baseUrl');
    $relative_uri = substr($request_uri, strlen($base_url));
    $relative_uri_params_stripped = strstr($relative_uri, '?', true);
    return $relative_uri_params_stripped ? $relative_uri_params_stripped : $relative_uri;
}

function get_route($request_uri) {
    $return = explode('/', $request_uri, 2);
    if (!isset($return[1]))
        $return[1] = '';

    if (empty($return[0]))
        $return[0] = \Pixelf\Config\get_config_parameter('default_controller');
    if (empty($return[1]))
        $return[1] = \Pixelf\Config\get_config_parameter('default_action');

    return $return;
}

function create_url($route, $params = array()) {
    $url = \Pixelf\Config\get_config_parameter('baseUrl').$route;
    if (!empty($params)) {
        $url .= '?'.http_build_query($params);
    }
    return $url;
}

function create_absolute_url($route, $params = array()) {
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    return '//'.$host.create_url($route, $params);
}

function redirect($route, $params = array(), $terminate = true) {
    header('Location: '.create_url($route, $params));
    if ($terminate)
        die;
}

function render_file($file, $context) {
    require_once dirname(__FILE__).'/../../lib/Twig/Autoloader.php';
    \Twig_Autoloader::register();

    $loader = new \Twig_Loader_Filesystem(dirname(__FILE__).'/../views/');
    $twig = new \Twig_Environment($loader, array(
//        'cache' => dirname(__FILE__).'/../runtime/',
    ));

    $get_param = new \Twig_SimpleFunction('get_param', function ($parameter_name) {
        return \Pixelf\Config\get_config_parameter($parameter_name);
    });
    $twig->addFunction($get_param);

    $create_url = new \Twig_SimpleFunction('create_url', function ($route, $params = array()) {
        return create_url($route, $params);
    });
    $twig->addFunction($create_url);

    $create_absolute_url = new \Twig_SimpleFunction('create_absolute_url', function ($route, $params = array()) {
        return create_absolute_url($route, $params);
    });
    $twig->addFunction($create_absolute_url);

    $template = $twig->loadTemplate($file);
    echo $template->render($context);
}

function ref_values($arr) {
    $refs = array();

    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }

    return $refs;
}