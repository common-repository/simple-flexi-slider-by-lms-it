<?php

/*
  Plugin Name: Simple Flexi Slider By Lms-IT
  Description: Simple setup responsive slider using FlexSlider by WooThemes.
  Version: 1.0
  Author: LMS-IT
  License: GPLv2 or later
 */

/*
Copyright 2014  LMS-IT  (email : lms-pgm@hotmail.fr )

 This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

//Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* sfs abbreviation stands for Simple Flexi Slider */

//plugin activation function
function lmsit_sfs_activation() {

}

register_activation_hook(__FILE__, 'lmsit_sfs_activation');

//plugin desctivation function
function lmsit_sfs_deactivation() {

}

register_deactivation_hook(__FILE__, 'lmsit_sfs_deactivation');


//Load plugin javascript
add_action('wp_enqueue_scripts', 'lmsit_sfs_scripts');

function lmsit_sfs_scripts() {
	global $post;

	wp_enqueue_script( 'jquery' );
	
	wp_register_script( 'lmsit_sfs_js_flexslider', plugins_url( 'js/jquery.flexslider-min.js', __FILE__ ), array( 'jquery' ), '2.0', false );
	wp_enqueue_script( 'lmsit_sfs_js_flexslider' );


	wp_register_script('lmsit_sfs_js_init', plugins_url('js/slider_initialize.js', __FILE__));
	wp_enqueue_script('lmsit_sfs_js_init');
	
		$effect      	= (get_option('sfs_effect') == '') ? "slide" : get_option('sfs_effect');
		$slideshowSpeed = (get_option('sfs_slideshowSpeed') == '') ? 2000 : get_option('sfs_slideshowSpeed');
		$animationSpeed	= (get_option('sfs_animationSpeed') == '') ? 3000 : get_option('sfs_animationSpeed');
		
		$config_array = array(
				'effect' => $effect,
				'slideshowSpeed' => $slideshowSpeed,
				'animationSpeed' => $animationSpeed
		);
		
		wp_localize_script('lmsit_sfs_js_init', 'setting', $config_array);
}

//Load Plugin CSS
add_action('wp_enqueue_scripts', 'lmsit_sfs_styles');

function lmsit_sfs_styles() {
	wp_register_style( 'lmsit_sfs_css', plugins_url( 'css/slider.css', __FILE__) );
	wp_enqueue_style('lmsit_sfs_css');
}

//Add plugin ShortCode
add_shortcode("lmsit_sfs", "lmsit_display_sfs_slider");

function lmsit_display_sfs_slider($attr, $content) {

	extract(shortcode_atts(array(
			'id' => ''
	), $attr));
	
	$gallery_images = get_post_meta($id, "_sfs_gallery_images", true);
	$gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();
	
	$plugins_url = plugins_url();
	
	$html = '<div class="flexslider">
			  <ul class="slides">';
	
	foreach ($gallery_images as $gal_img) {
		if ($gal_img != "") {
			$html .= "<li><img src='" . $gal_img . "' /></li>";
		}
	}
	
	$html .=' </ul>
		</div>';
	

	return $html;
}


//Add slider menu
add_action('init', 'lmsit_sfs_register_slider');

function lmsit_sfs_register_slider() {
	$labels = array(
			'menu_name' => _x('Flexi Sliders', 'sfs_js_slider'),
	);

	$args = array(
			'labels' => $labels,
			'hierarchical' => true,
			'description' => 'Slideshows',
			'supports' => array('title', 'editor'),
			'public' => true,
			'show_ui' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => true,
			'capability_type' => 'post'
	);

	register_post_type('sfs_js_slider', $args);
}


/* Define shortcode column*/
add_filter('manage_edit-sfs_js_slider_columns', 'sfs_set_custom_edit_slider_columns');
add_action('manage_sfs_js_slider_posts_custom_column', 'sfs_custom_slider_column', 10, 2);

function sfs_set_custom_edit_slider_columns($columns) {
	return $columns
	+ array('slider_shortcode' => __('Shortcode'));
}

function sfs_custom_slider_column($column, $post_id) {

	$slider_meta = get_post_meta($post_id, "_sfs_slider_meta", true);
	$slider_meta = ($slider_meta != '') ? json_decode($slider_meta) : array();

	switch ($column) {
		case 'slider_shortcode':
			echo "[lmsit_sfs id='$post_id' /]";
			break;
	}
}


//Add meta Boxes into Flexi Sliders
add_action('add_meta_boxes', 'sfs_slider_meta_box');

