<?php

/**
 * Check for the presence of specific shortcodes on the page and set global flags for enqueuing CSS files.
 */

/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets a global flag for enqueuing related CSS files if the shortcode is found.
 */
function wp_road_map_check_for_new_idea_shortcode() {
    global $wp_road_map_new_idea_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        $wp_road_map_new_idea_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_new_idea_shortcode');

/**
 * Checks if the 'display_ideas' shortcode is present on the current page.
 * Sets a global flag for enqueuing related CSS files if the shortcode is found.
 */
function wp_road_map_check_for_ideas_shortcode() {
    global $wp_road_map_ideas_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'display_ideas')) {
        $wp_road_map_ideas_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_ideas_shortcode');

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets a global flag for enqueuing related CSS files if the shortcode is found.
 */
function wp_road_map_check_for_roadmap_shortcode() {
    global $wp_road_map_roadmap_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        $wp_road_map_roadmap_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_roadmap_shortcode');

/**
 * Enqueues admin styles for specific admin pages and post types.
 *
 * @param string $hook The current admin page hook.
 */
function wp_road_map_enqueue_admin_styles($hook) {
    global $post;

    // Enqueue CSS for 'idea' post type editor
    if ('post.php' == $hook && isset($post) && 'idea' == $post->post_type) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/idea-editor-styles.css';
        wp_enqueue_style('wp-road-map-idea-admin-styles', $css_url);
    }

    // Enqueue CSS for specific plugin admin pages
    if (in_array($hook, ['roadmap_page_wp-road-map-taxonomies', 'roadmap_page_wp-road-map-settings'])) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
        wp_enqueue_style('wp-road-map-general-admin-styles', $css_url);
    }
}
add_action('admin_enqueue_scripts', 'wp_road_map_enqueue_admin_styles');

/**
 * Enqueues front end styles and scripts for the plugin.
 *
 * This function checks whether any of the plugin's shortcodes are loaded or if it's a singular 'idea' post,
 * and enqueues the necessary styles and scripts.
 */
function wp_road_map_enqueue_frontend_styles() {
    global $wp_road_map_new_idea_shortcode_loaded, $wp_road_map_ideas_shortcode_loaded, $wp_road_map_roadmap_shortcode_loaded;

    // Consolidate shortcode load states
    $wp_road_map_shortcodes_loaded = array(
        $wp_road_map_new_idea_shortcode_loaded,
        $wp_road_map_ideas_shortcode_loaded,
        $wp_road_map_roadmap_shortcode_loaded,
    );

    // Enqueue styles if any shortcode is loaded
    if (in_array(true, $wp_road_map_shortcodes_loaded, true) || is_singular('idea')) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-road-map-frontend.css'; 
        wp_enqueue_style('wp-road-map-frontend-styles', $css_url);
    }

    // Enqueue scripts and localize them
    wp_enqueue_script('wp-road-map-voting', plugin_dir_url(__FILE__) . 'assets/js/voting.js', array('jquery'), null, true);
    wp_localize_script('wp-road-map-voting', 'wpRoadMapVoting', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-road-map-vote-nonce')
    ));

    wp_enqueue_script('wp-road-map-idea-filter', plugin_dir_url(__FILE__) . 'assets/js/idea-filter.js', array('jquery'), '', true);
    wp_localize_script('wp-road-map-idea-filter', 'wpRoadMapAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-road-map-vote-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'wp_road_map_enqueue_frontend_styles');

/**
 * Adds admin menu pages for the plugin.
 *
 * This function creates a top-level menu item 'RoadMap' in the admin dashboard,
 * along with several submenu pages like Settings, Ideas, and Taxonomies.
 */
function wp_road_map_add_admin_menu() {
    add_menu_page(
        __('RoadMap', 'wp-road-map'), 
        __('RoadMap', 'wp-road-map'), 
        'manage_options', 
        'wp-road-map', 
        'wp_road_map_settings_page', 
        'dashicons-chart-line', 
        6
    );

    add_submenu_page(
        'wp-road-map',
        __('Settings', 'wp-road-map'),
        __('Settings', 'wp-road-map'),
        'manage_options',
        'wp-road-map-settings',
        'wp_road_map_settings_page'
    );

    add_submenu_page(
        'wp-road-map',
        __('Ideas', 'wp-road-map'),
        __('Ideas', 'wp-road-map'),
        'manage_options',
        'edit.php?post_type=idea'
    );

    add_submenu_page(
        'wp-road-map',
        __('Taxonomies', 'wp-road-map'),
        __('Taxonomies', 'wp-road-map'),
        'manage_options',
        'wp-road-map-taxonomies',
        'wp_road_map_taxonomies_page'
    );

    remove_submenu_page('wp-road-map', 'wp-road-map');
}
add_action('admin_menu', 'wp_road_map_add_admin_menu');

/**
 * Registers settings for the RoadMap plugin.
 *
 * This function sets up a settings section for the plugin, allowing configuration of various features and functionalities.
 */
function wp_road_map_register_settings() {
    register_setting('wp_road_map_settings', 'wp_road_map_settings');
}
add_action('admin_init', 'wp_road_map_register_settings');

/**
 * Dynamically enables or disables comments on 'idea' post types.
 *
 * @param bool $open Whether the comments are open.
 * @param int $post_id The post ID.
 * @return bool Modified status of comments open.
 */
function wp_road_map_filter_comments_open($open, $post_id) {
    $post = get_post($post_id);
    $options = get_option('wp_road_map_settings');
    if ($post->post_type == 'idea') {
        return isset($options['allow_comments']) && $options['allow_comments'] == 1;
    }
    return $open;
}
add_filter('comments_open', 'wp_road_map_filter_comments_open', 10, 2);
