<?php

/**
* Validating text fields
*/
function tfnm_validate( $input ){

  if( isset( $input['api_key'] ) ){
    $input['api_key'] = sanitize_text_field( $input['api_key'] );
  }

  if( isset( $input['g_api_key'] ) ){
    $input['g_api_key'] = sanitize_text_field( $input['g_api_key'] );
  }

  // Just being extra cautious
  if( isset( $input['auto_locate'] ) ){
    if( '1' === $input['auto_locate']  ){
      $input['auto_locate'] = '1';
    } else {
      $input['auto_locate'] = false;
    }

  }

  return $input;

}

 ?>
