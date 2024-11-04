<?php

function load_jquery() {
    if ( ! wp_script_is( 'jquery', 'enqueued' )) {
        wp_enqueue_script( 'jquery' );
    }
}

