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

global $wp_road_map_ideas_shortcode_loaded;
$wp_road_map_ideas_shortcode_loaded = false;

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

// use custom template for idea cpt
function wp_road_map_custom_template($template) {
    global $post;

    if ('idea' === $post->post_type && file_exists(plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php')) {
        return plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php';
    }

    return $template;
}
add_filter('single_template', 'wp_road_map_custom_template');


// ajax handling for voting functionality
function wp_road_map_handle_vote() {
    check_ajax_referer('wp-road-map-vote-nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $user_id = get_current_user_id();

    // Check if the user is logged in
    if (!$user_id) {
        wp_send_json_error('User not logged in.');
        wp_die();
    }

    // Retrieve the current vote count
    $current_votes = get_post_meta($post_id, 'idea_votes', true) ?: 0;
    
    // Check if this user has already voted
    $has_voted = get_user_meta($user_id, 'voted_for_idea_' . $post_id, true);

    if ($has_voted) {
        // User has voted, remove their vote
        $new_votes = max($current_votes - 1, 0);
        delete_user_meta($user_id, 'voted_for_idea_' . $post_id);
    } else {
        // User hasn't voted, add their vote
        $new_votes = $current_votes + 1;
        update_user_meta($user_id, 'voted_for_idea_' . $post_id, true);
    }

    // Update the post meta with the new vote count
    update_post_meta($post_id, 'idea_votes', $new_votes);

    // Send the new vote count and voting status back to the client
    wp_send_json_success(array('new_count' => $new_votes, 'voted' => !$has_voted));

    wp_die();
}

add_action('wp_ajax_wp_road_map_handle_vote', 'wp_road_map_handle_vote');
add_action('wp_ajax_nopriv_wp_road_map_handle_vote', 'wp_road_map_handle_vote');