function sfs_slider_meta_box() {

	add_meta_box("sfs-slider-images", "Slider Images", 'sfs_view_slider_images_box', "sfs_js_slider", "normal");
}

function sfs_view_slider_images_box() {
	global $post;

	$gallery_images = get_post_meta($post->ID, "_sfs_gallery_images", true);
	$gallery_images = ($gallery_images != '') ? json_decode($gallery_images) : array();

	// Use nonce for verification
	$html = '<input type="hidden" name="sfs_slider_box_nonce" value="' . wp_create_nonce(basename(__FILE__)) . '" />';

	$html .= '<table class="form-table">';

	$html .= "
	<tr>
	<th style=''><label for='Upload Images'>Image 1</label></th>
	<td><input name='gallery_img[]' id='sfs_slider_upload' type='text' value='" . $gallery_images[0] . "'  /></td>
	</tr>
	<tr>
	<th style=''><label for='Upload Images'>Image 2</label></th>
	<td><input name='gallery_img[]' id='sfs_slider_upload' type='text' value='" . $gallery_images[1] . "' /></td>
	</tr>
	<tr>
	<th style=''><label for='Upload Images'>Image 3</label></th>
	<td><input name='gallery_img[]' id='sfs_slider_upload' type='text'  value='" . $gallery_images[2] . "' /></td>
	</tr>
	<tr>
	<th style=''><label for='Upload Images'>Image 4</label></th>
	<td><input name='gallery_img[]' id='sfs_slider_upload' type='text' value='" . $gallery_images[3] . "' /></td>
	</tr>
	<tr>
	<th style=''><label for='Upload Images'>Image 5</label></th>
	<td><input name='gallery_img[]' id='sfs_slider_upload' type='text' value='" . $gallery_images[4] . "' /></td>
	</tr>

	</table>";

	echo $html;
}



/* Save Slider Options to database */
add_action('save_post', 'sfs_save_slider_info');

function sfs_save_slider_info($post_id) {

	// verify nonce
	if (!wp_verify_nonce($_POST['sfs_slider_box_nonce'], basename(__FILE__))) {
		return $post_id;
	}

	// check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return $post_id;
	}

	// check permissions
	if ('sfs_js_slider' == $_POST['post_type'] && current_user_can('edit_post', $post_id)) {

		/* Save Slider Images */
		$gallery_images = (isset($_POST['gallery_img']) ? $_POST['gallery_img'] : '');
		$gallery_images = strip_tags(json_encode($gallery_images));
		update_post_meta($post_id, "_sfs_gallery_images", $gallery_images);
		 
	} else {
		return $post_id;
	}
}

add_action('admin_menu', 'lmsit_sfs_plugin_settings');

function lmsit_sfs_plugin_settings() {
	//create settings menu
	add_menu_page('Flexi Slider Settings', 'Flexi Slider Settings', 'administrator', 'lmsit_sfs_settings', 'lmsit_sfs_display_settings');
}

function lmsit_sfs_display_settings() {

	$slide_effect = (get_option('sfs_effect') == 'slide') ? 'selected' : '';
	$fade_effect = (get_option('sfs_effect') == 'fade') ? 'selected' : '';
	$slideshowSpeed = (get_option('sfs_slideshowSpeed') != '') ? get_option('sfs_slideshowSpeed') : '2000';
	$animationSpeed = (get_option('sfs_animationSpeed') != '') ? get_option('sfs_animationSpeed') : '3000';
	
	$html = '<div class="wrap">

	<form method="post" name="options" action="options.php">

	<h2>Select Your Settings</h2>' . wp_nonce_field('update-options') . '
	<table width="100%" cellpadding="10" class="form-table">
	<tr>
	<td align="left" scope="row">
	<label>Slider Effect</label><select name="sfs_effect" >
	<option value="slide" ' . $slide_effect . '>Slide</option>
	<option value="fade" '.$fade_effect.'>Fade</option>
	</select>
	 

	</td>
	</tr>
	<tr>
	<td align="left" scope="row">
	<label>Slideshow Speed</label><input type="text" name="sfs_slideshowSpeed"
	value="' . $slideshowSpeed . '" />

	</td>
	</tr>
	<tr>
	<td align="left" scope="row">
	<label>Animation Speed</label><input type="text" name="sfs_animationSpeed"
	value="' . $animationSpeed . '" />

	</td>
	</tr>
	</table>
	<p class="submit">
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="page_options" value="sfs_effect,sfs_slideshowSpeed,sfs_animationSpeed" />
	<input type="submit" name="Submit" value="Update" />
	</p>
	</form>

	</div>';
	echo $html;
}