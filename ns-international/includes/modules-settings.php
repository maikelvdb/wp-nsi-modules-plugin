<?php

function setModuleSettings() {
    $tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');
    $plugin_url = str_replace("/includes", "", plugin_dir_url( __FILE__ ));

    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'jquery-style' );
    wp_enqueue_script('jquery-ui-datepicker');

    wp_enqueue_style(
        'plugin_name-admin-ui-css',
        'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.0/themes/base/jquery-ui.min.css',
        false,
        "1.0",
        'all'
    );

    wp_enqueue_style('modules-style',  $plugin_url . "styles/modules.css");

    wp_enqueue_script('shared-modules-js',  $plugin_url . "scripts/shared.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('modules-js',  $plugin_url . "scripts/modules.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('search-from-js',  $plugin_url . "scripts/search-from.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('calendar-js',  $plugin_url . "scripts/calendar.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('day-schedule-js',  $plugin_url . "scripts/day-schedule.js", array('jquery', 'jquery-ui-datepicker'), false);

    wp_enqueue_style('settings-style',  $plugin_url . "styles/settings.css");
    wp_enqueue_script('settings-js',  $plugin_url . "scripts/settings.js", array('jquery'), false);


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