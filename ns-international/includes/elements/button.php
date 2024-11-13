<?php

function render_button($label, $type = "submit", $className = "", $attributes = []) {
    $Content = "<button type=\"" . $type . "\" ";

    foreach($attributes as $key => $val) {
        $Content .= " " . $key . "=\"" . $val . "\"";
    }

    $Content .= " class=\"" . $className . " button\">" . $label . "</button>";

    return $Content;
}