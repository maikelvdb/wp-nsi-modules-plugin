<?php
include_once 'includes.php';

function renderNsInternationalCalendar($attrs) {
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

    $content = "<div class=\"ns-international_calendar\" style=\"--nsi-max-width: {$maxWidth}px; --nsi-margin-bottom: {$marginBottom}px;\" data-from=\"{$a['from']}\" data-to=\"{$a['to']}\" data-min-date=\"{$a['min-date']}\" data-current-index=\"{$startIndex}\">";
    
    $content .= "<div class=\"ns-calendar-header\">".
        "<div class=\"ns-calendar-header-left\"><button class=\"prev\"><div class=\"arrow\"></div></button></div>".
        "<div class=\"ns-calendar-header-center js-active-date\">" . getNLMonth($startDate) . "</div>". //$date->format('F Y')
        "<div class=\"ns-calendar-header-right\"><button class=\"next\"><div class=\"arrow\"></div></button></div>".
    "</div>";

        $content .= "<div class=\"ns-calendar-container\"><div class=\"ns-calendar-slider\">";
    
        $content .= createCalendar($date->format('Y-m-d'), 0, $tracking_code, $a['from'], $a['to']);

        for ($x = 1; $x <= 11; $x++) {
            $newDate = clone $date;
            $newDate->modify("+{$x} month");
            
            $content .= createCalendar($newDate->format('Y-m-d'), $x, $tracking_code, $a['from'], $a['to']);
        }

        $content .= "</div>";
        $content .= "</div>";
    $content .= "</div>";

    return $content;
}

function getNLMonth($date) {
    $month = $date->format('m');
    // no leading zero
    $month = ltrim($month, '0');

    switch ($month) {
        case 1:
            return 'januari';
        case 2:
            return 'februari';
        case 3:
            return 'maart';
        case 4:
            return 'april';
        case 5:
            return 'mei';
        case 6:
            return 'juni';
        case 7:
            return 'juli';
        case 8:
            return 'augustus';
        case 9:
            return 'september';
        case 10:
            return 'oktober';
        case 11:
            return 'november';
        case 12:
            return 'december';
        default:
            return 'Onbekende maand...';
    }
    
}

function createCalendar($date, $index, $tracking_code, $from, $to) {
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
        $content .= "<a href=\"" . getUrl($from, $to, $currentDate->format('Ymd'), $tracking_code) . "\" target=\"_blank\" class=\"cell nsi-cta{$isDisabled}\" data-date=\"" . $currentDate->format('Y-m-d') . "\"><div class=\"daynr\" data-day=\"" . $dayNr . "\"></div><div class=\"price\">";
        
        if ($isCurrentMonth) {
            $content .= "<div class=\"loader\"></div>";
        }

        $content .= "</div></a>";

        if ($currentDate->format('N') == 7) {
            $content .= "</div>";
        }

        $currentDate->modify('+1 day');
    }

    $content .= "</div>";

    return $content;
}


function getUrl($from, $to, $date, $tracking_code) {
    return "https://www.nsinternational.com/traintracker/?tt={$tracking_code}&r=%2Fnl%2Ftreintickets-v3%2F%23%2Fsearch%2F{$from}%2F{$to}%2F{$date}";
}


add_shortcode('ns-international-calendar', 'renderNsInternationalCalendar');
