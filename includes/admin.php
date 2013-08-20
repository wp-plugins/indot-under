<?php
/**
 * Admin functions
 **/
 
 add_action('admin_menu', 'indot_under_admin_menu_setup'); 

 
 /* Register menu item */
 function indot_under_admin_menu_setup()
 {
	$page_title = 'Indot Under';
	$menu_title = 'Indot Under';
	$capability = 'manage_options';
	$menu_slug = 'indot-under-settings';
	$function = 'indot_under_settings';
	add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function);
		
	$sub_menu_title = 'Under';
	add_submenu_page($menu_slug, $page_title, $sub_menu_title, $capability, $menu_slug, $function);
 }
 
 
 function indot_under_settings() 
 {
    if (!current_user_can('manage_options'))
	{
        wp_die('You do not have sufficient permissions to access this page.');
    }
	else
	{
		require(INDOT_UNDER_DIR.'includes/settings.php');
	}
}

add_action( 'admin_notices', 'indot_admin_notices' );
function indot_admin_notices() {
	// If a newer version is available, add the update

 	$screen_id = get_current_screen()->id;
 	if ($screen_id == 'toplevel_page_indot-under-settings' && checkUpdateVersion()) {
    	printf( '<div class="updated"> 
    		<p> A <strong>new version</strong> has been released! Click <strong><a href="'.downloadUpdateVersion().'">here</a></strong> to update!<br /> Feel free to support the development of this plugin! (hint: you can buy us a beer!) </p> </div>');
 	}
}
 
function checkUpdateVersion(){
	require_once (INDOT_UNDER_DIR.'includes/indot_autoupdate.php');
	$indot_under_current_version = INDOT_UNDER_VERSION;
	$indot_under_remote_path = 'http://localhost:81/update.php';
	$indot_under_slug = INDOT_UNDER_BASENAME;
	$obj = new indot_auto_update ($indot_under_current_version, $indot_under_remote_path, $indot_under_slug);

	if (version_compare($indot_under_current_version, $obj->getRemote_version(), '<')) {
        return true;
    }
	return false;
}

function downloadUpdateVersion(){
	require_once (INDOT_UNDER_DIR.'includes/indot_autoupdate.php');
	$indot_under_current_version = INDOT_UNDER_VERSION;
	$indot_under_remote_path = 'http://localhost:81/update.php';
	$indot_under_slug = INDOT_UNDER_BASENAME;
	$obj = new indot_auto_update ($indot_under_current_version, $indot_under_remote_path, $indot_under_slug);

	return $obj->getRemote_information()->download_link;
}

 ?>