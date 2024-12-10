<?php
include_once 'includes.php';


function renderNsInternationalDayschedule($attrs) {
    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  '',
        'date-addition' => '+1 day'
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

    $fromDate = date('d-m-Y', strtotime(date('Y-m-d') . ' ' . $a['date-addition']));
    $content = "<div class=\"ns-international-dayschedule\" data-from=\"" . $a['from'] . "\" data-to=\"" . $a['to'] . "\" data-date=\"" . $fromDate . "\">";

        $content .= "<div class=\"form form-container\">" . getForm($a['from'], $a['to'], $fromDate) . "</div>";
        $content .= "<div class=\"schedule\"></div>";

    $content .= '</div>';

    return $content;
}

function getForm($from, $to, $date) {
    $form = renderNsStationsSelect(TextValues::get("from"), "from", $from);
    $form .= renderNsStationsSelect(TextValues::get("to"), "to", $to);
    $form .= render_input(TextValues::get("date"), "text", "date", $date, "js-date", ["min" => date('Y-m-d')]);

    return $form;
}


add_shortcode('ns-international-dayschedule', 'renderNsInternationalDayschedule');
