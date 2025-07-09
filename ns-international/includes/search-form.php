<?php
function renderNsInternationalSearch($atts) {
    global $templateParser;

	add_filter('the_content', function ($content) {
		if (has_shortcode($content, 'ns_international_search')) {
			$content = shortcode_unautop($content);
		}
		return $content;
	});
	
	
    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  ''
    ), $atts );

    if (empty($a['from'])) {
        $default = get_option(Constants::DEFAULT_FROM_STATION, '');
        $a['from'] = $default;
    }


    $maxWidth = get_option(Constants::MAX_WIDTH, '900');
    $marginBottom = get_option(Constants::SPACING_BOTTOM, '15');
	$id = uniqid();


	return $templateParser->render('search-form', [
		'id' => $id,
		
		'maxWidth' => $maxWidth,
		'marginBottom' => $marginBottom,

		'from' => $a['from'],
		'fromLabel' => TextValues::get("from"),

		'to' => $a['to'],
		'toLabel' => TextValues::get("to"),

		'dateLabel' => TextValues::get("date"),
		'dateValue' => date('d-m-Y', strtotime(date('Y-m-d') . ' +1 day')),
		'minDate' => date('Y-m-d'),
		
		'buttonLabel' => TextValues::get("search")
	]);
}

add_shortcode('ns-international-search', 'renderNsInternationalSearch');
