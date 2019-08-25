<?php

// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tfnm_settings_page(){
  // Double check permissions
  if ( ! current_user_can( 'manage_options' ) ){
    return;
  }

  ?>
  <div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <form action="options.php" method="post">
      <?php
        settings_fields( 'tfnm_options' );

        do_settings_sections( 'tfnm_settings' );

        submit_button();
      ?>
    </form>
  </div>
<?php }

function tfnm_add_settings_area(){
  add_menu_page(
    'Top Five Near Me Settings',
    'Top Five Near Me Settings',
    'manage_options',
    'tfnm_settings',
    'tfnm_settings_page',
    '',
    null
  );
}
add_action( 'admin_menu', 'tfnm_add_settings_area' );

function tfnm_register_settings(){

  /* Register settings fields */
  register_setting(
    'tfnm_options',
    'tfnm_options',
    'tfnm_validate'
  );

  add_settings_section(
		'tfnm_section_admin',
		'Set up the plugin.',
		'tfnm_callback_section_admin',
		'tfnm_settings'
	);

  add_settings_field(
    'api_key',
    'Yelp API Key',
    'tfnm_text_field',
    'tfnm_settings',
    'tfnm_section_admin',
    array(
      'id' => 'api_key',
      'label' => 'Learn how to get a Yelp API Key in the <a href="" target="_blank">Yelp Fusion Documentation</a>.',
      'url' => 'https://www.yelp.com/developers/documentation/v3/authentication'
    )
  );

	add_settings_field(
		'auto_locate',
		'Auto Locate?',
		'tfnm_checkbox_field',
		'tfnm_settings',
		'tfnm_section_admin',
		array(
			'id' => 'auto_locate',
			'label' => 'Checking this item off will require a Google Maps API Key and will allow the plugin to geolocate and geocode.'
		)
	);

	add_settings_field(
		'g_api_key',
		'Google Map API Key',
		'tfnm_text_field',
		'tfnm_settings',
		'tfnm_section_admin',
		array(
			'id' => 'g_api_key',
			'label' => 'Learn how to get a Google Maps API Key in the <a href="" target="_blank">Google Cloud Documentation</a>.',
			'url' => 'https://developers.google.com/maps/gmp-get-started#get-api-key'
		)
	);

}
add_action( 'admin_init', 'tfnm_register_settings' );

?>
