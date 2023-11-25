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

    // Check if the form has been submitted
    if (isset($_POST['wp_road_map_nonce'], $_POST['taxonomy_name'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['wp_road_map_nonce'], 'wp_road_map_add_taxonomy')) {
            die('Invalid nonce...'); 
        }

        // Sanitize and validate inputs
        $taxonomy_name = sanitize_key($_POST['taxonomy_name']);
        $taxonomy_label = sanitize_text_field($_POST['taxonomy_label']);
        $hierarchical = isset($_POST['hierarchical']) ? (bool) $_POST['hierarchical'] : false;
        $public = isset($_POST['public']) ? (bool) $_POST['public'] : false;

        // Register the taxonomy (you might want to move this to a separate function)
        if (!taxonomy_exists($taxonomy_name)) {
            register_taxonomy(
                $taxonomy_name,
                'idea',
                array(
                    'label' => $taxonomy_label,
                    'public' => $public,
                    'hierarchical' => $hierarchical,
                )
            );
            echo '<div class="notice notice-success is-dismissible"><p>Taxonomy created successfully.</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>This taxonomy already exists.</p></div>';
        }

        // Inside your form submission handling
if (!taxonomy_exists($taxonomy_name)) {
    $taxonomy_data = array(
        'label' => $taxonomy_label,
        'public' => $public,
        'hierarchical' => $hierarchical,
    );

    // Get existing taxonomies
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
    
    // Add the new taxonomy
    $custom_taxonomies[$taxonomy_name] = $taxonomy_data;
    
    // Update the option
    update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);
}

    }

    // The form HTML
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="" method="post">
            <?php wp_nonce_field('wp_road_map_add_taxonomy', 'wp_road_map_nonce'); ?>

            <label for="taxonomy_name">Name:</label>
            <input type="text" id="taxonomy_name" name="taxonomy_name" required>

            <label for="taxonomy_label">Label:</label>
            <input type="text" id="taxonomy_label" name="taxonomy_label" required>

            <label for="hierarchical">Hierarchical:</label>
            <select id="hierarchical" name="hierarchical">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <label for="public">Public:</label>
            <select id="public" name="public">
                <option value="1">Yes</option>
                <option value="0">No</option>
            </select>

            <input type="submit" value="Add Taxonomy">
        </form>
    </div>
    <?php
}
add_action('admin_menu', 'wp_road_map_add_admin_menu');



