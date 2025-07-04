<?php

function render_input($label, $type, $name, $value, $className = "", $attributes = []) {
    $Content = "<div class=\"nsi-label input " . esc_attr($className) . "\">";
    $Content .= "<input type=\"" . esc_attr($type) . "\" placeholder=\"" . esc_attr($label) . "\" id=\"" . esc_attr($name) . "\" name=\"" . esc_attr($name) . "\" value=\"" . esc_attr($value) . "\"";

    foreach ($attributes as $key => $val) {
        $Content .= " " . esc_attr($key) . "=\"" . esc_attr($val) . "\"";
    }

    $Content .= " />";
    $Content .= "</div>";

    return $Content;
}