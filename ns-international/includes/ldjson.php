<?php
include_once 'includes.php';

function renderLdjson($collection, $id = null) {
	add_filter('the_content', function ($content) {
		if (has_shortcode($content, 'ns_international_search')) {
			$content = shortcode_unautop($content);
		}
		return $content;
	});

    $tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');
    $baseUrl = get_site_url();
    $image = $baseUrl . "/wp-content/plugins/ns-international/styles/gtk-ns-logo.png";

    $graphItems = [];
    foreach ($collection as $a) {
        $dateOnly = date('Ymd', strtotime($a['departure']));
        $timeOnly = date('Hi', strtotime($a['departure']));
        $timeOnlyArrival = null;
        if (isset($a['arrival'])) {
            $timeOnlyArrival = date('Hi', strtotime($a['arrival']));
        }

        $trackingUrl = "https://www.nsinternational.com/traintracker/?tt={$tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F{$a['from']}%2F{$a['to']}%2F{$dateOnly}";
        if ($timeOnly && $timeOnly !== '0000') {
            $trackingUrl .= "%2F{$timeOnly}";
        }
        if ($timeOnlyArrival) {
            $trackingUrl .= "%2F{$timeOnlyArrival}";
        }
    
        $from = $a['from'];
        $to = $a['to'];
        $date = $a['departure'];
        $price = $a['price'];
        
        $fromStationResponse = fetchData("/stations/bene/" . $from);
        $fromStation = $fromStationResponse ? $fromStationResponse->data : null;
        $fromName = $fromStation['name'] ?? 'Onbekend';

        $toStationResponse = fetchData("/stations/bene/" . $to);
        $toStation = $toStationResponse ? $toStationResponse->data : null;
        $toName = $toStation['name'] ?? 'Onbekend';
    
        $trainTrip = array(
            "@type" => "TrainTrip",
            "arrivalStation" => array(
                "@type" => "TrainStation",
                "name" => $toName
            ),
            "arrivalTime" => $a['arrival'],
            "departureStation" => array(
                "@type" => "TrainStation",
                "name" => $fromName
            ),
            "departureTime" => $a['departure'],
            "offers" => array(
                "@type" => "Offer",
                "price" => $price,
                "priceCurrency" => "EUR",
                "url" => $trackingUrl
            ),
            "provider" => array(
                "@type" => "Organization",
                "name" => "NS International",
                "url" => "https://www.nsinternational.com"
            )
        );

        $nicePrice = !isset($price) || $price === '0.00' ? 'onbekend' : "€{$price}";
        $nameText = prepareText(TextValues::get('dl_product_name'), $fromName, $toName, $date, $nicePrice);
        $descriptionText = prepareText(TextValues::get('dl_product_description'), $fromName, $toName, $date, $nicePrice);
        
        $product = array(
            "@type" => "Product",
            "name" => $nameText, //"Trein van $fromName naar $toName op $date",
            "description" => $descriptionText, //"Goedkoopste treinkaartje van $fromName naar $toName op $date voor $nicePrice",
            "image" => array($image),
            "offers" => array(
                "@type" => "Offer",
                "price" => $price,
                "priceCurrency" => "EUR",
                "url" => $trackingUrl
            )
        );

        $graphItems[] = $trainTrip;
        if (isset($price)) {
            $graphItems[] = $product;
        }
    }

    $structuredData = array(
        "@context" => "https://schema.org",
        "@graph" => $graphItems
    );

    return '<script type="application/ld+json" id="'. esc_attr($id) . '">'
        . json_encode($structuredData, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
        . '</script>';
}

function renderLdjsonWithoutStations($collection, $id = null) {
	add_filter('the_content', function ($content) {
		if (has_shortcode($content, 'ns_international_search')) {
			$content = shortcode_unautop($content);
		}
		return $content;
	});

    $tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');
    $baseUrl = get_site_url();
    $image = $baseUrl . "/wp-content/plugins/ns-international/styles/gtk-ns-logo.png";

    $graphItems = [];
    foreach ($collection as $a) {
        $dateOnly = date('Ymd', strtotime($a['departure']));
        $timeOnly = date('Hi', strtotime($a['departure']));
        $timeOnlyArrival = null;
        if (isset($a['arrival'])) {
            $timeOnlyArrival = date('Hi', strtotime($a['arrival']));
        }

        $trackingUrl = "https://www.nsinternational.com/traintracker/?tt={$tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F{$a['from']}%2F{$a['to']}%2F{$dateOnly}";
        if ($timeOnly && $timeOnly !== '0000') {
            $trackingUrl .= "%2F{$timeOnly}";
        }
        if ($timeOnlyArrival) {
            $trackingUrl .= "%2F{$timeOnlyArrival}";
        }
    
        $fromName = $a['from'];
        $toName = $a['to'];
        $date = $a['departure'];
        $price = $a['price'];
    
        $trainTrip = array(
            "@type" => "TrainTrip",
            "arrivalStation" => array(
                "@type" => "TrainStation",
                "name" => $toName
            ),
            "arrivalTime" => $a['arrival'],
            "departureStation" => array(
                "@type" => "TrainStation",
                "name" => $fromName
            ),
            "departureTime" => $a['departure'],
            "offers" => array(
                "@type" => "Offer",
                "price" => $price,
                "priceCurrency" => "EUR",
                "url" => $trackingUrl
            ),
            "provider" => array(
                "@type" => "Organization",
                "name" => "NS International",
                "url" => "https://www.nsinternational.com"
            )
        );

        $nicePrice = !isset($price) || $price === '0.00' ? 'onbekend' : "€{$price}";
        $nameText = prepareText(TextValues::get('dl_product_name'), $fromName, $toName, $date, $nicePrice);
        $descriptionText = prepareText(TextValues::get('dl_product_description'), $fromName, $toName, $date, $nicePrice);
        
        $product = array(
            "@type" => "Product",
            "name" => $nameText, //"Trein van $fromName naar $toName op $date",
            "description" => $descriptionText, //"Goedkoopste treinkaartje van $fromName naar $toName op $date voor $nicePrice",
            "image" => array($image),
            "offers" => array(
                "@type" => "Offer",
                "price" => $price,
                "priceCurrency" => "EUR",
                "url" => $trackingUrl
            )
        );

        $graphItems[] = $trainTrip;
        if (isset($price)) {
            $graphItems[] = $product;
        }
    }

    $structuredData = array(
        "@context" => "https://schema.org",
        "@graph" => $graphItems
    );

    return '<script type="application/ld+json" id="'. esc_attr($id) . '">'
        . json_encode($structuredData, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)
        . '</script>';
}

function prepareText($text, $fromName, $toName, $date, $nicePrice) {
    $text = str_replace('{from}', $fromName, $text);
    $text = str_replace('{to}', $toName, $text);
    $text = str_replace('{date}', $date, $text);
    $text = str_replace('{price}', $nicePrice, $text);

    return $text;
}

function ldJsonStructuredData($attrs) {
    $a = shortcode_atts(array(
        'from' => '',
        'to'  =>  '',
        'date' => date('Y-m-d')
    ), $attrs );

    $from = $a['from'];
    $to = $a['to'];
    $date = $a['date'];
    if (empty($from) || empty($to) || empty($date)) {
        return '';
    }

    $response = fetchData("/Calendar/{$from}/{$to}");
    $data = $response ? $response->data : null;
    if (is_null($data) || !isset($data['calendarEntries'])) {
        return '';
    }

    $calendarEntries = $data['calendarEntries'];
    // Get item where calendarDate matches the provided date
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
    $departure = $entry['firstTravelConnection']['departureDate'] ?? $date . " 07:00:00";
    $arrival = $entry['firstTravelConnection']['arrivalDate'] ?? '';

    $input = [
        'from' => $from,
        'to' => $to,
        'departure' => $departure,
        'arrival' => $arrival,
        'price' => $price
    ];

    return renderLdjson([$input]);
}

add_shortcode('ldjson', 'ldJsonStructuredData');