<?php
/**
 * Shortcode to display the new idea submission form.
 *
 * @return string The HTML output for the new idea form.
 */
function wp_roadmap_new_idea_form_shortcode() {
	// Flag to indicate the new idea form shortcode is loaded
	update_option( 'wp_roadmap_new_idea_shortcode_loaded', true );

	$output = '';

	if ( isset( $_GET['new_idea_submitted'] ) && $_GET['new_idea_submitted'] == '1' ) {
		$output .= '<p>Thank you for your submission!</p>';
	}

	$hide_submit_idea_heading = apply_filters( 'wp_roadmap_hide_custom_idea_heading', false );
	$new_submit_idea_heading  = apply_filters( 'wp_roadmap_custom_idea_heading_text', 'Submit new Idea' );

	$output .= '<div class="roadmap_wrapper container mx-auto">';
	$output .= '<div class="new_idea_form__frontend">';
	if ( ! $hide_submit_idea_heading ) {
		$output .= '<h2>' . esc_html( $new_submit_idea_heading ) . '</h2>';
	}
	$output .= '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	$output .= '<ul class="flex-outer">';
	$output .= '<li class="new_idea_form_input"><label for="idea_title">Title:</label>';
	$output .= '<input type="text" name="idea_title" id="idea_title" required></li>';
	$output .= '<li class="new_idea_form_input"><label for="idea_description">Description:</label>';
	$output .= '<textarea name="idea_description" id="idea_description" required></textarea></li>';

	$taxonomies = get_object_taxonomies( 'idea', 'objects' );
	foreach ( $taxonomies as $taxonomy ) {
		if ( $taxonomy->name !== 'status' && ( $taxonomy->name === 'idea-tag' ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy->name,
					'hide_empty' => false,
				)
			);

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				$output .= '<li class="new_idea_form_input">';
				$output .= '<label>' . esc_html( $taxonomy->labels->singular_name ) . ':</label>';
				$output .= '<div class="taxonomy-term-labels">';

				foreach ( $terms as $term ) {
					$output .= '<label class="taxonomy-term-label">';
					$output .= '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->term_id ) . '"> ';
					$output .= esc_html( $term->name );
					$output .= '</label>';
				}

				$output .= '</div>';
				$output .= '</li>';
			}
		}
	}

	// Generate a nonce and add it as a hidden input field
	$nonce   = wp_create_nonce( 'wp_roadmap_new_idea' );
	$output .= '<input type="hidden" name="wp_roadmap_new_idea_nonce" value="' . esc_attr( $nonce ) . '">';
	$output .= '<li class="new_idea_form_input"><input type="submit" value="Submit Idea"></li>';
	$output .= '</ul>';
	$output .= '</form>';
	$output .= '</div>';
	$output .= '</div>';

	return $output;
}

add_shortcode( 'new_idea_form', 'wp_roadmap_new_idea_form_shortcode' );

/**
 * Function to handle the submission of the new idea form.
 */
function wp_roadmap_handle_new_idea_submission() {
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['idea_title'] ) && isset( $_POST['wp_roadmap_new_idea_nonce'] ) && wp_verify_nonce( $_POST['wp_roadmap_new_idea_nonce'], 'wp_roadmap_new_idea' ) ) {
		$title       = sanitize_text_field( $_POST['idea_title'] );
		$description = sanitize_textarea_field( $_POST['idea_description'] );

		// Get the default post status option from the settings
		// Fetch Pro plugin settings
		$options = get_option( 'wp_roadmap_settings', array() );
		// Retrieve the default status from Pro plugin settings
		$default_idea_status = isset( $options['default_idea_status'] ) ? $options['default_idea_status'] : 'pending';

		$idea_id = wp_insert_post(
			array(
				'post_title'   => $title,
				'post_content' => $description,
				'post_status'  => $default_idea_status, // Use the default post status
				'post_type'    => 'idea',
			)
		);

		if ( isset( $_POST['idea_taxonomies'] ) && is_array( $_POST['idea_taxonomies'] ) ) {
			foreach ( $_POST['idea_taxonomies'] as $tax_slug => $term_ids ) {
				$term_ids = array_map( 'intval', $term_ids );
				wp_set_object_terms( $idea_id, $term_ids, $tax_slug );
			}
		}

		$redirect_url = add_query_arg( 'new_idea_submitted', '1', esc_url_raw( $_SERVER['REQUEST_URI'] ) );
		wp_redirect( $redirect_url );
		exit;
	}
}
add_action( 'template_redirect', 'wp_roadmap_handle_new_idea_submission' );
