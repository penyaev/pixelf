<?php
/**
 * kts, 2014
 * User: penyaev
 * Date: 10.06.14
 * Time: 0:21
 */

namespace Pixelf\Helpers\leads;
require_once dirname(__FILE__).'/../models/lead.php';
require_once dirname(__FILE__).'/../helpers/helpers.php';

const API_ENDPOINT = 'https://api.vk.com';

function complete_lead($session_id) {
    $session = \Pixelf\Models\lead\get_session_by_session_id($session_id);
    if (empty($session))
        return false;
    $lead = \Pixelf\Models\lead\get_by_lead_id(\Pixelf\Helpers\get_value($session, 'lead_id'));
    if (empty($lead))
        return false;

    \Pixelf\Models\lead\close_session($session_id);

    $parameters = array(
        'vk_sid' => \Pixelf\Helpers\get_value($session, 'vk_sid'),
        'secret' => \Pixelf\Helpers\get_value($lead, 'secret'),
    );
    $query = http_build_query($parameters);
    $response = @file_get_contents(API_ENDPOINT.'/method/leads.complete?'.$query);

    return true;
}

function validate_hash($vk_hash, $vk_sid, $vk_lead_id, $vk_uid, $lead_secret) {
    $valid_hash = md5(implode('_', array(
        $vk_sid,
        $vk_lead_id,
        $vk_uid,
        $lead_secret
    )));

    return $valid_hash === $vk_hash;
}
