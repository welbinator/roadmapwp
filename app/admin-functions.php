<?php

// check if shortcode exists on page
function wp_road_map_check_for_shortcode() {
    global $wp_road_map_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        $wp_road_map_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_shortcode');

// enqueue admin styles
function wp_road_map_enqueue_admin_styles($hook) {
    error_log($hook);
    // Check if we are on the specific admin page
    if ( 'roadmap_page_wp-road-map-taxonomies' !== $hook  && 'roadmap_page_wp-road-map-settings' !== $hook  ){
        return;
    }

    // The URL of the CSS file
    $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';

    // Enqueue the style
    wp_enqueue_style('wp-road-map-admin-styles', $css_url);
}
add_action('admin_enqueue_scripts', 'wp_road_map_enqueue_admin_styles');

// enqueue front end styles
function wp_road_map_enqueue_frontend_styles() {
    global $wp_road_map_shortcode_loaded;

    if (!$wp_road_map_shortcode_loaded) {
        return;
    }

    // Correct path to the CSS file
    $css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-road-map-frontend.css'; 
    wp_enqueue_style('wp-road-map-frontend-styles', $css_url);
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

// Function to display WP RoadMap settings page
function wp_road_map_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Settings form fields go here
            ?>
        </form>
    </div>
    <?php
}

// Function to display the Taxonomies management page
function wp_road_map_taxonomies_page() {

    $message = '';

    // Check if a new term is being added
    // Check if a new term is being added
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_road_map_add_term_nonce']) && wp_verify_nonce($_POST['wp_road_map_add_term_nonce'], 'add_term_to_' . $_POST['taxonomy_slug'])) {
        $new_term = sanitize_text_field($_POST['new_term']);
        $taxonomy_slug = sanitize_key($_POST['taxonomy_slug']);

        if (!empty($new_term)) {
            if (!term_exists($new_term, $taxonomy_slug)) {
                $inserted_term = wp_insert_term($new_term, $taxonomy_slug);
                if (is_wp_error($inserted_term)) {
                    $message = 'Error adding term: ' . $inserted_term->get_error_message();
                } else {
                    $message = 'Term "' . esc_html($new_term) . '" added successfully to ' . esc_html($taxonomy_slug) . '.';
                }
            } else {
                $message = 'The term "' . esc_html($new_term) . '" already exists in ' . esc_html($taxonomy_slug) . '.';
            }
        }
    }

    // Display the message
    if (!empty($message)) {
        echo '<div class="notice notice-info"><p>' . $message . '</p></div>';
    }

    // Check if the user has the right capability
    if (!current_user_can('manage_options')) {
        return;
    }

    $error_message = '';

    // Handle taxonomy deletion
    if (isset($_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce']) && $_GET['action'] == 'delete') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_taxonomy_' . $_GET['taxonomy'])) {
            $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
            unset($custom_taxonomies[$_GET['taxonomy']]);
            update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);
            // error_log('Deleted taxonomy: ' . $_GET['taxonomy']);
        }
    }

    // Check if the form has been submitted
    if (isset($_POST['wp_road_map_nonce'], $_POST['taxonomy_slug'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['wp_road_map_nonce'], 'wp_road_map_add_taxonomy')) {
            $error_message = 'Invalid nonce...';
        } else {
            // Sanitize and validate inputs
            $raw_taxonomy_slug = sanitize_key($_POST['taxonomy_slug']);
            
            // Validate slug: only letters, numbers, and underscores
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $raw_taxonomy_slug)) {
                $error_message = 'Invalid taxonomy slug. Only letters, numbers, and underscores are allowed.';
            } elseif (taxonomy_exists($raw_taxonomy_slug)) {
                $error_message = 'The taxonomy "' . esc_html($raw_taxonomy_slug) . '" already exists.';
            } else {
                $hierarchical = isset($_POST['hierarchical']) ? (bool) $_POST['hierarchical'] : false;
                $public = isset($_POST['public']) ? (bool) $_POST['public'] : false;
                $taxonomy_singular = sanitize_text_field($_POST['taxonomy_singular']);
                $taxonomy_plural = sanitize_text_field($_POST['taxonomy_plural']);

                // Register the taxonomy
                $labels = array(
                    'name' => _x($taxonomy_plural, 'taxonomy general name'),
                    'singular_name' => _x($taxonomy_singular, 'taxonomy singular name'),
                    'search_items' => __('Search ' . $taxonomy_plural),
                    'all_items' => __('All ' . $taxonomy_plural),
                    'parent_item' => __('Parent ' . $taxonomy_singular),
                    'parent_item_colon' => __('Parent ' . $taxonomy_singular . ':'),
                    'edit_item' => __('Edit ' . $taxonomy_singular),
                    'update_item' => __('Update ' . $taxonomy_singular),
                    'add_new_item' => __('Add New ' . $taxonomy_singular),
                    'new_item_name' => __('New ' . $taxonomy_singular . ' Name'),
                    'menu_name' => __($taxonomy_plural),
                );

                $taxonomy_data = array(
                    'labels' => $labels, 
                    'public' => $public,
                    'hierarchical' => false,
                    'show_ui' => true,
                    'show_in_rest' => true,
                    'show_admin_column' => true,
                    'query_var' => true,
                    'rewrite' => array('slug' => $taxonomy_slug),
                );

                register_taxonomy($taxonomy_slug, 'idea', $taxonomy_data);
                // error_log('Registered taxonomy: ' . $taxonomy_slug);

                // Add the new taxonomy
                $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
                $custom_taxonomies[$taxonomy_slug] = $taxonomy_data;

                // Update the option
                update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);
                // error_log('Taxonomy saved: ' . $taxonomy_slug . '; Current taxonomies: ' . print_r($custom_taxonomies, true));

                if (empty($error_message)) {
                    echo '<div class="notice notice-success is-dismissible"><p>Taxonomy created successfully.</p></div>';
                }
            }
        }
    
        // Display error message if it exists
        if (!empty($error_message)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
        }
    }

    // Form HTML
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="new_taxonomy_form">
        <h2>Add New Taxonomy</h2>
            <form action="" method="post">
                <?php wp_nonce_field('wp_road_map_add_taxonomy', 'wp_road_map_nonce'); ?>
                <ul class="flex-outer">
                    <li class="new_taxonomy_form_input">
                        <label for="taxonomy_slug">Slug:</label>
                        <input type="text" id="taxonomy_slug" name="taxonomy_slug" required>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <label for="taxonomy_singular">Singular Name:</label>
                        <input type="text" id="taxonomy_singular" name="taxonomy_singular" required>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <label for="taxonomy_plural">Plural Name:</label>
                        <input type="text" id="taxonomy_plural" name="taxonomy_plural" required>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <label for="public">Public:</label>
                        <select id="public" name="public">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <input type="submit" value="Add Taxonomy">
                    </li>
                </ul>
            </form>
        </div>
    </div>
    <hr style="margin-block: 50px;" />
    <?php

    // Retrieve and display taxonomies
    echo '<h2>Registered Taxonomies</h2>';
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
    if (!empty($custom_taxonomies)) {
       
        echo '<ul>';

        foreach ($custom_taxonomies as $taxonomy_slug => $taxonomy_data) {
            echo '<li><strong>Taxonomy slug:</strong> ' . esc_html($taxonomy_slug);

            // Form for adding terms to this taxonomy
            echo '<form action="' . esc_url(admin_url('admin.php?page=wp-road-map-taxonomies')) . '" method="post">';
            echo '<input type="text" name="new_term" placeholder="New Term" />';
            echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr($taxonomy_slug) . '" />';
            echo '<input type="submit" value="Add Term" />';
            echo wp_nonce_field('add_term_to_' . $taxonomy_slug, 'wp_road_map_add_term_nonce');
            echo '</form>';

            echo '</li>';
        }

        echo '</ul>';
    }
}

