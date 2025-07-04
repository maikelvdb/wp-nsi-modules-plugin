<?php
function render_button($label, $type = "submit", $className = "", $attributes = []) {
    $Content = "<button type=\"" . esc_attr($type) . "\"";

    foreach ($attributes as $key => $val) {
        $Content .= " " . esc_attr($key) . "=\"" . esc_attr($val) . "\"";
    }

    $Content .= " class=\"" . esc_attr($className) . " button\">" . esc_html($label) . "</button>";

    return $Content;
}