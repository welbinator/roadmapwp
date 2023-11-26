<?php
/*
Plugin Name: WP Road Map
Plugin URI:  https://apexbranding.design/wp-road-map
Description: A roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     1.0
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-road-map
*/

global $wp_road_map_shortcode_loaded;
$wp_road_map_shortcode_loaded = false;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Include admin functions
require_once plugin_dir_path( __FILE__ ) . 'app/admin-functions.php';

// Include Custom Post Type definition
require_once plugin_dir_path( __FILE__ ) . 'app/cpt-ideas.php';

// Include initialization functions
require_once plugin_dir_path( __FILE__ ) . 'init.php';

function wp_road_map_on_activation() {
    // Directly call the function that registers your taxonomies here
    wp_road_map_register_default_taxonomies();

    // Now add the terms
    $status_terms = array('New Idea', 'Maybe', 'On Roadmap', 'Not Now', 'Closed');
    foreach ($status_terms as $term) {
        if (!term_exists($term, 'status')) {
            $result = wp_insert_term($term, 'status');
            if (is_wp_error($result)) {
                error_log('Error inserting term ' . $term . ': ' . $result->get_error_message());
            }
        }
    }
}

register_activation_hook(__FILE__, 'wp_road_map_on_activation');
