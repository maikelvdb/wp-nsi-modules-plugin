<?php
include_once 'includes/shared.php';
require_once 'renderer/templates.php';
include_once 'includes/text-values.php';
include_once 'includes/settings.php';
include_once 'includes/modules-settings.php';
include_once 'includes/plugin-settings.php';
include_once 'includes/ldjson.php';
include_once 'includes/search-form.php';
include_once 'includes/calendar.php';
include_once 'includes/day-schedule.php';
include_once 'includes/short-day-schedule.php';
include_once 'includes/price.php';
include_once 'includes/co2.php';

setlocale(LC_TIME, 'NL_nl');
define('NSI_PLUGIN_URL', plugin_dir_url(__FILE__));

global $nsiTemplateParser;
$nsiTemplateParser = new NsiTemplateParser(plugin_dir_path(__FILE__) . 'templates');

function nsiEnqueueScripts() {
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

    $plugin_url = str_replace("/includes", "", plugin_dir_url( __FILE__ ));
    wp_enqueue_style('modules-style',  $plugin_url . "styles/modules.css");

    wp_enqueue_script('shared-modules-js',  $plugin_url . "scripts/shared.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('modules-js',  $plugin_url . "scripts/modules.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('search-from-js',  $plugin_url . "scripts/search-from.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('calendar-js',  $plugin_url . "scripts/calendar.js", array('jquery', 'jquery-ui-datepicker'), false);
    wp_enqueue_script('day-schedule-js',  $plugin_url . "scripts/day-schedule.js", array('jquery', 'jquery-ui-datepicker'), false);

    wp_enqueue_style('settings-style',  $plugin_url . "styles/settings.css");
    wp_enqueue_script('settings-js',  $plugin_url . "scripts/settings.js", array('jquery'), false);
}
add_action('wp_enqueue_scripts', 'nsiEnqueueScripts');
add_action('admin_enqueue_scripts', 'nsiEnqueueScripts');
add_action('wp_enqueue_scripts', 'setModuleSettings', 20);
add_action('admin_enqueue_scripts', 'setModuleSettings', 20);

setPluginSettings();
