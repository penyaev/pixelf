<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 26.05.14
 * Time: 17:41
 */

namespace Pixelf\Controllers\search;
require_once dirname(__FILE__).'/../helpers/helpers.php';
require_once dirname(__FILE__).'/../models/site.php';
require_once dirname(__FILE__).'/../models/user.php';

function action_search() {
    $q = isset($_GET['q']) ? $_GET['q'] : null;
    if (!empty($q)) {
        if (strpos($q, \Pixelf\models\site\get_site_prefix()) === 0) { // looks like site uid
            $site = \Pixelf\models\site\select_by_uid($q);
            if (!empty($site)) {
                \Pixelf\helpers\redirect('sites/view', array('site_id' => $site['site_id']));
            }
        }
        if (strpos($q, \Pixelf\models\user\get_user_prefix()) === 0) { // looks like user uid
            \Pixelf\helpers\redirect('users/view', array('user_uid' => $q));
        }
        $site = \Pixelf\models\site\select_by_domain($q);
        if (!empty($site)) {
            \Pixelf\helpers\redirect('sites/view', array('site_id' => $site['site_id']));
        }
    }
    render('search', array(
        'search_query' => $q,
    ));
}

function render($view, $data = array()) {
    \Pixelf\Helpers\render_file('search/'.$view.'.twig', $data);
}