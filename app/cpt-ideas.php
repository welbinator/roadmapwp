<?php

// Function to register the custom post type
function wp_road_map_register_post_type() {
    $labels = array(
        'name'               => _x( 'Ideas', 'post type general name', 'wp-road-map' ),
        'singular_name'      => _x( 'Idea', 'post type singular name', 'wp-road-map' ),
        'menu_name'          => _x( 'Ideas', 'admin menu', 'wp-road-map' ),
        'name_admin_bar'     => _x( 'Idea', 'add new on admin bar', 'wp-road-map' ),
        'add_new'            => _x( 'Add New', 'idea', 'wp-road-map' ),
        'add_new_item'       => __( 'Add New Idea', 'wp-road-map' ),
        'new_item'           => __( 'New Idea', 'wp-road-map' ),
        'edit_item'          => __( 'Edit Idea', 'wp-road-map' ),
        'view_item'          => __( 'View Idea', 'wp-road-map' ),
        'all_items'          => __( 'All Ideas', 'wp-road-map' ),
        'search_items'       => __( 'Search Ideas', 'wp-road-map' ),
        'parent_item_colon'  => __( 'Parent Ideas:', 'wp-road-map' ),
        'not_found'          => __( 'No ideas found.', 'wp-road-map' ),
        'not_found_in_trash' => __( 'No ideas found in Trash.', 'wp-road-map' )
    );

    // Fetch all taxonomies associated with 'idea' post type
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
    $taxonomies = array_keys($custom_taxonomies);

    // Add default taxonomies if they aren't already included
    if (!in_array('status', $taxonomies)) {
        $taxonomies[] = 'status';  // Default taxonomy 'status'
    }
    if (!in_array('tag', $taxonomies)) {
        $taxonomies[] = 'tag';  // Default taxonomy 'tag'
    }

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'idea' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'author', 'comments' ),
        'taxonomies'         => $taxonomies 
    );

    register_post_type('idea', $args);
}

add_action('init', 'wp_road_map_register_post_type');


// default taxonomies
function wp_road_map_register_default_taxonomies() {
    // Define default taxonomies with their properties
    $default_taxonomies = array(
        'status' => array(
            'singular' => 'Status',
            'plural' => 'Status',
            'public' => true  // Make status taxonomy private
        ),
        'idea-tag' => array(
            'singular' => 'Tag',
            'plural' => 'Tags',
            'public' => true  // Keep tag taxonomy public
        )
    );

    foreach ($default_taxonomies as $slug => $properties) {
        if (!taxonomy_exists($slug)) {
            register_taxonomy(
                $slug,
                'idea',
                array(
                    'label' => $properties['plural'],
                    'labels' => array(
                        'name' => $properties['plural'],
                        'singular_name' => $properties['singular'],
                        // ... other labels ...
                    ),
                    'public' => $properties['public'],
                    'hierarchical' => ($slug == 'status'),
                    'show_ui' => true,
                    'show_in_rest' => true,
                    'show_admin_column' => true,
                )
            );
        }
    }
}
add_action('init', 'wp_road_map_register_default_taxonomies');

//automatically assign Status of New Idea to new idea posts
// Hook into the save_post action
add_action('save_post_idea', 'wp_road_map_auto_assign_new_idea_status', 10, 3);

// Function to auto-assign "New Idea" status to new Idea posts
function wp_road_map_auto_assign_new_idea_status($post_id, $post, $update) {
    // If this is an update, not a new post, or if it's an autosave, don't do anything
    if ($update || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
        return;
    }

    // Check if the term exists
    $term = term_exists('New Idea', 'status');

    // If the term doesn't exist, add it
    if ($term === 0 || $term === null) {
        $term = wp_insert_term('New Idea', 'status');
    }

    // Check for errors
    if (is_wp_error($term)) {
        error_log('Error auto-assigning "New Idea" status: ' . $term->get_error_message());
        return;
    }

    // Assign "New Idea" status to this idea post using the term slug
    wp_set_object_terms($post_id, 'new-idea', 'status');
}

add_action('save_post_idea', 'wp_road_map_auto_assign_new_idea_status', 10, 3);




