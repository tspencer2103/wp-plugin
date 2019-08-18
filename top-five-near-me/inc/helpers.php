<?php

// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
* This function is used to replace the empty href to include the url.
* It is also here to show my skills.
*/
function tfnm_parse_link( $text, $url ){
  $start = strpos( $text, 'href=""' );
  return substr_replace( $text, 'href="' . esc_url( $url ) . '"', $start, strlen('href=""') );
}

/**
* This function gets data from Yelp API.
* The shortcode and settings area send needed configuration data for the call.
*/
function tfnm_get_data( $args ){
	$default_args = array(
		'location' => array(
			'latitude' => 42.360081,
			'longitude' => -71.058884
		),
		'categories' => ''
	);

	$args = wp_parse_args( $args, $default_args );

	// Get API key from settings area of plugin
  $api_key = get_option( 'tfnm_options' )[ 'api_key' ];

	/**
	* First check:
	*  -  Making sure api_key is set for authentication into API
	*  -  Making sure location is set for query of businesses in area
	*  -  Making sure category parameter is set to allow for customization of query for type of business
	*/
	if( empty( $api_key ) || empty( $args['location'] ) || empty( $args['categories'] ) ){
		return 'There was a configuration error. Please contact the adminstrator.';
	}

	// We're clear, set up the call.
  $api_url = 'https://api.yelp.com/v3/businesses/search?latitude=' . $args['location']['latitude'] . '&longitude=' . $args['location']['longitude'] . '&limit=5&categories=' . $args['categories'];

  $header_args = array(
    'user-agent' => '',
    'headers' => array(
      'authorization' => 'Bearer ' . $api_key
    )
  );

	// Try to get the data
  $response = wp_safe_remote_get( $api_url, $header_args );

	// Error proofing
	if ( is_wp_error( $response ) ) {
		return 'There was an error in response. Please contact the administrator.';
	}

	// Doing some data clean up before sending toward the front.
	if( !empty( $response['body'] ) ){
		$response = json_decode( $response['body'] );
		if( !empty( $response->businesses ) ){
			return $response->businesses;
		}
	}

	// Catch all.
	return false;
}

function tfnm_star_rating( $rating ){
	$image_name = floor( $rating );
	$decimal = $rating - $image_name;

	if( $decimal >= .5 ){
		$image_name .= '_half';
	}

	return plugin_dir_url( dirname( __FILE__ ) ) . 'public/images/yelp_stars/web_and_ios/regular/regular_' . $image_name . '.png';
}

function tfnm_pretty_display_items( $data ){
	if( empty( $data ) ){
		return 'There was an error. Please contact the administrator.';
	}

	$top_five = array();

	foreach( $data as $datum ){

		// Only going to show items that have a name and url
		if( empty( $datum->name || $datum->url ) ){
			continue;
		}

		$html = '<div class="tfnm-item">';

		$html .= '<h3 class="name">';
		$html .= '<a href="' . esc_url( $datum->url ) . '" target="_blank">';
		$html .= esc_html( $datum->name ) . '</a></h3>';

		if( !empty( $datum->image_url ) ){
			$html .= '<a href="' . esc_url( $datum->url ) . '" target="_blank">';
			$html .= '<div class="business-image" style="background-image: url( ' . esc_url( $datum->image_url ) . ' );">';
			//Keeping image tag in source for accessibility
			$html .= '<img src="' . esc_url( $datum->image_url ) . '" width="200" height="200" alt="' . esc_attr( $datum->name ) . ' Image" />';
			$html .= '</div></a>';
		}

		if( !empty( $datum->rating ) ){
			$stars = tfnm_star_rating( $datum->rating );
			$html .= '<p class="rating">';
			$html .= '<a href="' . esc_url( $datum->url ) . '" target="_blank">';
			$html .= '<img src="' . esc_url( $stars ) . '" alt="Yelp ' . esc_attr( $datum->rating ) . ' Rating Stars Image" /> ';
			$html .= '</a></p>';
		}

		if( !empty( $datum->review_count ) ){
			$html .= '<p class="review-count">';
			$html .= '<a href="' . esc_url( $datum->url ) . '" target="_blank">Based on ' . esc_html( $datum->review_count ) . ' Reviews</a>';
			$html .= '</p>';
		}

		if( !empty( $datum->hours ) ){
			$html .= '<p class="hours">Hours: ' . esc_html( $datum->hours ) . '</p>';
		}

		if( !empty( $datum->distance ) ){
			$html .= '<p class="distance">You are <span>' . esc_html( round( $datum->distance ) ) . '</span>m away.</p>';
		}

		if( !empty( $datum->display_phone ) ){
			$html .= '<p class="phone">T: ' . esc_html( $datum->display_phone ) . '</p>';
		}

		$html .= '<a class="yelp-attribution" href="' . esc_url( $datum->url ) . '" target="_blank"><p>Read more on Yelp</p></a>';
		$html .= '</div>';

		array_push( $top_five, $html );
	}

	return $top_five;

}

 ?>
