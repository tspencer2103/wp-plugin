<?php

/**
* Validating text fields
*/
function tfnm_validate_text( $input ){

  if( isset( $input['api_key'] ) ){
    $input['api_key'] = sanitize_text_field( $input['api_key'] );
  }

  return $input;
  
}

 ?>
