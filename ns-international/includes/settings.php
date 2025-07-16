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
		Constants::MAX_WIDTH,
		'Maximale breedte modules', // label
		'inputField',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::MAX_WIDTH,
			'class' => Constants::MAX_WIDTH, // for <tr> element
			'name' => Constants::MAX_WIDTH, // pass any custom parameters
			'saver' => false,
			'type' => 'number',
			'value' => '900'
		)
	);
	
	add_settings_field(
		Constants::SPACING_BOTTOM,
		'Margin onderkant modules', // label
		'inputField',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::SPACING_BOTTOM,
			'class' => Constants::SPACING_BOTTOM, // for <tr> element
			'name' => Constants::SPACING_BOTTOM, // pass any custom parameters
			'saver' => false,
			'type' => 'number',
			'value' => '15'
		)
	);
	
	add_settings_field(
		Constants::WP_PLUGIN_MANAGER_API_KEY,
		'Updater api key', // label
		'inputField',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::WP_PLUGIN_MANAGER_API_KEY,
			'class' => Constants::WP_PLUGIN_MANAGER_API_KEY, // for <tr> element
			'name' => Constants::WP_PLUGIN_MANAGER_API_KEY, // pass any custom parameters
			'saver' => false,
			'type' => 'string',
			'value' => ''
		)
	);
	
	add_settings_field(
		Constants::WP_PLUGIN_MANAGER_USE_DEV,
		'Updater use previews', // label
		'updater_input',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::WP_PLUGIN_MANAGER_USE_DEV,
			'class' => Constants::WP_PLUGIN_MANAGER_USE_DEV, // for <tr> element
			'name' => Constants::WP_PLUGIN_MANAGER_USE_DEV, // pass any custom parameters
			'saver' => false,
			'type' => 'bool',
			'value' => 'true'
		)
	);
	
	add_settings_field(
		Constants::SKIP_DAYS,
		'Aantal dagen vooruit modules', // label
		'inputField',
		Constants::PLUGIN_SLUG,
		$section,
		array(
			'label_for' => Constants::SKIP_DAYS,
			'class' => Constants::SKIP_DAYS, // for <tr> element
			'name' => Constants::SKIP_DAYS, // pass any custom parameters
			'saver' => false,
			'type' => 'number',
			'value' => '70'
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

	$wp_updater_value = get_option(Constants::WP_PLUGIN_MANAGER_USE_DEV, "0");
	add_settings_field(
        Constants::WP_PLUGIN_MANAGER_USE_DEV,
		'Updater use previews', // label
		'updaterInput',
		Constants::PLUGIN_SLUG,
		$section,
        array( 
            'type'         => 'checkbox',
            'name'         => Constants::WP_PLUGIN_MANAGER_USE_DEV,
            'label_for'    => 'wp_updater',
            'value'        => 1,
            'checked'      => $wp_updater_value == '1' ? 1 : 0,
			'saver' => false
		)
    ); 
}

function updaterInput($args) { 
    $checked = '';
    if($args['checked'] == 1) { 
		$checked = ' checked="checked" ';
	 }
	
	$html  = '';
	$html .= '<input id="' . esc_attr( $args['name'] ) . '" 
	name="' . esc_attr($args['name']) .'" 
	type="checkbox" ' . $checked . ' value="' . $args['value'] . '" />';
	if (isset($args['description']) && !empty($args['description'])) {
		$html .= '<span class="wndspan">' . esc_html( $args['description'] ) .'</span>';
	}
	
	if (isset($args['tip']) && !empty($args['tip'])) {
		$html .= '<b class="wntip" data-title="'. esc_attr( $args['tip'] ) .'"> ? </b>';
	}

	$saveBtn = $args['saver'] ? '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />' : '';
	echo "<div class=\"input-saver\">" . $html . $saveBtn . "</div>";
}

function inputField( $args ){
	$default_value = isset($args['value']) ? $args['value'] : '';
	$type = isset($args['type']) ? $args['type'] : 'text';

	$saveBtn = $args['saver'] ? '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes" />' : '';
	printf(
		'<div class="input-saver"><input type="%s" id="%s" name="%s" value="%s" class="ns-input" />
		%s</div>',
		$type,
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
					<option value="nsi-price">Prijs (Optioneel: date="<?= date('Y-m-d'); ?>")</option>
					<option value="nsi-co2">CO2</option>
					<option value="ldjson">LD json (google data)</option>
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
				<strong>NS International settings saved.</strong>
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