<?php

function wp_road_map_register_custom_taxonomies() {
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());

    foreach ($custom_taxonomies as $taxonomy_name => $taxonomy_data) {
        register_taxonomy(
            $taxonomy_name,
            'idea',
            array(
                'label' => $taxonomy_data['label'],
                'public' => $taxonomy_data['public'],
                'hierarchical' => $taxonomy_data['hierarchical'],
            )
        );
    }
}
add_action('init', 'wp_road_map_register_custom_taxonomies');
