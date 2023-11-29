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

global $wp_road_map_new_idea_shortcode_loaded;
$wp_road_map_new_idea_shortcode_loaded = false;

global $wp_road_map_ideas_shortcode_loaded;
$wp_road_map_ideas_shortcode_loaded = false;

global $wp_road_map_roadmap_shortcode_loaded;
$wp_road_map_roadmap_shortcode_loaded = false;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Include admin functions
require_once plugin_dir_path( __FILE__ ) . 'app/admin-functions.php';

// Include Custom Post Type definition
require_once plugin_dir_path( __FILE__ ) . 'app/cpt-ideas.php';

// Include ajax hanlders
require_once plugin_dir_path( __FILE__ ) . 'app/ajax-handlers.php';

// Register admin pages
require_once plugin_dir_path( __FILE__ ) . 'app/admin-pages.php';

// Shortcodes
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes.php';

function wp_road_map_on_activation() {
    // Directly call the function that registers your taxonomies here
    wp_road_map_register_default_taxonomies();

    // Now add the terms
    $status_terms = array('New Idea', 'Maybe', 'Up Next', 'On Roadmap', 'Not Now', 'Closed');
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

// use custom template for idea cpt
function wp_road_map_custom_template($template) {
    global $post;

    if ('idea' === $post->post_type && file_exists(plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php')) {
        return plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php';
    }

    return $template;
}
add_filter('single_template', 'wp_road_map_custom_template');





