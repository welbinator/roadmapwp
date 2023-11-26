<?php

require_once plugin_dir_path( __FILE__ ) . 'app/cpt-ideas.php';

// Register custom taxonomies
function wp_road_map_register_custom_taxonomies() {
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());

    foreach ($custom_taxonomies as $taxonomy_name => $taxonomy_data) {
        if (!taxonomy_exists($taxonomy_name)) {
            register_taxonomy($taxonomy_name, 'idea', $taxonomy_data);
        }
    }
}
add_action('init', 'wp_road_map_register_custom_taxonomies', 0);
