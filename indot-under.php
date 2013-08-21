<?php
/**
Plugin Name: Indot Under
Description: Under by indot is a simple plugin to make your scheduled launch or scheduled maintenance task easier!
Version: 1.0.0
Author: Indot
Author URI: http://indot.pt
Plugin URI: http://indot-under.indot.pt
License: GPLv2 or later
**/

/**  
	Copyright 2013  Indot  (email : info@indot.pt)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
**/

/**
 * Define some useful constants
 **/
define('INDOT_UNDER_VERSION', '1.0.0');
define('INDOT_UNDER_DIR', plugin_dir_path(__FILE__));
define('INDOT_UNDER_URL', plugin_dir_url(__FILE__));
define('INDOT_UNDER_BASENAME', plugin_basename(__FILE__));
/**
 * Load files
 * 
 **/
function indot_under_load(){
	date_default_timezone_set(get_option('timezone_string'));
	if(!get_option('IndotUnderActive'))
		add_option('IndotUnderActive',false);
	if(!get_option('IndotUnderSettings')){
		require(INDOT_UNDER_DIR.'includes/default_settings.php');
		add_option('IndotUnderSettings',$defaultSettings);
	}
    if(is_admin()) 
	{
        require_once(INDOT_UNDER_DIR.'includes/admin.php');
	}
}
indot_under_load();

/**
 * Activation, Deactivation and Uninstall Functions
 * 
 **/
register_activation_hook(__FILE__, 'indot_under_activation');
register_deactivation_hook(__FILE__, 'indot_under_deactivation');

function indot_under_activation() {
	if(!get_option('IndotUnderActive'))
		add_option('IndotUnderActive',false);
	if(!get_option('IndotUnderSettings')){
		require(INDOT_UNDER_DIR.'includes/default_settings.php');
		add_option('IndotUnderSettings',$defaultSettings);
	}
}

function indot_under_deactivation() {    
	/* actions to perform once on plugin deactivation go here	  */
}

function indot_under_set() {
	if(get_option('IndotUnderActive')){
		$option = get_option('IndotUnderSettings');
		$showUnder = true;

		if($option['whitelist']['enable']){
			$externalContent = file_get_contents('http://checkip.dyndns.com/');
			preg_match('/Current IP Address: ([\[\]:.[0-9a-fA-F]+)</', $externalContent, $m);

			global $current_user;
			$user_roles = $current_user->roles;
			$result = array_intersect(array_map('strtolower', $user_roles), array_map('strtolower', $option['whitelist']['rolelist']));

			if(in_array($m[1], $option['whitelist']['iplist'])){
				$showUnder = false;
			}
			else if(!empty($result)){
				$showUnder = false;
			}
		}

		if($showUnder){
			if($option['statuscode']['enable']){
				if($option['statuscode']['code'] == 200){
					http_response_code(200);
					require_once(INDOT_UNDER_DIR.'includes/under/index.php');
					die();
				}
				else if($option['statuscode']['code'] == 301){
					header("Location: ".$option['statuscode']['redirect'], TRUE, 301);
					die();
				}
				else if($option['statuscode']['code'] == 503){
					http_response_code(503);
					require_once(INDOT_UNDER_DIR.'includes/under/index.php');
					die();
				}
				else {
					require_once(INDOT_UNDER_DIR.'includes/under/index.php');
					die();
				}
			}
			else{
				require_once(INDOT_UNDER_DIR.'includes/under/index.php');
				die();
			}
		}
	}
}
add_action('template_redirect', 'indot_under_set');

function indot_under_get_wp_version() {
   global $wp_version;
   return $wp_version;
}

function indot_under_register_scripts($hook) {
    wp_enqueue_style( 'indot-admin-css', INDOT_UNDER_URL . 'assets/css/admin.css' );
	if($hook == 'toplevel_page_indot-under-settings') {
	    wp_enqueue_style( 'indot-jquery-ui-css', INDOT_UNDER_URL . 'assets/css/jquery-ui.min.css');
	    wp_enqueue_script( 'jquery');
	    wp_enqueue_script( 'jquery-ui-core');
	    wp_enqueue_script( 'jquery-ui-tabs');
	    wp_enqueue_script( 'jquery-ui-accordion');
	    wp_enqueue_script( 'jquery-ui-datepicker');
	    wp_enqueue_script( 'indot-time-picker', INDOT_UNDER_URL . 'assets/js/jquery-ui-timepicker-addon.js', array('jquery','jquery-ui-core','jquery-ui-datepicker'));
	    wp_enqueue_script( 'indot-script', INDOT_UNDER_URL . 'assets/js/script.js', array('jquery','jquery-ui-core','jquery-ui-tabs','jquery-ui-accordion','indot-time-picker'));
	    if(function_exists('wp_enqueue_media') && version_compare(indot_under_get_wp_version(), '3.5', '>=')) {
        	wp_enqueue_media();
      	}
      	else {
	    	wp_enqueue_script('media-upload');
	    	wp_enqueue_style('thickbox');
	 		wp_enqueue_script('thickbox');
		}
	}
}
add_action( 'admin_enqueue_scripts', 'indot_under_register_scripts' );

function indot_under_timer_hook(){
	update_option('IndotUnderActive',false);
}
add_action('indot_under_timer_action','indot_under_timer_hook');

function indot_under_add_new_image_size() {
    add_image_size( 'indot_under_img', 350, 250, false );
}
add_action( 'init', 'indot_under_add_new_image_size' );

function indot_under_settings_plugin_link( $links, $file) 
{
	if($file == plugin_basename(INDOT_UNDER_DIR.'/indot-under.php')){
		$in = '<a href="admin.php?page=indot-under-settings">Settings</a>';
        array_unshift($links, $in);
	}
    return $links;
}
add_filter( 'plugin_action_links', 'indot_under_settings_plugin_link', 10, 2 );


function indot_remove_styles($queuedStyles) {
	global $wp_styles;
	foreach($wp_styles->queue as $style)
	{
		if(!in_array($style, $queuedStyles)){
			wp_dequeue_style( $style );
		}
	}
}
add_action('wp_head', 'indot_remove_styles', 1, 1);

?>