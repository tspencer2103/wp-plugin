<?php

/**
* Callback function to create a text field
*/

function tfnm_text_field( $args ){
  // Get saved option, if it's there.
	$options = get_option( 'tfnm_options' );

  //Default values
  $default_args = array(
    'id' => 'default',
    'label' => '',
    'url' => ''
  );

  $args = wp_parse_args( $args, $default_args );

  //Easy update to URL above and showing of skills.
  if( !empty( $args[ 'url' ] ) && false !== strpos( $args[ 'label' ], 'href=""' ) ){
    $args[ 'label' ] = tfnm_parse_link( $args['label'], $args['url'] );
  }

	echo '<input id="tfnm_options_' . esc_attr( $args[ 'id' ] ) . '" name="tfnm_options[' . esc_attr( $args[ 'id' ] ) . ']" type="text" size="100" value=" ' . sanitize_text_field( $options[ $args[ 'id' ] ] ) . ' "><br />';
	echo '<label for="tfnm_options_' . esc_attr( $args[ 'id' ] ) . '">' . wp_kses_post( $args[ 'label' ] ) . '</label>';

}

 ?>