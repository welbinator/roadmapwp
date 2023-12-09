<?php

// Function to register the custom post type
function wp_roadmap_register_post_type() {
    $options = get_option('wp_roadmap_settings');
     
    $supports = array('title', 'editor', 'author'); // include 'comments' support


    $labels = array(
        'name'               => _x( 'Ideas', 'post type general name', 'wp-roadmap' ),
        'singular_name'      => _x( 'Idea', 'post type singular name', 'wp-roadmap' ),
        'menu_name'          => _x( 'Ideas', 'admin menu', 'wp-roadmap' ),
        'name_admin_bar'     => _x( 'Idea', 'add new on admin bar', 'wp-roadmap' ),
        'add_new'            => _x( 'Add New', 'idea', 'wp-roadmap' ),
        'add_new_item'       => __( 'Add New Idea', 'wp-roadmap' ),
        'new_item'           => __( 'New Idea', 'wp-roadmap' ),
        'edit_item'          => __( 'Edit Idea', 'wp-roadmap' ),
        'view_item'          => __( 'View Idea', 'wp-roadmap' ),
        'all_items'          => __( 'All Ideas', 'wp-roadmap' ),
        'search_items'       => __( 'Search Ideas', 'wp-roadmap' ),
        'parent_item_colon'  => __( 'Parent Ideas:', 'wp-roadmap' ),
        'not_found'          => __( 'No ideas found.', 'wp-roadmap' ),
        'not_found_in_trash' => __( 'No ideas found in Trash.', 'wp-roadmap' )
    );

    // Fetch all taxonomies associated with 'idea' post type
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
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
        'taxonomies'         => $taxonomies,
        'supports'           => array( 'title', 'editor', 'author', 'comments' )
    );

    register_post_type('idea', $args);
}

add_action('init', 'wp_roadmap_register_post_type');


// default taxonomies
function wp_roadmap_register_default_taxonomies() {
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
add_action('init', 'wp_roadmap_register_default_taxonomies');

//automatically assign Status of New Idea to new idea posts
// Hook into the save_post action
add_action('save_post_idea', 'wp_roadmap_auto_assign_new_idea_status', 10, 3);

// Function to auto-assign "New Idea" status to new Idea posts
function wp_roadmap_auto_assign_new_idea_status($post_id, $post, $update) {
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

add_action('save_post_idea', 'wp_roadmap_auto_assign_new_idea_status', 10, 3);

// Register custom taxonomies
function wp_roadmap_register_custom_taxonomies() {
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());

    foreach ($custom_taxonomies as $taxonomy_slug => $taxonomy_data) {
        if (!taxonomy_exists($taxonomy_slug)) {
            register_taxonomy($taxonomy_slug, 'idea', $taxonomy_data);
        }
    }
}
add_action('init', 'wp_roadmap_register_custom_taxonomies', 0);