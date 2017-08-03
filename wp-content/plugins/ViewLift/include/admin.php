<?php
// Add JQuery-UI Draggable to the included scripts, and other scripts needed for plugin
function av_admin_enqueue_scripts( $hook_suffix ) {

	// only enqueue on relevant admin pages
	$load_on_pages = array(
		'media-upload-popup',
		'post.php',
		'post-new.php',
	);
	if ( ! in_array( $hook_suffix, $load_on_pages, true ) ) {
		return;
	}

	$ajaxupload_url = plugins_url( '../static/js/upload.js', __FILE__ );
	$style_url = plugins_url( '../static/css/style.css', __FILE__ );
	$logic_url = plugins_url( '../static/js/logic.js', __FILE__ );

	//wp_register_style( 'av_wp_admin_css', $style_url, false, AV_PLUGIN_VERSION );
	wp_enqueue_style( 'av_wp_admin_css' );
	wp_enqueue_script( 'jquery-ui-draggable' );
	wp_enqueue_script( 'ajaxupload_script', $ajaxupload_url );
	wp_enqueue_script( 'logic_script', $logic_url );
}

