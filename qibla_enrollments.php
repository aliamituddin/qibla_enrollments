<?php
/*
Plugin Name: Qibla Enrollments
Description: Plugin will make Qibla Enrollments
Version: 1.0.0
Author: Muhammad Atiq
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit;
}
//error_reporting(0);
define( 'QE_PLUGIN_NAME', 'Qibla Enrollments' );
define( 'QE_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'QE_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'QE_SITE_BASE_URL',  rtrim(get_bloginfo('url'),"/")."/");

require_once QE_PLUGIN_PATH.'includes/qe_class.php';

register_activation_hook( __FILE__, array( 'QE', 'qe_install' ) );
register_deactivation_hook( __FILE__, array( 'QE', 'qe_uninstall' ) );