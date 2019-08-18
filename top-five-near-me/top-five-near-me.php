<?php
/*
Plugin Name: Top Five Near Me
Description: Welcome to the plugin that shows you the top five places near me powered by Yelp.
Plugin URI: https://github.com/tspencer2103/wp-plugin
Author: Theresa Spencer
Version: 1.0
License: GPLv2 or Later
License URI: https://www.gnu.org/licenses/gpl-2.0.txt
*/

// disable direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tfnm_init(){
  add_filter( 'widget_text', 'shortcode_unautop' );
  add_filter( 'widget_text', 'do_shortcode' );

  wp_enqueue_style( 'tfnm-styles', plugin_dir_url( dirname( __FILE__ ) ) . 'top-five-near-me/public/css/tfnm-style.css', array(), '1', 'screen' );
}
add_action( 'init', 'tfnm_init' );

require_once plugin_dir_path( __FILE__ ) . 'inc/helpers.php';

if( is_admin() ){
  require_once plugin_dir_path( __FILE__ ) . 'admin/validations.php';
  require_once plugin_dir_path( __FILE__ ) . 'admin/callbacks.php';
  require_once plugin_dir_path( __FILE__ ) . 'admin/top-five-near-me-admin.php';
}

function tfnm_show_five_shortcode( $atts ){
  // Default shortcode atts.
  $default_atts = array(
    'location' => array(),
    'categories' => ''
  );

  // Set up configuration from shortcode
  $atts = wp_parse_args( $atts, $default_atts );

  // $atts parses the value as a string
  // In this case, a true value will mean find location automatically
  if( 'true' === $atts['location'] ){
    $atts['location'] = array(
			'latitude' => 42.360081,
			'longitude' => -71.058884
		);
  }

  // Try to get data
  $data = tfnm_get_data( $atts );

  if( empty( $data ) || is_string( $data ) ){
    // The value false will return as a catch all.
    if( empty( $data ) ){
      $data = 'There was an error. Please check with the administrator.';
    }
    echo esc_html( $data );
  }

	echo '<div id="tfnm">';
	foreach( tfnm_pretty_display_items( $data ) as $item ){
    echo wp_kses_post( $item );
  }
	echo '</div>';
	// Yelp Credit
	echo '<a class="yelp-credit" href="https://www.yelp.com" target="_blank"><span>Powered by Yelp</span>';
	echo '<img src="' . esc_url( plugin_dir_url( __FILE__ ) . 'public/images/YelpLogo_Trademark/Screen(R)/Yelp_trademark_RGB_outline.png' ) . '" alt="Yelp Logo" />';
	echo '</a>';
	
}
add_shortcode( 'show_top_five', 'tfnm_show_five_shortcode' );
