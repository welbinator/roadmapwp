<?php

// functions check if a shortcode is displayed and sets a global variable to true for purposes of enqueuing css files

// function to check if new idea form shortcode is displayed on a page and sets global variable to true
function wp_road_map_check_for_new_idea_shortcode() {
    global $wp_road_map_new_idea_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        $wp_road_map_new_idea_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_new_idea_shortcode');

// function to check if display ideas shortcode is displayed on the page and sets global variable to true
function wp_road_map_check_for_ideas_shortcode() {
    global $wp_road_map_ideas_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'display_ideas')) {
        $wp_road_map_ideas_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_ideas_shortcode');

// function to check if roadmap shortcode is displayed on the page, and sets global variable to true
function wp_road_map_check_for_roadmap_shortcode() {
    global $wp_road_map_roadmap_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        $wp_road_map_roadmap_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_roadmap_shortcode');

// enqueue admin styles
function wp_road_map_enqueue_admin_styles($hook) {
    global $post;

    // Enqueue CSS for 'idea' post type editor
    if ('post.php' == $hook && isset($post) && 'idea' == $post->post_type) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/idea-editor-styles.css';
        wp_enqueue_style('wp-road-map-idea-admin-styles', $css_url);
    }

    // Enqueue CSS for other specific plugin admin pages
    if (in_array($hook, ['roadmap_page_wp-road-map-taxonomies', 'roadmap_page_wp-road-map-settings'])) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
        wp_enqueue_style('wp-road-map-general-admin-styles', $css_url);
    }
}
add_action('admin_enqueue_scripts', 'wp_road_map_enqueue_admin_styles');


// enqueue front end styles
function wp_road_map_enqueue_frontend_styles() {
    // Declare the global variables
    global $wp_road_map_new_idea_shortcode_loaded, $wp_road_map_ideas_shortcode_loaded, $wp_road_map_roadmap_shortcode_loaded;

    // Consolidate the shortcode load states into an array
    $wp_road_map_shortcodes_loaded = array(
        $wp_road_map_new_idea_shortcode_loaded,
        $wp_road_map_ideas_shortcode_loaded,
        $wp_road_map_roadmap_shortcode_loaded,
        // Add more shortcode flags here as needed
    );

    // Check if any of the shortcodes are loaded
    if (in_array(true, $wp_road_map_shortcodes_loaded, true) || is_singular('idea') ) {
        error_log('new idea' . $wp_road_map_new_idea_shortcode_loaded);
        error_log('ideas' . $wp_road_map_ideas_shortcode_loaded);
        error_log('roadmap' . $wp_road_map_roadmap_shortcode_loaded);
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-road-map-frontend.css'; 
        wp_enqueue_style('wp-road-map-frontend-styles', $css_url);
    }

    wp_enqueue_script('wp-road-map-voting', plugin_dir_url(__FILE__) . 'assets/js/voting.js', array('jquery'), null, true);
    wp_localize_script('wp-road-map-voting', 'wpRoadMapVoting', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-road-map-vote-nonce')
    ));

    // Enqueue the idea-filter JavaScript file
    wp_enqueue_script('wp-road-map-idea-filter', plugin_dir_url(__FILE__) . 'assets/js/idea-filter.js', array('jquery'), '', true);

    // Localize the script with the AJAX URL
    wp_localize_script('wp-road-map-idea-filter', 'wpRoadMapAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-road-map-vote-nonce') // Same nonce used in the AJAX handler
    ));
}

add_action('wp_enqueue_scripts', 'wp_road_map_enqueue_frontend_styles');


// add menus
function wp_road_map_add_admin_menu() {
    // Add top-level menu page
    add_menu_page(
        __('RoadMap', 'wp-road-map'), // Page title
        __('RoadMap', 'wp-road-map'), // Menu title
        'manage_options', // Capability
        'wp-road-map', // Menu slug
        'wp_road_map_settings_page', // Function to display the settings page
        'dashicons-chart-line', // Icon URL
        6 // Position
    );

    // Add submenu page for Settings
    add_submenu_page(
        'wp-road-map', // Parent slug
        __('Settings', 'wp-road-map'), // Page title
        __('Settings', 'wp-road-map'), // Menu title
        'manage_options', // Capability
        'wp-road-map-settings', // Menu slug
        'wp_road_map_settings_page' // Function to display the settings page
    );

    // Add submenu page for Ideas
    add_submenu_page(
        'wp-road-map', // Parent slug
        __('Ideas', 'wp-road-map'), // Page title
        __('Ideas', 'wp-road-map'), // Menu title
        'manage_options', // Capability
        'edit.php?post_type=idea' // Menu slug to the Ideas CPT
    );

    // Add submenu page for Taxonomies
add_submenu_page(
    'wp-road-map', // Parent slug
    __('Taxonomies', 'wp-road-map'), // Page title
    __('Taxonomies', 'wp-road-map'), // Menu title
    'manage_options', // Capability
    'wp-road-map-taxonomies', // Menu slug
    'wp_road_map_taxonomies_page' // Function to display the Taxonomies page
);

    // Remove duplicate RoadMap submenu item
    remove_submenu_page('wp-road-map', 'wp-road-map');
}
add_action('admin_menu', 'wp_road_map_add_admin_menu');


// registering settings
function wp_road_map_register_settings() {
    register_setting('wp_road_map_settings', 'wp_road_map_settings');
}
add_action('admin_init', 'wp_road_map_register_settings');

// filter that dynamically enables or disables comments on idea posts
function wp_road_map_filter_comments_open($open, $post_id) {
    $post = get_post($post_id);
    $options = get_option('wp_road_map_settings');
    if ($post->post_type == 'idea') {
        if (isset($options['allow_comments']) && $options['allow_comments'] == 1) {
            return true; // Enable comments
        } else {
            return false; // Disable comments
        }
    }
    return $open;
}
add_filter('comments_open', 'wp_road_map_filter_comments_open', 10, 2);



