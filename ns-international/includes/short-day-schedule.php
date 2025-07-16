<?php
function renderNsInternationalShortDayschedule($attrs) {
    global  $nsiTemplateParser;

    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  '',
        'date' => date('Y-m-d'),
    ), $attrs );

    if (empty($a['to'])) {
        return "'to' attribute is required";
    }

    if (empty($a['from'])) {
        $default = get_option(Constants::DEFAULT_FROM_STATION, '');
        $a['from'] = $default;

        if (empty($a['from'])) {
            return "'from' attribute is required";
        }
    }

    $from = $a['from'];
    $to = $a['to'];
    $date = $a['date'];

    $response = fetchData("/SearchQueue/$from/$to/$date");
    if (!isset($response->data) || empty($response->data)) {
        return "";
    }

    return $nsiTemplateParser->render('short-dayschedule', $response->data);
}

add_shortcode('ns-international-short-dayschedule', 'renderNsInternationalShortDayschedule');
