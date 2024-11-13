<?php

function renderNsStationsSelect($label, $name, $value, $className = "") {
    $content = '<div class="filter-input ' . $className . '">';
        $content .= '<label for="' . $name . '">' . $label . '</label>';
        $content .= '<div class="select-wrapper">';
            $content .= '<input type="text" data-name="' . $name . '" name="' . $name . '_display" value="" />';
            $content .= '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $value . '" />';

        $content .= '</div>';
    $content .= '</div>';

    return $content;
}
