<?php
include_once 'includes/shared.php';
require_once 'renderer/templates.php';
include_once 'includes/text-values.php';
include_once 'includes/stations-replacer.php';
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
include_once 'includes/shortcode-interceptor.php'; 

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

    styleWithDate('modules-style',  "styles/modules.css");

    scriptWithDate('tabs-js',  "scripts/tabs.js", array('jquery'));
    scriptWithDate('shared-modules-js',  "scripts/shared.js", array('jquery', 'jquery-ui-datepicker'));
    scriptWithDate('modules-js',  "scripts/modules.js", array('jquery', 'jquery-ui-datepicker'));
    scriptWithDate('search-from-js',  "scripts/search-from.js", array('jquery', 'jquery-ui-datepicker'));
    scriptWithDate('calendar-js',  "scripts/calendar.js", array('jquery', 'jquery-ui-datepicker'));
    scriptWithDate('day-schedule-js',  "scripts/day-schedule.js", array('jquery', 'jquery-ui-datepicker'));

    styleWithDate('settings-style',  "styles/settings.css");
    scriptWithDate('settings-js',  "scripts/settings.js", array('jquery'));
}
add_action('wp_enqueue_scripts', 'nsiEnqueueScripts');
add_action('admin_enqueue_scripts', 'nsiEnqueueScripts');
add_action('wp_enqueue_scripts', 'setModuleSettings', 20);
add_action('admin_enqueue_scripts', 'setModuleSettings', 20);

setPluginSettings();

function styleWithDate($key, $path) {
    $plugin_path = plugin_dir_path(__FILE__);
    $plugin_url = str_replace("/includes", "", plugin_dir_url( __FILE__ ));

    wp_enqueue_style($key, $plugin_url . $path, [], filemtime($plugin_path . $path), "all");
}


function scriptWithDate($key, $path, $dependencies = array()) {
    $plugin_path = plugin_dir_path(__FILE__);
    $plugin_url = str_replace("/includes", "", plugin_dir_url( __FILE__ ));

    wp_enqueue_script($key, $plugin_url . $path, $dependencies, filemtime($plugin_path . $path));
}
