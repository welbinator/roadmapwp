<?php
/**
 * Ajax handling for voting functionality.
 */

namespace RoadMapWP\Free\Ajax;
use RoadMapWP\Free\Admin\Functions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

/**
 * Handles voting functionality via AJAX.
 */
function handle_vote() {
	check_ajax_referer( 'wp-roadmap-vote-nonce', 'nonce' );

	$post_id = intval( $_POST['post_id'] );
	$user_id = get_current_user_id();

	if (!\RoadMapWP\Free\ClassVoting\VotingHandler::can_user_vote($user_id)) {
		wp_send_json_error(['message' => 'You are not allowed to vote.']);
		wp_die();
	}

	$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
	$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
	$user_key = $user_id ? 'user_' . $user_id : 'guest_' . md5($remote_addr . $http_user_agent);

	$current_votes = get_post_meta( $post_id, 'idea_votes', true ) ?: 0;
	$has_voted = get_post_meta( $post_id, 'voted_' . $user_key, true );

	if ( $has_voted ) {
		$new_votes = max( $current_votes - 1, 0 );
		delete_post_meta( $post_id, 'voted_' . $user_key );
	} else {
		$new_votes = $current_votes + 1;
		update_post_meta( $post_id, 'voted_' . $user_key, true );
	}

	update_post_meta( $post_id, 'idea_votes', $new_votes );
	wp_send_json_success([
		'new_count' => $new_votes,
		'voted'     => ! $has_voted,
	]);

	wp_die();
}

add_action( 'wp_ajax_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );
add_action( 'wp_ajax_nopriv_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );

/**
 * Handles AJAX requests for filtering ideas.
 */
function filter_ideas() {
	check_ajax_referer( 'wp-roadmap-idea-filter-nonce', 'nonce' );

	$filter_data = isset($_POST['filter_data']) ? (array) $_POST['filter_data'] : [];
	$tax_query   = [];

	$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', [] );
	$display_taxonomies = array_merge( [ 'idea-tag' ], array_keys( $custom_taxonomies ) );

	foreach ($filter_data as $taxonomy => $data) {
		$taxonomy = sanitize_key($taxonomy);
		if (!taxonomy_exists($taxonomy)) {
			continue;
		}
		if (!empty($data['terms']) && is_array($data['terms'])) {
			$sanitized_terms = array_map('sanitize_text_field', $data['terms']);
			$operator = isset($data['matchType']) && $data['matchType'] === 'all' ? 'AND' : 'IN';

			$tax_query[] = [
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $sanitized_terms,
				'operator' => $operator,
			];
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$args = [
		'post_type'      => 'idea',
		'posts_per_page' => -1,
		'tax_query'      => $tax_query,
	];

	$query = new \WP_Query( $args );

	if ( $query->have_posts() ) {
		echo '<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">';
		while ( $query->have_posts() ) {
			$query->the_post();
			$idea_id = get_the_ID();
			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );

			echo '<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden" data-v0-t="card">';
			echo '<div class="p-6">';
			echo '<h2 class="text-2xl font-bold"><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h2>';
			echo '<p class="text-gray-500 mt-2 text-sm">Submitted on: ' . esc_html( get_the_date() ) . '</p>';

			$terms = wp_get_post_terms( $idea_id, $display_taxonomies );
			if ($terms) {
				echo '<div class="flex flex-wrap space-x-2 mt-2 idea-tags">';
				foreach ($terms as $term) {
					$term_link = get_term_link( $term );
					if ( ! is_wp_error( $term_link ) ) {
						echo '<a href="' . esc_url( $term_link ) . '" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm text-white">' . esc_html( $term->name ) . '</a>';
					}
				}
				echo '</div>';
			}

			echo '<p class="text-gray-700 mt-4 break-all">' . esc_html( wp_trim_words( get_the_excerpt(), 20 ) ) . ' <a class="text-blue-500 hover:underline" href="' . esc_url( get_permalink() ) . '">read more...</a></p>';
			echo '</div>';

			\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button( $idea_id, $vote_count );
			echo '</div>';
		}
		echo '</div>';
	} else {
		echo '<p>No ideas found.</p>';
	}

	wp_reset_postdata();
	wp_die();
}

add_action( 'wp_ajax_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );
add_action( 'wp_ajax_nopriv_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );

/**
 * Loads ideas for a given status via AJAX.
 */
function load_ideas_for_status() {
	check_ajax_referer( 'roadmap_nonce', 'nonce' );

	$status = isset( $_POST['idea-status'] ) ? sanitize_text_field( $_POST['idea-status'] ) : '';
	$tax_query = [
		'relation' => 'AND',
		[
			'taxonomy' => 'idea-status',
			'field'    => 'slug',
			'terms'    => $status,
		],
	];

	$args = [
		'post_type'      => 'idea',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'tax_query'      => $tax_query,
	];

	$query = new \WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$idea_id = get_the_ID();
			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
			$idea_class = Functions\get_idea_class_with_votes($idea_id);

			echo '<div class="wp-roadmap-idea rounded-lg border bg-card text-card-foreground shadow-sm ' . esc_attr($idea_class) . '" data-v0-t="card">';
			echo '<div class="flex flex-col space-y-1.5 p-6">';
			echo '<h3 class="text-2xl font-semibold leading-none tracking-tight"><a href="' . esc_url( get_permalink( $idea_id ) ) . '">' . esc_html( get_the_title() ) . '</a></h3>';
			echo '</div>';
			echo '<div class="p-6">';
			echo '<p class="text-gray-700 mt-4 break-all">' . esc_html( wp_trim_words( get_the_excerpt(), 20 ) ) . ' <a class="text-blue-500 hover:underline" href="' . esc_url( get_permalink() ) . '">read more...</a></p>';
			echo '</div>';

			\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
			echo '</div>';
		}
	} else {
		echo '<p>No ideas found for this status.</p>';
	}

	wp_reset_postdata();

	$html = ob_get_clean();
	wp_send_json_success( [ 'html' => $html ] );
}

add_action( 'wp_ajax_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );
add_action( 'wp_ajax_nopriv_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );
