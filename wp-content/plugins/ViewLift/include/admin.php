<?php

// included styles, and other styles needed for plugin
function VIEWLIFT_stylesheet() 
{
    wp_enqueue_style( 'style', plugins_url( '../static/css/style.css', __FILE__ ) );
    wp_enqueue_style( 'bootstrap_min', plugins_url( '../static/css/bootstrap_min.css', __FILE__ ) );
    wp_enqueue_style( 'bootstrap_theme_min', plugins_url( '../static/css/bootstrap_theme_min.css', __FILE__ ) );
      
}


// included scripts, and other scripts needed for plugin
function VIEWLIFT_admin_enqueue_scripts() {	
        
        $upload = plugins_url( '../static/js/upload.js', __FILE__ );        
        $jquery_min_js = plugins_url( '../static/js/jquery.min.js', __FILE__ );
        $jquery_min2_js = plugins_url( '../static/js/jquery-2.2.4.min.js', __FILE__ );
        $bootstrap_min_js = plugins_url( '../static/js/bootstrap.min.js', __FILE__ );
        
	wp_enqueue_script( 'upload', $upload );
	wp_enqueue_script( 'jquery_min_js', $jquery_min_js );
        wp_enqueue_script( 'jquery_min2_js', $jquery_min2_js );
        wp_enqueue_script( 'bootstrap_min_js', $bootstrap_min_js );
}


