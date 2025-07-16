<?php
function renderCo2Shortcode($attrs) {
    global  $nsiTemplateParser;

	add_filter('the_content', function ($content) {
		if (has_shortcode($content, 'nsi-co2')) {
			$content = shortcode_unautop($content);
		}
		return $content;
	});

    $a = shortcode_atts(array(
        'from' => '',
        'to'  =>  ''
    ), $attrs );

    $from = $a['from'];
    $to = $a['to'];

    $result = fetchData("/CO2/{$from}/{$to}");
    if (is_null($result) || !isset($result->data)) {
        return 'No data available for the specified CO2 emissions.';
    }

    $data = $result->data;
    return $nsiTemplateParser->render('co2', $data);
}

add_shortcode('nsi-co2', 'renderCo2Shortcode');