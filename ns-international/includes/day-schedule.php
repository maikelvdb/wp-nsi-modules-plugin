<?php
include_once 'includes.php';


function renderNsInternationalDayschedule($attrs) {
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

    
    $maxWidth = get_option(Constants::MAX_WIDTH, '900');
    $marginBottom = get_option(Constants::SPACING_BOTTOM, '15');

    $fromDate = date('d-m-Y', strtotime(date('Y-m-d') . ' ' . $a['date-addition']));
    $content = "<div class=\"ns-international-dayschedule\" style=\"--nsi-max-width: {$maxWidth}px; --nsi-margin-bottom: {$marginBottom}px;\" data-from=\"" . $a['from'] . "\" data-to=\"" . $a['to'] . "\" data-date=\"" . $fromDate . "\">";

        $content .= "<div class=\"form form-container\">" . getForm($a['from'], $a['to'], $fromDate) . "</div>";
        $content .= "<div class=\"schedule\"></div>";

    $content .= '</div>';

    return $content;
}

function getForm($from, $to, $date) {
    $id = uniqid();
    $form = renderNsStationsSelect(TextValues::get("from"), "from", $from, $id . '_from');
    $form .= renderNsStationsSelect(TextValues::get("to"), "to", $to, $id . '_to');
    $form .= render_input(TextValues::get("date"), "text", "date", $date, "js-date", ["min" => date('Y-m-d')]);

    return $form;
}


add_shortcode('ns-international-dayschedule', 'renderNsInternationalDayschedule');
