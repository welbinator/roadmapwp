<?php
/*
Plugin Name: WP Road Map
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: A roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     1.0.5
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wp-roadmap
*/

function wp_roadmap_free_activate() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (is_plugin_active('wproadmap-pro/wp-roadmap-pro.php')) {
        // Schedule the admin notice
        add_action('admin_notices', 'wp_roadmap_free_admin_notice');

        // Redirect back to plugins page
        wp_redirect(admin_url('plugins.php'));
        exit;
    }
    // Additional activation code for Free version goes here...
}
register_activation_hook(__FILE__, 'wp_roadmap_free_activate');

function wp_roadmap_free_admin_notice() {
    echo '<div class="notice notice-warning is-dismissible"><p>RoadMapWP Pro is already installed. The free version has been deactivated.</p></div>';
}


defined('ABSPATH') or die('No script kiddies please!');

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'app/admin-functions.php';
require_once plugin_dir_path(__FILE__) . 'app/cpt-ideas.php';
require_once plugin_dir_path(__FILE__) . 'app/ajax-handlers.php';
require_once plugin_dir_path(__FILE__) . 'app/admin-pages.php';
require_once plugin_dir_path(__FILE__) . 'app/shortcodes.php';

function wp_roadmap_on_activation() {
    // Directly call the function that registers your taxonomies here
    wp_roadmap_register_default_taxonomies();

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

register_activation_hook(__FILE__, 'wp_roadmap_on_activation');

function wp_roadmap_custom_template($template) {
    global $post;

    if ('idea' === $post->post_type) {
        $pro_options = get_option('wp_roadmap_pro_settings');
        $chosen_idea_template = isset($pro_options['single_idea_template']) ? $pro_options['single_idea_template'] : 'plugin';

        if ($chosen_idea_template === 'plugin' && file_exists(plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php')) {
            return plugin_dir_path(__FILE__) . 'app/templates/template-single-idea.php';
        }
    }

    return $template;
}

add_filter('single_template', 'wp_roadmap_custom_template');

