<?php
function renderPriceShortcode($attrs) {
	add_filter('the_content', function ($content) {
		if (has_shortcode($content, 'nsi-price')) {
			$content = shortcode_unautop($content);
		}
		return $content;
	});

    $a = shortcode_atts(array(
        'from' => '',
        'to'  =>  '',
        'date' => date('Y-m-d'),
    ), $attrs );

    $from = $a['from'];
    $to = $a['to'];
    $date = $a['date'];
    if (!empty($a['date-addition'])) {
        $date = date('Y-m-d', strtotime($date . ' ' . $a['date-addition']));
    }

    if (empty($from) || empty($to) || empty($date)) {
        return '';
    }

    $response = fetchData("/Calendar/{$from}/{$to}");
    $data = $response ? $response->data : null;
    if (is_null($data) || !isset($data['calendarEntries'])) {
        return '';
    }

    $calendarEntries = $data['calendarEntries'];

    $entry = null;
    foreach ($calendarEntries as $item) {
        if ($item['calendarDate'] === $date) {
            $entry = $item;
            break;
        }
    }

    if (is_null($entry)) {
        return '';
    }

    $price = $entry['price']['lowest'] ?? '0.00';

    $content = "<span class='nsi-price'>&euro; " . esc_html($price) . "</span>";
    return $content;
}

add_shortcode('nsi-price', 'renderPriceShortcode');

add_filter('the_title', function($title, $id) {
    if ( is_singular() && has_shortcode($title, 'nsi-price') ) {
        return do_shortcode($title);
    }
    return $title;
}, 10, 2);

add_filter( 'nav_menu_item_title', 'nsi_price_change_menu_titles', 10, 4 );

function nsi_price_change_menu_titles( $title, $item, $args, $depth ) {
    return do_shortcode($title);
}