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
    'tfnm_validate_text'
  );

  add_settings_section(
		'tfnm_section_admin',
		'Enter your Yelp API Key.',
		'tfnm_callback_section_admin',
		'tfnm_settings'
	);

  add_settings_field(
    'api_key',
    'API Key',
    'tfnm_text_field',
    'tfnm_settings',
    'tfnm_section_admin',
    array(
      'id' => 'api_key',
      'label' => 'Learn how to get an API Key in the <a href="" target="_blank">Yelp Fusion Documentation</a>.',
      'url' => 'https://www.yelp.com/developers/documentation/v3/authentication'
    )
  );

}
add_action( 'admin_init', 'tfnm_register_settings' );

?>
