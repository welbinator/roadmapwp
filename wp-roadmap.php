<?php
/*
Plugin Name: RoadMapWP
Plugin URI:  https://apexbranding.design/wp-roadmap
Description: A roadmap plugin where users can submit and vote on ideas, and admins can organize them into a roadmap.
Version:     1.2.1
Author:      James Welbes
Author URI:  https://apexbranding.design
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: roadmapwp-free
*/

namespace RoadMapWP\Free;

function free_activate() {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (is_plugin_active('roadmapwp-pro/wp-roadmap-pro.php')) {
        // Schedule the admin notice
        add_action('admin_notices', __NAMESPACE__ . '\\admin_notice');

        // Redirect back to plugins page
        wp_redirect(admin_url('plugins.php'));
        exit;
    }
    // Additional activation code for Free version goes here...
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\free_activate');

function admin_notice() {
    echo '<div class="notice notice-warning is-dismissible"><p>RoadMapWP Pro is already installed. The free version has been deactivated.</p></div>';
}


defined('ABSPATH') or die('No script kiddies please!');

// Include necessary files
require_once plugin_dir_path( __FILE__ ) . 'app/admin-functions.php';
require_once plugin_dir_path( __FILE__ ) . 'app/cpt-ideas.php';
require_once plugin_dir_path( __FILE__ ) . 'app/ajax-handlers.php';
require_once plugin_dir_path( __FILE__ ) . 'app/admin-pages.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/display-ideas.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/new-idea-form.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/roadmap.php';
require_once plugin_dir_path( __FILE__ ) . 'app/shortcodes/roadmap-tabs.php';
require_once plugin_dir_path( __FILE__ ) . 'app/customizer-styles.php';
require_once plugin_dir_path( __FILE__ ) . 'settings/comments.php';


function on_activation() {
    // Directly call the function that registers your taxonomies here
    \RoadMapWP\Free\CPT\register_default_taxonomies();

    // Now add the terms
    $status_terms = array( 'New Idea', 'Maybe', 'Up Next', 'On Roadmap', 'Not Now', 'Closed' );
	foreach ( $status_terms as $term ) {
		if ( ! term_exists( $term, 'status' ) ) {
			$result = wp_insert_term( $term, 'status' );
			if ( is_wp_error( $result ) ) {
				error_log( 'Error inserting term ' . $term . ': ' . $result->get_error_message() );
			}
		}
	}
    flush_rewrite_rules();
}

register_activation_hook(__FILE__, __NAMESPACE__ . '\\on_activation');

function custom_template( $template ) {
	global $post;

	if ( 'idea' === $post->post_type ) {
		$options          = get_option( 'wp_roadmap_settings' );
		$chosen_idea_template = isset( $options['single_idea_template'] ) ? $options['single_idea_template'] : 'plugin';

		if ( $chosen_idea_template === 'plugin' && file_exists( plugin_dir_path( __FILE__ ) . 'app/templates/template-single-idea.php' ) ) {
			return plugin_dir_path( __FILE__ ) . 'app/templates/template-single-idea.php';
		}
	}

	return $template;
}

add_filter( 'single_template', __NAMESPACE__ . '\\custom_template' );