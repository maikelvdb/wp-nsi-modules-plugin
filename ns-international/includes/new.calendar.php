<?php
include_once 'includes.php';

function renderNsInternationalCalendar($attrs) {
    global $templateParser;

    $skipDays = get_option(Constants::SKIP_DAYS, '70');
    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  '',
        'min-date' => '',
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

    $from = $a['from'];
    $to = $a['to'];
    $response = fetchData("/Calendar/{$from}/{$to}");
    $data = $response ? $response->data : null;

    $dateStr = $a['min-date'];
    if (empty($dateStr)) {
        $dateStr = date('Y-m-d');
    }

    // Skip days
    $date = new DateTime($dateStr);
    $startDate = new DateTime($dateStr);

    $startDate->modify($a['date-addition']);

    // get month floor diff but also with next year
    $monthDiff = $date->diff($startDate)->m + ($date->diff($startDate)->y * 12);
    $startIndex = $monthDiff;

    $tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');

    $months = array();
    for ($x = 0; $x <= 11; $x++) {
        $newDate = clone $date;
        $newDate->modify("+{$x} month");

        $months[] = [
            "" => $newDate,
            "firstDay" => $newDate->format('Y-m-d'),
        // $content .= createCalendar($newDate->format('Y-m-d'), $x, $tracking_code, $from, $to, $data);
    }


    return $templateParser->render('calendar', [
        'maxWidth' => $maxWidth,
        'marginBottom' => $marginBottom,
        'from' => $a['from'],
        'to' => $a['to'],
        'minDate' => $a['min-date'],
        'tracking_code' => $tracking_code,
        'startDate' => $startDate,
        'startIndex' => $startIndex,

        'dates' => $months,
    ]);
}

function createCalendar($date, $index, $tracking_code, $from, $to, $data) {
    $ldCollection = array();
    $startDate = new DateTime($date);
    $month = $startDate->format('m');

    $startDate->modify('first day of this month');
    $firstDayDate = clone $startDate;

    if ($startDate->format('N') != 1) {
        $startDate->modify('last monday');
    }

    $endDate = new DateTime($date);
    $endDate->modify('last day of this month');

    if ($endDate->format('N') != 7) {
        $endDate->modify('next sunday');
    }

    $activeClass = $index == 0 ? 'active' : '';
    $monthName = getNLMonth($firstDayDate);
    $content = "<div class=\"ns-calendar {$activeClass}\" data-date-str=\"{$monthName}\" data-date=\"{$firstDayDate->format('Y-m-d')}\" data-index=\"{$index}\">";
    
    $content .= "<div class=\"row header\">" .
        "<div class=\"cell\">M</div><div class=\"cell\">D</div><div class=\"cell\">W</div>" .
        "<div class=\"cell\">D</div><div class=\"cell\">V</div><div class=\"cell\">Z</div><div class=\"cell\">Z</div>" .
    "</div>";
    
    $currentDate = clone $startDate;

    while ($currentDate <= $endDate) {
        $isCurrentMonth = $currentDate->format('m') == $month;
        if ($currentDate->format('N') == 1) {
            $content .= "<div class=\"row\">";
        }

        $isDisabled = $isCurrentMonth ? '' : ' disabled';
        $dayNr = $currentDate->format('j');
        $item = getMatch($data, $currentDate);
        
        $searchIcon = $isCurrentMonth && empty($item) ? ' search-icon' : '';

        $className = '';
        if (!empty($item) && isset($item['price']['category']) && $isCurrentMonth) {
            $className = $item['price']['category'];
            $className = strtolower($className);
        }
        
        $content .= "<a href=\"" . getUrl($from, $to, $currentDate->format('Ymd'), $tracking_code) . "\" target=\"_blank\" class=\"cell nsi-cta{$isDisabled}{$searchIcon}\" data-date=\"" . $currentDate->format('Y-m-d') . "\"><div class=\"daynr\" data-day=\"" . $dayNr . "\"></div><div class=\"price {$className}\">";
        
        if ($isCurrentMonth) {
            if (!empty($item)) {
                $price = toPrice($item['price']['lowest']);
                $price = trim($price);
                $price = str_replace('€', '', $price);

                $content .= "<div class=\"current\">{$price}</div>";

                array_push($ldCollection, array(
                    'from' => $from,
                    'to' => $to,
                    // 'departure' => $item['firstTravelConnection']['departureDate'],
                    'departure' => $currentDate->format('Y-m-d'),
                    // 'arrival' => $item['firstTravelConnection']['arrivalDate'],
                    'arrival' => null,
                    'price' => $item['price']['lowest']
                ));
            }
            else {
                $content .= "<svg width=\"20\" height=\"20\" class=\"search-icon\" role=\"img\" viewBox=\"2 9 20 5\" focusable=\"false\" aria-label=\"Search\"><path class=\"search-icon-path\" d=\"M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z\"></path></svg>";
                
                array_push($ldCollection, array(
                    'from' => $from,
                    'to' => $to,
                    'departure' => $currentDate->format('Y-m-d'),
                    'arrival' => null,
                    'price' => null,
                ));
            }
        }

        $content .= "</div></a>";

        if ($currentDate->format('N') == 7) {
            $content .= "</div>";
        }

        $currentDate->modify('+1 day');
    }

    $content .= "</div>";

    if (!empty($ldCollection)) {
        $content .= renderLdjsonWithoutStations($ldCollection, "ldjson-calendar-{$from}-{$to}-{$firstDayDate->format('Y-m')}");
    }

    return $content;
}

function getMatch($data, $date) {
    if (is_null($data) || !isset($data['calendarEntries'])) {
        return null;
    }

    foreach ($data['calendarEntries'] as $item) {
        if ($item['calendarDate'] === $date->format('Y-m-d')) {
            return $item;
        }
    }

    return null;
}

function toPrice($price): string {
    // Ensure it is numeric
    if (!is_numeric($price)) {
        $price = floatval($price);
    }

    // Create NumberFormatter for nl_NL and EUR
    $formatter = new NumberFormatter('nl_NL', NumberFormatter::CURRENCY);
    $formatter->setTextAttribute(NumberFormatter::CURRENCY_CODE, 'EUR');
    $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, 0);

    return $formatter->formatCurrency($price, 'EUR');
}

function getUrl($from, $to, $date, $tracking_code) {
    return "https://www.nsinternational.com/traintracker/?tt={$tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F{$from}%2F{$to}%2F{$date}";
}


add_shortcode('ns-international-calendar', 'renderNsInternationalCalendar');