// shortcode
function wp_road_map_new_idea_form_shortcode() {
    global $wp_road_map_shortcode_loaded;
    $wp_road_map_shortcode_loaded = true;

    $output = '';

    // Check if the form has been submitted
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_road_map_new_idea_nonce']) && wp_verify_nonce($_POST['wp_road_map_new_idea_nonce'], 'wp_road_map_new_idea')) {
        $title = sanitize_text_field($_POST['idea_title']);
        $description = sanitize_textarea_field($_POST['idea_description']);

        // Create new Idea post
        $idea_id = wp_insert_post(array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',  // or 'pending' if you want to review submissions
            'post_type'     => 'idea',
        ));

        // Handle taxonomies
        if (isset($_POST['idea_taxonomies']) && is_array($_POST['idea_taxonomies'])) {
            foreach ($_POST['idea_taxonomies'] as $tax_slug => $term_ids) {
                $term_ids = array_map('intval', $term_ids); // Sanitize term IDs
                wp_set_object_terms($idea_id, $term_ids, $tax_slug);
            }
        }

        $output .= '<p>Thank you for your submission!</p>';
    }

    // Display the form
    $output .= '<div class="new_taxonomy_form__frontend">';
    $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    $output .= '<ul class="flex-outer">';
    $output .= '<li class="new_taxonomy_form_input"><label for="idea_title">Title:</label>';
    $output .= '<input type="text" name="idea_title" id="idea_title" required></li>';
    $output .= '<li class="new_taxonomy_form_input"><label for="idea_description">Description:</label>';
    $output .= '<textarea name="idea_description" id="idea_description" required></textarea></li>';

    // Fetch taxonomies associated with the 'idea' post type
    $taxonomies = get_object_taxonomies('idea', 'objects');
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));

        if (!empty($terms) && !is_wp_error($terms)) {
            $output .='<li class="new_taxonomy_form_input">';
            $output .= '<label for="idea_taxonomy_' . esc_attr($taxonomy->name) . '">' . esc_html($taxonomy->labels->singular_name) . ':</label>';
            $output .= '<select name="idea_taxonomies[' . esc_attr($taxonomy->name) . '][]" id="idea_taxonomy_' . esc_attr($taxonomy->name) . '" multiple>';

            foreach ($terms as $term) {
                $output .= '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
            }

            $output .= '</select>';
            $output .= '</li>';
        }
    }

    // Nonce field for security
    $output .= wp_nonce_field('wp_road_map_new_idea', 'wp_road_map_new_idea_nonce');

    $output .= '<li class="new_taxonomy_form_input"><input type="submit" value="Submit Idea"></li>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}
add_shortcode('new_idea_form', 'wp_road_map_new_idea_form_shortcode');







