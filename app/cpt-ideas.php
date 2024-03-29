<?php

namespace RoadMapWP\Free\CPT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

/**
 * Register the custom post type.
 */
function rmwp_register_post_type() {
	$options = get_option( 'wp_roadmap_settings' );

	$supports = array( 'title', 'editor', 'author' ); // include 'comments' support

	$labels = array(
		'name'               => _x( 'Ideas', 'post type general name', 'roadmapwp-free' ),
		'singular_name'      => _x( 'Idea', 'post type singular name', 'roadmapwp-free' ),
		'menu_name'          => _x( 'Ideas', 'admin menu', 'roadmapwp-free' ),
		'name_admin_bar'     => _x( 'Idea', 'add new on admin bar', 'roadmapwp-free' ),
		'add_new'            => _x( 'Add New', 'idea', 'roadmapwp-free' ),
		'add_new_item'       => __( 'Add New Idea', 'roadmapwp-free' ),
		'new_item'           => __( 'New Idea', 'roadmapwp-free' ),
		'edit_item'          => __( 'Edit Idea', 'roadmapwp-free' ),
		'view_item'          => __( 'View Idea', 'roadmapwp-free' ),
		'all_items'          => __( 'All Ideas', 'roadmapwp-free' ),
		'search_items'       => __( 'Search Ideas', 'roadmapwp-free' ),
		'parent_item_colon'  => __( 'Parent Ideas:', 'roadmapwp-free' ),
		'not_found'          => __( 'No ideas found.', 'roadmapwp-free' ),
		'not_found_in_trash' => __( 'No ideas found in Trash.', 'roadmapwp-free' ),
	);

	// Fetch all taxonomies associated with 'idea' post type.
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_keys( $custom_taxonomies );

	// Add default taxonomies if they aren't already included.
	if ( ! in_array( 'idea-status', $taxonomies ) ) {
		$taxonomies[] = 'idea-status';
	}
	if ( ! in_array( 'tag', $taxonomies ) ) {
		$taxonomies[] = 'tag';
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
		'supports'           => array( 'title', 'editor', 'author', 'comments' ),
		'show_in_rest'       => true,
	);

	register_post_type( 'idea', $args );
}

add_action( 'init', __NAMESPACE__ . '\\rmwp_register_post_type' );

/**
 * Register default taxonomies.
 */
function register_default_taxonomies() {
	// Define default taxonomies with their properties
	$default_taxonomies = array(
		'idea-status'   => array(
			'singular' => __( 'Status', 'roadmapwp-free' ), // Translatable
			'plural'   => __( 'Status', 'roadmapwp-free' ),   // Translatable
			'public'   => true,  // Make status taxonomy private
		),
		'idea-tag' => array(
			'singular' => __( 'Tag', 'roadmapwp-free' ),    // Translatable
			'plural'   => __( 'Tags', 'roadmapwp-free' ),     // Translatable
			'public'   => true,  // Keep tag taxonomy public
		),
	);

	foreach ( $default_taxonomies as $slug => $properties ) {
		if ( ! taxonomy_exists( $slug ) ) {
			register_taxonomy(
				$slug,
				'idea',
				array(
					'label'             => $properties['plural'],
					'labels'            => array(
						'name'          => $properties['plural'],
						'singular_name' => $properties['singular'],
						// ... other labels ...
					),
					'public'            => $properties['public'],
					'hierarchical'      => ( $slug == 'idea-status' ),
					'show_ui'           => true,
					'show_in_rest'      => true,
					'show_admin_column' => true,
				)
			);
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_default_taxonomies' );

/**
 * Register custom taxonomies.
 */
function register_custom_taxonomies() {
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	foreach ( $custom_taxonomies as $taxonomy_slug => $taxonomy_data ) {
		if ( ! taxonomy_exists( $taxonomy_slug ) ) {
			register_taxonomy( $taxonomy_slug, 'idea', $taxonomy_data );
		}
	}
}
add_action( 'init', __NAMESPACE__ . '\\register_custom_taxonomies', 0 );
