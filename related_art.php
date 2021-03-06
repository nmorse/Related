<?php
/*
Plugin Name: Related_Art
Plugin URI: https://github.com/matthiassiegel/Related_art
Description: A simple 'related art' plugin that lets you select related posts manually instead of automatically generating the list.
Version: 1.1.1
Author: Matthias Siegel
Author URI: https://github.com/matthiassiegel/Related_art


Copyright 2010-2012  Matthias Siegel  (email: matthias.siegel@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if (!class_exists('Related_art')) :
	class Related_art {

		// Constructor
		public function __construct() {
			
			// Set some helpful constants
			$this->defineConstants();
						
			// Register hook to save the related posts when saving the post
			add_action('save_post', array(&$this, 'save'));

			// Start the plugin
			add_action('admin_menu', array(&$this, 'start'));
		}
		

		// Defines a few static helper values we might need
		protected function defineConstants() {
			define('RELATED_VERSION', '1.1.10');
			define('RELATED_HOME', 'https://github.com/matthiassiegel/Related_Art2');
			define('RELATED_FILE', plugin_basename(dirname(__FILE__)));
			define('RELATED_ABSPATH', str_replace('\\', '/', WP_PLUGIN_DIR . '/' . plugin_basename(dirname(__FILE__))));
			define('RELATED_URLPATH', WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)));
		}


		// Main function
		public function start() {
			// Load the scripts
			add_action('admin_print_scripts', array(&$this, 'loadScripts'));
			
			// Load the CSS
			add_action('admin_print_styles', array(&$this, 'loadCSS'));
			
			// Adds a meta box for related posts to the edit screen of each post type in WordPress
			foreach (get_post_types() as $post_type) :
				add_meta_box($post_type . '-related-arts-box', 'Related Articles', array(&$this, 'displayMetaBox'), $post_type, 'normal', 'high');
			endforeach;
		}


		// Load Javascript
		public function loadScripts() {
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('related-art-scripts', RELATED_URLPATH .'/related-art.js', false, RELATED_VERSION);
		}


		// Load CSS
		public function loadCSS() {
			wp_enqueue_style('related-art-css', RELATED_URLPATH .'/related-art.css', false, RELATED_VERSION, 'all');
		}


		// Save related posts when saving the post
		public function save($id) {
			
			global $wpdb;
			
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

			if (!isset($_POST['related-arts']) || empty($_POST['related-arts'])) :
				delete_post_meta($id, 'related_arts');
			else :
				update_post_meta($id, 'related_arts', $_POST['related-arts']);
			endif;
		}


		// Creates the output on the post screen
		public function displayMetaBox() {
        
			global $post;
			
			$post_id = $post->ID;
			
			echo '<div id="related-arts">';
			
			// Get related posts if existing
			$related = get_post_meta($post_id, 'related_arts', true);

			if (!empty($related)) :
				foreach($related as $r) :
					//if (!is_numeric($r)) {
					//	$args=array(
					//		'name' => $r,
					//		'post_type' => 'post',
					//		'post_status' => 'publish',
					//		'posts_per_page' => 1
					//	);
					//	$this_posts = get_posts( $args );
					//	if( $my_posts ) {
					//		$r = $this_posts[0]->ID;
					//	}
					//}
					$p = get_post($r);
					echo '
						<div class="related-art" id="related-art-' . $r->post_name . '">
							<input type="hidden" name="related-arts[]" value="' . $r->post_name . '">
							<span class="related-art-title">' . $p->post_title . ' (' . ucfirst(get_post_type($p->ID)) . ')</span>
							<span>' . is_numeric($r) . '</span>
							<a href="#">Delete</a>
						</div>';
				endforeach;
			endif;
			
			echo '
				</div>
				<p>
					<select id="related-arts-select" name="related-arts-select">
						<option value="0">Select</option>';
			
			$query = array(
				'nopaging' => true,
				'post__not_in' => array($post_id),
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'post_type' => 'any',
				'orderby' => 'title',
				'order' => 'ASC'
			);
			
			$p = new WP_Query($query);
			
			if ($p->have_posts()) :
				while ($p->have_posts()) :
					$p->the_post();
					echo '
						<option value="' . get_the_ID() . '">' . get_the_title() . ' (' . ucfirst(get_post_type(get_the_ID())) . ')</option>';
				endwhile;
			endif;
			
			wp_reset_query();
			wp_reset_postdata();
								
			echo '
					</select>
				</p>
				<p>
					Select related articles from the list. Drag selected ones to change order.
				</p>';
		}


		// The frontend function that is used to display the related post list
		public function show($id, $return = false) {

			global $wpdb;

			if (!empty($id) && is_numeric($id)) :
				$related = get_post_meta($id, 'related_arts', true);
				
				if (!empty($related)) :
					$rel = array();
					foreach ($related as $r) :
						//if (!is_numeric($r)) {
						//	$args=array(
						//		'name' => $r,
						//		'post_type' => 'post',
						//		'post_status' => 'publish',
						//		'posts_per_page' => 1
						//	);
						//	$this_posts = get_posts( $args );
						//	if( $my_posts ) {
						//		$r = $this_posts[0]->ID;
						//	}
						//}
						$p = get_post($r);
						$rel[] = $p;
					endforeach;
					
					// If value should be returned as array, return it
					if ($return) :
						return $rel;
						
					// Otherwise return a formatted list
					else :
						$list = '<ul class="related-arts">';
						foreach ($rel as $r) :
							$list .= '<li><a href="' . get_permalink($r->ID) . '">' . $r->post_title . '</a></li>';
						endforeach;
						$list .= '</ul>';
						
						return $list;
					endif;
				else :
					return false;
				endif;
			else :
				return 'Invalid post ID specified';
			endif;
		}
	}
	
	
	
endif;



// Start the plugin

global $related_art;

$related_art = new Related_art();

?>
