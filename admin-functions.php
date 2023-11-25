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

    // Handle taxonomy deletion
    if (isset($_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce']) && $_GET['action'] == 'delete') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_taxonomy_' . $_GET['taxonomy'])) {
            $taxonomies = get_option('wp_road_map_custom_taxonomies', array());
            unset($taxonomies[$_GET['taxonomy']]);
            update_option('wp_road_map_custom_taxonomies', $taxonomies);
            // Optionally, flush rewrite rules if necessary
        }
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

        // Get existing taxonomies
        $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());

        // Check if taxonomy already exists
        if (isset($custom_taxonomies[$taxonomy_name])) {
            echo '<div class="notice notice-error is-dismissible"><p>This taxonomy already exists.</p></div>';
        } else {
            // Register the taxonomy
            $taxonomy_data = array(
                'label' => $taxonomy_label,
                'public' => $public,
                'hierarchical' => $hierarchical,
            );

            register_taxonomy($taxonomy_name, 'idea', $taxonomy_data);

            // Add the new taxonomy
            $custom_taxonomies[$taxonomy_name] = $taxonomy_data;

            // Update the option
            update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);

            echo '<div class="notice notice-success is-dismissible"><p>Taxonomy created successfully.</p></div>';
        }
    }

    // The form HTML
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
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

    // Retrieve and display taxonomies
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
    if (!empty($custom_taxonomies)) {
        echo '<h2>Existing Taxonomies</h2>';
        echo '<ul>';
        foreach ($custom_taxonomies as $taxonomy_name => $taxonomy_data) {
            $delete_link = wp_nonce_url(
                admin_url('admin.php?page=wp-road-map-taxonomies&action=delete&taxonomy=' . $taxonomy_name),
                'delete_taxonomy_' . $taxonomy_name
            );
            echo '<li>' . esc_html($taxonomy_name) . ' - <a href="' . esc_url($delete_link) . '">Delete</a></li>';
        }
        echo '</ul>';
    }
}
add_action('admin_menu', 'wp_road_map_add_admin_menu');




