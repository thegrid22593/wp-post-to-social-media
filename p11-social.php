<?php
/**
* Plugin Name: P11 Social
* Plugin URI: http://gsandersongraphics.com
* Description: This plugin posts your blog posts to Facebook and Twitter
* Version: 0.1.0
* Author: P11 Creative - Garrett Sanderson
* Author URI: http://www.p11.com
* License: GPL2
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'P11_SOCIAL_VERSION', '0.1' );
define( 'P11_SOCIAL_MINIMUM_WP_VERSION', '3.7' );
define( 'P11_SOCIAL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'P11_SOCIAL_PLUGIN_URL', plugin_dir_url('') . 'p11-social' );
define( 'P11_SOCIAL_DELETE_LIMIT', 100000 );


require_once( P11_SOCIAL_PLUGIN_DIR . 'class.p11-social.php' );


add_action( 'init', array( 'P11_SOCIAL', 'init' ) );
add_action( 'init', array( 'P11_SOCIAL_CONFIG', 'init' ) );
