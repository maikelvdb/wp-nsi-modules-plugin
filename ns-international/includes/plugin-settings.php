<?php
if( !function_exists('get_plugin_data') ){
    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}

function setPluginSettings() {
    $plugin = plugin_basename( __FILE__ );
    
    add_filter( "plugin_action_links_$plugin", 'nsi_settings_link' );
}


function nsi_settings_link( $links ) {
	// Build and escape the URL.
	$url = esc_url( add_query_arg(
		'page',
		Constants::PLUGIN_SLUG,
		get_admin_url() . 'admin.php'
	) );
	// Create the link.
	$settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
	// Adds the link to the end of the array.
	array_push(
		$links,
		$settings_link
	);
	return $links;
}