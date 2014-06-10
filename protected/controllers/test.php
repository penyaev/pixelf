<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 10.06.14
 * Time: 11:30
 */

namespace Pixelf\Controllers\test;

require_once dirname(__FILE__).'/../helpers/helpers.php';
require_once dirname(__FILE__).'/../models/site.php';


function action_fill_sites() {
    $total = 1000;
    $word_list = explode("\n", file_get_contents('google-10000-english.txt'));
    $domains_list = explode("\n", file_get_contents('tlds-alpha-by-domain.txt'));

    for ($i=1; $i <= $total; $i++) {
        $site_uid = \Pixelf\models\site\generate_site_uid();

        $domain_name = $word_list[array_rand($word_list)];
        $tld = strtolower($domains_list[array_rand($domains_list)]);
        $domain = $domain_name.'.'.$tld;
        \Pixelf\Models\site\insert($domain, $site_uid, rand(3,6));
    }
}

function action_load() {
    echo 'Start load simulation (random delays between requests)'.PHP_EOL;

    $sites = \Pixelf\models\site\select_all(0, 20000);
    if (empty($sites)) {
        echo 'Please create some sites first with fill_sites';
        die;
    }
    $delay_min = 12;
    $delay_max = 50;

    $start_time = microtime(true);
    $stats_step = 1000;
    for ($i=1;; $i++) {
        if (($i % $stats_step) == 0) {
            $time = microtime(true)-$start_time;
            $avg_rps = number_format($stats_step/$time, 2);
            $start_time = microtime(true);
            echo $stats_step.' requests done (total '.$i.') within last '.number_format($time, 1).' seconds, avg rps='.$avg_rps.PHP_EOL;
        }

        $site_uid = $sites[array_rand($sites)]['site_uid'];
        $url = 'url'.rand(1,100000);
        $user_id = 'pfu-'.rand(1,5000);

        $click_url = 'http://'.\Pixelf\Config\get_config_parameter('host').\Pixelf\Helpers\create_url('click/store', array(
                'pf_user_id' => $user_id,
                'site_uid' => $site_uid,
                'url' => $url,
            ));
        @file_get_contents($click_url);

        usleep(rand($delay_min, $delay_max)*100);
    }
    echo 'done'.PHP_EOL;
}


function action_fill_sessions() {
    echo 'Generating random sessions'.PHP_EOL;
}