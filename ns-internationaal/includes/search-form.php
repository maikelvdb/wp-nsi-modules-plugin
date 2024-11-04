<?php
include_once 'includes.php';

function renderNsInternationalSearch($atts) {
    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  ''
    ), $atts );

    if (empty($a['from'])) {
        $default = get_option(Constants::DEFAULT_FROM_STATION, '');
        $a['from'] = $default;
    }


	$content = "<div class=\"ns-international_searchform ns-from\"><form action=\"#\" methode=\"get\" class=\"form-container\">";
        $content .= renderNsStationsSelect("Vertrekken vanaf", "from", $a['from']);
        $content .= renderNsStationsSelect("Bestemming", "to", $a['to']);
        $content .= render_input("Vertrek op", "text", "date", date('d-m-Y', strtotime(date('Y-m-d') . ' +1 day')), "js-date", ["min" => date('Y-m-d')]);
        $content .= "<div class=\"button-wrapper\">" . render_button("Zoeken") . "</div>";
    $content .= "</div></div>";

    return $content;
}

add_shortcode('ns-international-search', 'renderNsInternationalSearch');