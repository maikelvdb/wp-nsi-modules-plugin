<?php

include_once 'constants.php';

add_action('admin_menu', 'addAdminMenuItems', 9);

function addAdminMenuItems() {
	add_menu_page(
		'NS International Settings', // Page title
		'NS International', // Menu title
		'manage_options', // user rights
		Constants::PLUGIN_SLUG, // url slug
		'nsInternationalSettingsPageCallback', // callback for rendering the page
		'dashicons-tickets-alt' // Icon
	);
}

function nsInternationalSettingsPageCallback() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

    global  $nsiTemplateParser;

	ob_start();
	settings_fields(Constants::PLUGIN_SLUG);
	$updater_nonce = ob_get_clean();

	// Capture do_settings_sections output
	ob_start();
	do_settings_sections(Constants::PLUGIN_SLUG);
	$updater_sections = ob_get_clean();

	processPosts();

	echo $nsiTemplateParser->render('settings', [
			'TRACKING_CODE_NAME' => Constants::TRACKING_CODE_KEY,
			'TRACKING_CODE' => get_option(Constants::TRACKING_CODE_KEY, ''),

			'MAX_WIDTH_NAME' => Constants::MAX_WIDTH,
			'MAX_WIDTH' => get_option(Constants::MAX_WIDTH, '900'),

			'MARGIN_BOTTOM_NAME' => Constants::SPACING_BOTTOM,
			'MARGIN_BOTTOM' => get_option(Constants::SPACING_BOTTOM, '15'),

			'DAYS_AHEAD_NAME' => Constants::SKIP_DAYS,
			'DAYS_AHEAD' => get_option(Constants::SKIP_DAYS, '70'),

			'DEFAULT_STATION_NAME' => Constants::DEFAULT_FROM_STATION,
			'DEFAULT_STATION' => get_option(Constants::DEFAULT_FROM_STATION, ''),

			'UPDATE_KEY_NAME' => Constants::WP_PLUGIN_MANAGER_API_KEY,
			'UPDATE_KEY' => get_option(Constants::WP_PLUGIN_MANAGER_API_KEY, ''),

			'UPDATE_PREVIEWS_NAME' => Constants::WP_PLUGIN_MANAGER_USE_DEV,
			'UPDATE_PREVIEWS' => get_option(Constants::WP_PLUGIN_MANAGER_USE_DEV, '0'),

			'UPDATER_NONCE' => $updater_nonce,
			'UPDATER_SECTIONS' => $updater_sections,

			'MAPPINGS' => StationsReplacer::getAll(),
			'TEXTS' => TextValues::getAll(),
		]);
}

add_action( 'admin_init',  'nsInternationalSettingsFields' );
function nsInternationalSettingsFields(){
	addGeneralSettings();
}

function processPosts() {
	if ( !empty( $_POST ) ) {
		if (isset($_POST['stations'])) {
			processStationMappings($_POST['stations']);
		} else if (isset($_POST['text'])) {
			processTexts($_POST['text']);
		}
	}
}

function processStationMappings($stations) {
	if ( !is_array($stations) ) {
		return;
	}

	$stationsMap = [];
	foreach ($stations as $st) {
		if (!empty($st['key'])) {
			$stationsMap[$st['key']] = $st['value'];
		}
	}

	foreach ($stationsMap as $key => $value) {
		StationsReplacer::upsert($key, $value);
	}

	$allStations = StationsReplacer::getAll();
	foreach ($allStations as $station) {
		if (!isset($stationsMap[$station->key])) {
			StationsReplacer::remove($station->key);
		}
	}
}

function processTexts($texts) {
	if ( !is_array($texts) ) {
		return;
	}

	$textsMap = [];
	foreach ($texts as $txt) {
		if (!empty($txt['key'])) {
			$textsMap[$txt['key']] = $txt['value'];
		}
	}

	foreach ($textsMap as $key => $value) {
		TextValues::upsert($key, $value);
	}

	$allTexts = TextValues::getAll();
	foreach ($allTexts as $txt) {
		if (!isset($textsMap[$txt->key])) {
			TextValues::remove($txt->key);
		}
	}
}

function addGeneralSettings() {
	$section = 'ns_international_section_general';

	// data fields
	register_setting( Constants::PLUGIN_SLUG, Constants::TRACKING_CODE_KEY, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::DEFAULT_FROM_STATION, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::MAX_WIDTH, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::SKIP_DAYS, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::SPACING_BOTTOM, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::WP_PLUGIN_MANAGER_API_KEY, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::WP_PLUGIN_MANAGER_USE_DEV, 'number' );

	// form
	add_settings_section(
		$section, // section ID
		'', // title (optional)
		'', // callback function to display the section (optional)
		Constants::PLUGIN_SLUG
	);
}

add_action( 'admin_notices', 'nsInternationalNotice' );
function nsInternationalNotice() {

	if(
		isset( $_GET[ 'page' ] ) 
		&& Constants::PLUGIN_SLUG == $_GET[ 'page' ]
		&& isset( $_GET[ 'settings-updated' ] )
		&& $_GET[ 'settings-updated' ] === true
	) {
		?>
			<div class="notice notice-success is-dismissible">
				<strong>NS International settings saved.</strong>
			</div>
		<?php
	}

}
