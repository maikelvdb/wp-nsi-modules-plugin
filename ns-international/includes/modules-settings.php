<?php

function setModuleSettings() {
    $tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');

    $text_values = [];
    foreach (TextValues::DEFAULTS as $key => $value) {
        $text_values[$key] = TextValues::get($key);
    }

    $jsarray = array(
        'tracking_code'               => $tracking_code,
        'text_values'                 => $text_values,
    );
    wp_localize_script( 'modules-js', 'php_vars', $jsarray);
    wp_localize_script( 'search-from-js', 'php_vars', $jsarray);
    wp_localize_script( 'calendar-js', 'php_vars', $jsarray);
    wp_localize_script( 'day-schedule-js', 'php_vars', $jsarray);
    wp_localize_script( 'settings-js', 'php_vars', $jsarray);

}
