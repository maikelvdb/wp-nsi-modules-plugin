<?php

function renderNsStationsSelect($label, $name, $value, $id, $className = "") {
    $content = '<div class="select-wrapper">';
    $content .= '<div class="nsi-label filter-input ' . esc_attr($className) . '">';
    $content .= '<input type="text" id="' . esc_attr($id) . '" placeholder="' . esc_attr($label) . '" data-name="' . esc_attr($name) . '" name="' . esc_attr($name) . '_display" value="" />';
    $content .= '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" />';
    $content .= '</div>';
    $content .= '</div>';
    
    return $content;
}