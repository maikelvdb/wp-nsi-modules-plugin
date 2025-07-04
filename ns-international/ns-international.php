<?php
/**
* Plugin Name: NS International modules
* Plugin URI: https://www.maikelvdb.nl/
* Description: Voegt de mogelijkheid toe om NS Internationaal reis modules toe te voegen aan je website.
* Version: 0.0.4
* Author: Maikel van den Bosch
* Author URI: https://www.maikelvdb.nl/
**/

remove_filter('the_content', 'wpautop');
remove_filter('the_content', 'shortcode_unautop');
add_filter('the_content', function($content) {
    return $content;
}, 9999);

include_once( 'startup.php' );
