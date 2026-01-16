<?php
if (!defined('ABSPATH')) exit;

add_action('init', 'nsi_wrap_shortcodes', 99);

function nsi_wrap_shortcodes() {
    $my_shortcodes = [
        'ns-international-search',
        'ns-international-calendar',
        'ns-international-dayschedule',
        'ns-international-short-dayschedule',
        'ldjson',
        'nsi-price',
        'nsi-co2'
    ];

    global $shortcode_tags;

    foreach ($my_shortcodes as $tag) {
        if (!isset($shortcode_tags[$tag])) {
            continue;
        }

        $original_callback = $shortcode_tags[$tag];

        add_shortcode($tag, function($attrs = [], $content = '', $tagname = '') use ($original_callback) {            
            if (isset($attrs['from']) && $attrs['from'] !== '') {
                $attrs['from'] = StationsReplacer::replace($attrs['from']);
            }

            if (isset($attrs['to']) && $attrs['to'] !== '') {
                $attrs['to'] = StationsReplacer::replace($attrs['to']);
            }

            return call_user_func($original_callback, $attrs, $content, $tagname);
        });
    }
}