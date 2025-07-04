<?php
function renderNsInternationalShortDayschedule($attrs) {
    $skipDays = get_option(Constants::SKIP_DAYS, '70');
    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  '',
        'date-addition' => "+$skipDays day"
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

    ob_start();
?>

<h1>Dayschedule short</h1>



<?php
    return ob_get_clean();
}

add_shortcode('ns-international-short-dayschedule', 'renderNsInternationalShortDayschedule');
