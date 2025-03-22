<?php

function renderNsStationsSelect($label, $name, $value, $id, $className = "") {
    $content = '<label for="' . $id . '" class="filter-input ' . $className . '">';
        // $content .= '<span class="label">' . $label . '</span>';
        $content .= '<div class="select-wrapper">';
            $content .= '<input type="text" id="' . $id . '" placeholder="' . $label . '" data-name="' . $name . '" name="' . $name . '_display" value="" />';
            $content .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';

        $content .= '</div>';
    $content .= '</label>';

    return $content;
}
