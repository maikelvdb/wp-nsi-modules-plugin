<?php

function render_input($label, $type, $name, $value, $className = "", $attributes = []) {
    $Content = "<label for=\"" . $name . "\" class=\"input " . $className . "\">";
        // $Content .= "<span  class=\"label\">" . $label . "</span>";
        $Content .= "<input type=\"" . $type . "\" placeholder=\"" . $label . "\" id=\"" . $name . "\" name=\"" . $name . "\" value=\"" . $value . "\"";
        foreach($attributes as $key => $val) {
            $Content .= " " . $key . "=\"" . $val . "\"";
        }
        $Content .= " />";
    $Content .= "</label>";

    return $Content;
}