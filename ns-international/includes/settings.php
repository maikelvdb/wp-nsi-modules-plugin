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
	?>
		<div class="wrap">
			<h1><?php echo get_admin_page_title() ?></h1>
			<form method="post" action="options.php" class="inline-form">
				<?php
					settings_fields( Constants::PLUGIN_SLUG );
					do_settings_sections( Constants::PLUGIN_SLUG );
				?>
			</form>
			<?php
				nsInternationalStationsForm();
			?>
		</div>
	<?php
}

add_action( 'admin_init',  'nsInternationalSettingsFields' );
function nsInternationalSettingsFields(){
	addGeneralSettings();
}

function addGeneralSettings() {
	$section = 'ns_international_section_general';

	// data fields
	register_setting( Constants::PLUGIN_SLUG, Constants::TRACKING_CODE_KEY, 'string' );
	register_setting( Constants::PLUGIN_SLUG, Constants::DEFAULT_FROM_STATION, 'string' );

	// form
	add_settings_section(
		$section, // section ID
		'', // title (optional)
		'', // callback function to display the section (optional)
		Constants::PLUGIN_SLUG
	);
	
	add_settings_field(
		Constants::TRACKING_CODE_KEY,
		'Tracking code', // label
		'inputField',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::TRACKING_CODE_KEY,
			'class' => Constants::TRACKING_CODE_KEY, // for <tr> element
			'name' => Constants::TRACKING_CODE_KEY, // pass any custom parameters
			'saver' => false
		)
	);
	
	add_settings_field(
		Constants::DEFAULT_FROM_STATION,
		'Default vertrek station code', // label
		'inputField',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::DEFAULT_FROM_STATION,
			'class' => Constants::DEFAULT_FROM_STATION, // for <tr> element
			'name' => Constants::DEFAULT_FROM_STATION, // pass any custom parameters
			'saver' => true
		)
	);
}

function inputField( $args ){
	$default_value = isset($args['value']) ? $args['value'] : '';
	$saveBtn = $args['saver'] ? '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />' : '';
	printf(
		'<div class="input-saver"><input type="text" id="%s" name="%s" value="%s" class="ns-input" />
		%s</div>',
		$args[ 'name' ],
		$args[ 'name' ],
		get_option( $args[ 'name' ], $default_value),
		$saveBtn
	);
}


function nsInternationalStationsForm() {
	?>
		<div class="stations__search">
			<h3>
				Shortcode maken
			</h3>
			<div class="preview_settings">
				<div class="filter_input">
					<input type="search" placeholder="Search" class="search" value="Amsterdam" />
				</div>

				<select class="module_select">
					<option value="ns-international-search" selected>Zoekformulier</option>
					<option value="ns-international-calendar">Kalender</option>
					<option value="ns-international-dayschedule">Dagprijzen</option>
				</select>

				<div class="stations">
					<div class="from js-from-station"></div>
					<div class="to js-to-station"></div>
				</div>

				<div class="shortcode_preview">[]</div>
				
				<div class="ns-copy-confirm" style="display: none;">Gekopieerd</div>
			</div>

			<div class="stations_info">
				Klik op een station om deze toe te voegen aan de shortcode. (klik rechtermuis voor het '<b>Bestemming</b>' station)
			</div>
			<ul class="stations_list"></ul>
		</div>

	<?php
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
				<p>
					<strong>NS International settings saved.</strong>
				</p>
			</div>
		<?php
	}

}

function ns_international_error($args) {
	printf(
		'<span class="ns-error">%s</span>',
		$args['text']
	);
}

function ns_international_info_span($args) {
	printf(
		'<span>%s</span>',
		'Voer een NS API key in om stations te kunnen inladen.'
	);
}