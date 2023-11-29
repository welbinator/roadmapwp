<?php

/**
 * Check for the presence of specific shortcodes on the page and set global flags for enqueuing CSS files.
 */

/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets a global flag for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_new_idea_shortcode() {
    global $wp_roadmap_new_idea_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        $wp_roadmap_new_idea_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_roadmap_check_for_new_idea_shortcode');

/**
 * Checks if the 'display_ideas' shortcode is present on the current page.
 * Sets a global flag for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_ideas_shortcode() {
    global $wp_roadmap_ideas_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'display_ideas')) {
        $wp_roadmap_ideas_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_roadmap_check_for_ideas_shortcode');

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets a global flag for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_roadmap_shortcode() {
    global $wp_roadmap_roadmap_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        $wp_roadmap_roadmap_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_roadmap_check_for_roadmap_shortcode');

/**
 * Enqueues admin styles for specific admin pages and post types.
 *
 * @param string $hook The current admin page hook.
 */
function wp_roadmap_enqueue_admin_styles($hook) {
    global $post;

    // Enqueue CSS for 'idea' post type editor
    if ('post.php' == $hook && isset($post) && 'idea' == $post->post_type) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/idea-editor-styles.css';
        wp_enqueue_style('wp-roadmap-idea-admin-styles', $css_url);
    }

    // Enqueue CSS for specific plugin admin pages
    if (in_array($hook, ['roadmap_page_wp-roadmap-taxonomies', 'roadmap_page_wp-roadmap-settings'])) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
        wp_enqueue_style('wp-roadmap-general-admin-styles', $css_url);
    }
}
add_action('admin_enqueue_scripts', 'wp_roadmap_enqueue_admin_styles');

/**
 * Enqueues front end styles and scripts for the plugin.
 *
 * This function checks whether any of the plugin's shortcodes are loaded or if it's a singular 'idea' post,
 * and enqueues the necessary styles and scripts.
 */
function wp_roadmap_enqueue_frontend_styles() {
    global $post;

    // Check for shortcode presence
    $has_shortcode = has_shortcode($post->post_content, 'new_idea_form') ||
                     has_shortcode($post->post_content, 'display_ideas') ||
                     has_shortcode($post->post_content, 'roadmap');

    // Check for block presence
    $has_block = has_block('wp-roadmap-pro/new-idea-form', $post) ||
                 has_block('wp-roadmap-pro/display-ideas', $post) ||
                 has_block('wp-roadmap-pro/roadmap', $post);

    // Enqueue styles if a shortcode or block is loaded
    if ($has_shortcode || $has_block || is_singular('idea')) {
        // Enqueue Tailwind CSS
        $tailwind_css_url = plugin_dir_url(__FILE__) . '../dist/styles.css';
        wp_enqueue_style('wp-roadmap-tailwind-styles', $tailwind_css_url);

        // Enqueue your custom frontend styles (this will override Tailwind styles where applicable)
        $custom_css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-roadmap-frontend.css';
        wp_enqueue_style('wp-roadmap-frontend-styles', $custom_css_url);
    }

    // Enqueue scripts and localize them as before
    wp_enqueue_script('wp-roadmap-voting', plugin_dir_url(__FILE__) . 'assets/js/voting.js', array('jquery'), null, true);
    wp_localize_script('wp-roadmap-voting', 'wpRoadMapVoting', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-roadmap-vote-nonce')
    ));

    wp_enqueue_script('wp-roadmap-idea-filter', plugin_dir_url(__FILE__) . 'assets/js/idea-filter.js', array('jquery'), '', true);
    wp_localize_script('wp-roadmap-idea-filter', 'wpRoadMapAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-roadmap-vote-nonce')
    ));
}

add_action('wp_enqueue_scripts', 'wp_roadmap_enqueue_frontend_styles');


/**
 * Adds admin menu pages for the plugin.
 *
 * This function creates a top-level menu item 'RoadMap' in the admin dashboard,
 * along with several submenu pages like Settings, Ideas, and Taxonomies.
 */
function wp_roadmap_add_admin_menu() {
    add_menu_page(
        __('RoadMap', 'wp-roadmap'), 
        __('RoadMap', 'wp-roadmap'), 
        'manage_options', 
        'wp-roadmap', 
        'wp_roadmap_settings_page', 
        'dashicons-chart-line', 
        6
    );

    add_submenu_page(
        'wp-roadmap',
        __('Settings', 'wp-roadmap'),
        __('Settings', 'wp-roadmap'),
        'manage_options',
        'wp-roadmap-settings',
        'wp_roadmap_settings_page'
    );

    add_submenu_page(
        'wp-roadmap',
        __('Ideas', 'wp-roadmap'),
        __('Ideas', 'wp-roadmap'),
        'manage_options',
        'edit.php?post_type=idea'
    );

    add_submenu_page(
        'wp-roadmap',
        __('Taxonomies', 'wp-roadmap'),
        __('Taxonomies', 'wp-roadmap'),
        'manage_options',
        'wp-roadmap-taxonomies',
        'wp_roadmap_taxonomies_page'
    );

    remove_submenu_page('wp-roadmap', 'wp-roadmap');
}
add_action('admin_menu', 'wp_roadmap_add_admin_menu');

/**
 * Registers settings for the RoadMap plugin.
 *
 * This function sets up a settings section for the plugin, allowing configuration of various features and functionalities.
 */
function wp_roadmap_register_settings() {
    register_setting('wp_roadmap_settings', 'wp_roadmap_settings');
}
add_action('admin_init', 'wp_roadmap_register_settings');

/**
 * Dynamically enables or disables comments on 'idea' post types.
 *
 * @param bool $open Whether the comments are open.
 * @param int $post_id The post ID.
 * @return bool Modified status of comments open.
 */
function wp_roadmap_filter_comments_open($open, $post_id) {
    $post = get_post($post_id);
    $options = get_option('wp_roadmap_settings');
     
    if ($post->post_type == 'idea') {
        return isset($options['allow_comments']) && $options['allow_comments'] == 1;
    }
    return $open;
}
add_filter('comments_open', 'wp_roadmap_filter_comments_open', 10, 2);
