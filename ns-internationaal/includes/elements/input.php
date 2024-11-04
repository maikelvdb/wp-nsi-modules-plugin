<?php

function render_input($label, $type, $name, $value, $className = "", $attributes = []) {
    $Content = "<div class=\"input " . $className . "\">";
        $Content .= "<label for=\"" . $name . "\">" . $label . "</label>";
        $Content .= "<input type=\"" . $type . "\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"";
        foreach($attributes as $key => $val) {
            $Content .= " " . $key . "=\"" . $val . "\"";
        }
        $Content .= " />";
    $Content .= "</div>";

    return $Content;
}