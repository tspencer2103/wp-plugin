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
* This function is used to find the exact image based on rating.
* This is a requirement for Yelp branding guidelines.
*/
function tfnm_star_rating( $rating ){
	$image_name = floor( $rating );
	$decimal = $rating - $image_name;

	if( $decimal >= .5 ){
		$image_name .= '_half';
	}

	return plugin_dir_url( dirname( __FILE__ ) ) . 'public/images/yelp_stars/web_and_ios/regular/regular_' . $image_name . '.png';
}

/**
* This function is used to make the numbers formatted in a pretty way.
* It is a function as the logic is used more than once.
*/
function tfnm_format_number( $number, $decimal_places ){

	// Checking it is in fact a number before formatting to be safe.
	if( is_int( $number ) || is_float( $number ) ){
		return number_format( $number, $decimal_places, '.', ',' );
	}

	// Just return what was inputted if it fails the checks above.
	return $number;
}

/**
* This function takes inputs and gets API data.
* It is used to consolidate code, provide consistent error checking and
*
*/
function tfnm_call_api( $api_key, $api_url ){

	$header_args = array();

	if( !empty( $api_key ) ){
		$header_args = array(
			'user-agent' => '',
			'headers' => array(
				'authorization' => 'Bearer ' . $api_key
			)
		);
	}

	$response = wp_safe_remote_get( $api_url, $header_args );

	// Error proofing
	if ( is_wp_error( $response ) ) {
		return 'There was an error in response. Please contact the administrator.';
	}

	// Doing some data clean up before returning.
	if( !empty( $response['body'] ) ){
		$response = json_decode( $response['body'] );
	}

	return $response;
}

/**
* This function will return geocoding and/or geolocation
*/
function tfnm_geocode_location( $location ){
	$api_key = get_option( 'tfnm_options' )[ 'g_api_key' ];
	$api_url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $location . '&key=' . $api_key;

	$response = tfnm_call_api( false, $api_url );

	// Add capability to have a MapQuest (Verizon) or HERE API key

	// This is latitude && This is longitude from Google Maps API
	if( !empty( $response->results[0]->geometry->location->lat ) && !empty( $response->results[0]->geometry->location->lng ) ){
		return array(
			'latitude' => $response->results[0]->geometry->location->lat,
			'longitude' => $response->results[0]->geometry->location->lng
		);
	} else {
		// Will return if there is not a valid city inputted through the shortcode
		// OR if the quota limit has been reached
		return 'There was an error finding the location. Please contact the administrator.';
	}

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

	$response = tfnm_call_api( $api_key, $api_url );

	if( !empty( $response->businesses ) ){
		return $response->businesses;
	} elseif( is_string( $response ) ){
		// This is here to print location error messaging.
		return $response;
	}

	// Catch all. Something didn't work.
	return false;
}

function tfnm_pretty_display_items( $data ){
	// Will get printed if there was an error in tfnm_get_data() and the catch all -- false -- was returned.
	if( empty( $data ) ){
		return 'There was an error. Please contact the administrator.';
	}

	// Declaration
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
			// Format the review count number to look nice with thousands separator
			$number = tfnm_format_number( $datum->review_count, 0 );
			$html .= '<p class="review-count">';
			$html .= '<a href="' . esc_url( $datum->url ) . '" target="_blank">Based on ' . esc_html( $number ) . ' Reviews</a>';
			$html .= '</p>';
		}

		if( !empty( $datum->hours ) ){
			$html .= '<p class="hours">Hours: ' . esc_html( $datum->hours ) . '</p>';
		}

		if( !empty( $datum->distance ) ){
			$number = tfnm_format_number( $datum->distance, 0 );
			$html .= '<p class="distance">You are <span>' . esc_html( $number ) . '</span>m away.</p>';
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
