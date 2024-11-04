<?php
include_once 'includes.php';

function renderNsInternationalCalendar($attrs) {
    $a = shortcode_atts( array(
        'from' => '',
        'to'  =>  '',
        'min-date' => '',
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

    $dateStr = $a['min-date'];
    if (empty($dateStr)) {
        $dateStr = date('Y-m-d');
    }

    $tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');

    $content = "<div class=\"ns-international_calendar\" data-from=\"{$a['from']}\" data-to=\"{$a['to']}\" data-min-date=\"{$a['min-date']}\" data-current-index=\"0\">";

    $date = new DateTime($dateStr);
    $content .= "<div class=\"ns-calendar-header\">".
        "<div class=\"ns-calendar-header-left\"><button class=\"prev\">&lt;</button></div>".
        "<div class=\"ns-calendar-header-center js-active-date\">" . $date->format('F Y') . "</div>".
        "<div class=\"ns-calendar-header-right\"><button class=\"next\">&gt;</button></div>".
    "</div>";

        $content .= "<div class=\"ns-calendar-container\">";
    
        $content .= createCalendar($date->format('Y-m-d'), 0, $tracking_code, $a['from'], $a['to']);

        for ($x = 1; $x <= 11; $x++) {
            $newDate = clone $date;
            $newDate->modify("+{$x} month");
            
        $content .= createCalendar($newDate->format('Y-m-d'), $x, $tracking_code, $a['from'], $a['to']);
        }

        $content .= "</div>";
    $content .= "</div>";

    return $content;
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
    $content = "<div class=\"ns-calendar {$activeClass}\" data-date-str=\"{$firstDayDate->format('F Y')}\" data-date=\"{$firstDayDate->format('Y-m-d')}\" data-index=\"{$index}\">";
    $content .= "<div class=\"row header\">" .
        "<div class=\"cell\">Mon</div><div class=\"cell\">Tue</div><div class=\"cell\">Wed</div>" .
        "<div class=\"cell\">Thu</div><div class=\"cell\">Fri</div><div class=\"cell\">Sat</div><div class=\"cell\">Sun</div>" .
    "</div>";
    
    $currentDate = clone $startDate;

    while ($currentDate <= $endDate) {
        $isCurrentMonth = $currentDate->format('m') == $month;
        if ($currentDate->format('N') == 1) {
            $content .= "<div class=\"row\">";
        }

        $isDisabled = $isCurrentMonth ? '' : 'disabled';
        $dayNr = $currentDate->format('j');
        $content .= "<a href=\"" . getUrl($from, $to, $currentDate->format('Ymd'), $tracking_code) . "\" target=\"_blank\" class=\"cell {$isDisabled}\" data-date=\"" . $currentDate->format('Y-m-d') . "\"><div class=\"daynr\" data-day=\"" . $dayNr . "\"></div><div class=\"price\">";
        
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
