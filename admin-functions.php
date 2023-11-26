<?php

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
            error_log('Deleted taxonomy: ' . $_GET['taxonomy']);
        }
    }

    // Check if the form has been submitted
    if (isset($_POST['wp_road_map_nonce'], $_POST['taxonomy_slug'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['wp_road_map_nonce'], 'wp_road_map_add_taxonomy')) {
            $error_message = 'Invalid nonce...'; 
        } else {
            // Sanitize and validate inputs
            $raw_taxonomy_slug = $_POST['taxonomy_slug'];
            
            // Validate slug: only letters, numbers, and underscores
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $raw_taxonomy_slug)) {
                $error_message = 'Invalid taxonomy slug. Only letters, numbers, and underscores are allowed.';
            } else {
                $taxonomy_slug = sanitize_key($raw_taxonomy_slug);
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
                    'hierarchical' => $hierarchical,
                    'show_ui' => true,
                    'show_in_rest' => true,
                    'show_admin_column' => true,
                    'query_var' => true,
                    'rewrite' => array('slug' => $taxonomy_slug),
                );

                register_taxonomy($taxonomy_slug, 'idea', $taxonomy_data);
                error_log('Registered taxonomy: ' . $taxonomy_slug);

                // Add the new taxonomy
                $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
                $custom_taxonomies[$taxonomy_slug] = $taxonomy_data;

                // Update the option
                update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);
                error_log('Taxonomy saved: ' . $taxonomy_slug . '; Current taxonomies: ' . print_r($custom_taxonomies, true));

                if (empty($error_message)) {
                    echo '<div class="notice notice-success is-dismissible"><p>Taxonomy created successfully.</p></div>';
                }
            }
        }
    }

    // Display error message if it exists
    if (!empty($error_message)) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
    }

    // Form HTML
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="" method="post">
            <?php wp_nonce_field('wp_road_map_add_taxonomy', 'wp_road_map_nonce'); ?>

            <div class="new_taxonomy_form_input">
                <label for="taxonomy_slug">Slug:</label>
                <input type="text" id="taxonomy_slug" name="taxonomy_slug" required>
            </div>

            <div class="new_taxonomy_form_input">
                <label for="taxonomy_singular">Singular Name:</label>
                <input type="text" id="taxonomy_singular" name="taxonomy_singular" required>
            </div>

            <div class="new_taxonomy_form_input">
                <label for="taxonomy_plural">Plural Name:</label>
                <input type="text" id="taxonomy_plural" name="taxonomy_plural" required>
            </div>
            
            <div class="new_taxonomy_form_input">
                <label for="hierarchical">Hierarchical:</label>
                <select id="hierarchical" name="hierarchical">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="new_taxonomy_form_input">
                <label for="public">Public:</label>
                <select id="public" name="public">
                    <option value="1">Yes</option>
                    <option value="0">No</option>
                </select>
            </div>

            <div class="new_taxonomy_form_input">
                <input type="submit" value="Add Taxonomy">
            </div>
        </form>
    </div>
    <?php

    // Retrieve and display taxonomies
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
    if (!empty($custom_taxonomies)) {
        echo '<h2>Existing Taxonomies</h2>';
        echo '<ul>';
        foreach ($custom_taxonomies as $taxonomy_slug => $taxonomy_data) {
            $delete_link = wp_nonce_url(
                admin_url('admin.php?page=wp-road-map-taxonomies&action=delete&taxonomy=' . $taxonomy_slug),
                'delete_taxonomy_' . $taxonomy_slug
            );
            echo '<li>' . esc_html($taxonomy_slug) . ' - <a href="' . esc_url($delete_link) . '">Delete</a></li>';
        }
        echo '</ul>';
    }
}

add_action('admin_menu', 'wp_road_map_add_admin_menu');






