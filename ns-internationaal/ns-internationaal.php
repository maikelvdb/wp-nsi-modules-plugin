<?php
/**
* Plugin Name: NS International Plugin
* Plugin URI: https://www.maikelvdb.nl/
* Description: Deze plugin maakt meerdere NS International modules beschikbaar...
* Version: 0.0.1-APLHA.001
* Author: Maikel van den Bosch
* Author URI: https://www.maikelvdb.nl/
**/


include_once 'includes/settings.php';
include_once 'includes/search-form.php';
include_once 'includes/calendar.php';
include_once 'includes/day-schedule.php';


$tracking_code = get_option(Constants::TRACKING_CODE_KEY, '');
$plugin_url = str_replace("/includes", "", plugin_dir_url( __FILE__ ));
$wp_scripts = wp_scripts();

wp_enqueue_script( 'jquery' );
wp_register_script( 'google-jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.14.0/jquery-ui.min.js', array( 'jquery' ) );
wp_enqueue_style( 'jquery-style' );
wp_enqueue_script( 'google-jquery-ui' );

wp_enqueue_style(
'plugin_name-admin-ui-css',
'https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.14.0/themes/base/jquery-ui.min.css',
false,
"1.0",
false
);

wp_enqueue_style('modules-style',  $plugin_url . "/styles/modules.css");

wp_enqueue_script('shared-modules-js',  $plugin_url . "/scripts/shared.js", array('jquery', 'jquery-ui-datepicker'), false);
wp_enqueue_script('modules-js',  $plugin_url . "/scripts/modules.js", array('jquery', 'jquery-ui-datepicker'), false);

wp_enqueue_style('settings-style',  $plugin_url . "/styles/settings.css");
wp_enqueue_script('settings-js',  $plugin_url . "/scripts/settings.js", array('jquery'), false);


$jsarray = array(
    'tracking_code'               => $tracking_code
);
wp_localize_script( 'modules-js', 'php_vars', $jsarray);
wp_localize_script( 'settings-js', 'php_vars', $jsarray);



$info_url = 'https://raw.githubusercontent.com/maikelvdb/wp-nsi-modules-plugin/refs/heads/main/info.json';

add_filter( 'plugins_api', 'nsi_plugin_info', 20, 3);
function nsi_plugin_info( $res, $action, $args ){

	// do nothing if this is not about getting plugin information
	if( 'plugin_information' !== $action ) {
		return $res;
	}

	// do nothing if it is not our plugin
	if( plugin_basename( __DIR__ ) !== $args->slug ) {
		return $res;
	}

	// info.json is the file with the actual plugin information on your server
	$remote = wp_remote_get( 
		$info_url,
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			) 
		)
	);

	// do nothing if we don't get the correct response from the server
	if (is_wp_error( $remote )
		|| 200 !== wp_remote_retrieve_response_code( $remote )
		|| empty( wp_remote_retrieve_body( $remote ))
	) {
		return $res;
	}

	$remote = json_decode( wp_remote_retrieve_body( $remote ) );
	
	$res = new stdClass();
	$res->name = $remote->name;
	$res->slug = $remote->slug;
	$res->author = $remote->author;
	$res->author_profile = $remote->author_profile;
	$res->version = $remote->version;
	$res->tested = $remote->tested;
	$res->requires = $remote->requires;
	$res->requires_php = $remote->requires_php;
	$res->download_link = $remote->download_url;
	$res->trunk = $remote->download_url;
	$res->last_updated = $remote->last_updated;
	$res->sections = array(
		'description' => $remote->sections->description,
		'installation' => $remote->sections->installation,
		'changelog' => $remote->sections->changelog
		// you can add your custom sections (tabs) here
	);
	// in case you want the screenshots tab, use the following HTML format for its content:
	// <ol><li><a href="IMG_URL" target="_blank"><img src="IMG_URL" alt="CAPTION" /></a><p>CAPTION</p></li></ol>
	if( ! empty( $remote->sections->screenshots ) ) {
		$res->sections[ 'screenshots' ] = $remote->sections->screenshots;
	}
	
	if( ! empty( $remote->banners ) ) {
		$res->banners = array(
			'low' => $remote->banners->low,
			'high' => $remote->banners->high
		);
	}
	
	return $res;

}

add_filter( 'site_transient_update_plugins', 'nsi_push_update' );
 function nsi_push_update( $transient ){
 
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$remote = wp_remote_get( 
		$info_url,
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			)
		)
	);

	if(
		is_wp_error( $remote )
		|| 200 !== wp_remote_retrieve_response_code( $remote )
		|| empty( wp_remote_retrieve_body( $remote ) )
	) {
		return $transient;
	}
	
	$remote = json_decode( wp_remote_retrieve_body( $remote ) );
 
		// your installed plugin version should be on the line below! You can obtain it dynamically of course 
	if(
		$remote
		&& version_compare( $this->version, $remote->version, '<' )
		&& version_compare( $remote->requires, get_bloginfo( 'version' ), '<' )
		&& version_compare( $remote->requires_php, PHP_VERSION, '<' )
	) {
		
		$res = new stdClass();
		$res->slug = $remote->slug;
		$res->plugin = plugin_basename( __FILE__ ); // it could be just YOUR_PLUGIN_SLUG.php if your plugin doesn't have its own directory
		$res->new_version = $remote->version;
		$res->tested = $remote->tested;
		$res->package = $remote->download_url;
		$transient->response[ $res->plugin ] = $res;
		
		//$transient->checked[$res->plugin] = $remote->version;
	}
 
	return $transient;

}