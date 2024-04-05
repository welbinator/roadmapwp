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

	// Check if the user is allowed to vote
    if (!\RoadMapWP\Free\ClassVoting\VotingHandler::can_user_vote($user_id)) {
        wp_send_json_error(['message' => 'You are not allowed to vote.']);
        wp_die();
    }

	// Generate a unique key for non-logged-in user
	$remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
	$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
	$user_key = $user_id ? 'user_' . $user_id : 'guest_' . md5($remote_addr . $http_user_agent);


	// Retrieve the current vote count
	$current_votes = get_post_meta( $post_id, 'idea_votes', true ) ?: 0;

	// Check if this user or guest has already voted
	$has_voted = get_post_meta( $post_id, 'voted_' . $user_key, true );

	if ( $has_voted ) {
		// User or guest has voted, remove their vote
		$new_votes = max( $current_votes - 1, 0 );
		delete_post_meta( $post_id, 'voted_' . $user_key );
	} else {
		// User or guest hasn't voted, add their vote
		$new_votes = $current_votes + 1;
		update_post_meta( $post_id, 'voted_' . $user_key, true );
	}

	// Update the post meta with the new vote count
	update_post_meta( $post_id, 'idea_votes', $new_votes );

	wp_send_json_success(
		array(
			'new_count' => $new_votes,
			'voted'     => ! $has_voted,
		)
	);

	wp_die();
}

add_action( 'wp_ajax_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );
add_action( 'wp_ajax_nopriv_wp_roadmap_handle_vote', __NAMESPACE__ . '\\handle_vote' );

/**
 * Handle AJAX requests for ideas filter.
 */
function filter_ideas() {
	check_ajax_referer( 'wp-roadmap-idea-filter-nonce', 'nonce' );

	$filter_data = isset($_POST['filter_data']) ? (array) $_POST['filter_data'] : array();
	$tax_query   = array();

	$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$display_taxonomies = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );

	foreach ($filter_data as $taxonomy => $data) {
		// Sanitize taxonomy to ensure it's a valid taxonomy name
		$taxonomy = sanitize_key($taxonomy);
		if (!taxonomy_exists($taxonomy)) {
			continue; // Skip this iteration if the taxonomy is not valid
		}
	
		// Validate and sanitize 'terms' if they are set and is an array
		if (!empty($data['terms']) && is_array($data['terms'])) {
			$sanitized_terms = array_map('sanitize_text_field', $data['terms']);
			$operator = isset($data['matchType']) && $data['matchType'] === 'all' ? 'AND' : 'IN';
			
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $sanitized_terms,
				'operator' => $operator,
			);
		}
	}
	

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	$args = array(
		'post_type'      => 'idea',
		'posts_per_page' => -1,
		'tax_query'      => $tax_query,
	);

	$query = new \WP_Query( $args );

	if ( $query->have_posts() ) : ?>
		<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				$idea_id = get_the_ID();

				// Retrieve the correct vote count for each idea
				$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
				?>
	
				<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
					<div class="p-6">
						<h2 class="text-2xl font-bold"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
	
						<p class="text-gray-500 mt-2 text-sm"><?php esc_html_e( 'Submitted on:', 'roadmapwp-free' ); ?> <?php echo esc_html( get_the_date() ); ?></p>
						<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
							<?php
							$terms = wp_get_post_terms( $idea_id, $display_taxonomies );
							foreach ( $terms as $term ) :
								$term_link = get_term_link( $term );
								if ( ! is_wp_error( $term_link ) ) :
									?>
									<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm !no-underline text-white"><?php echo esc_html( $term->name ); ?></a>
									<?php
								endif;
							endforeach;
							?>
						</div>
	
						
						<p class="text-gray-700 mt-4 break-all">
							<?php
								$trimmed_excerpt = wp_trim_words( get_the_excerpt(), 20 );
								echo esc_html( $trimmed_excerpt ) . ' <a class="text-blue-500 hover:underline" href="' . esc_url( get_permalink() ) . '" rel="ugc">read more...</a>';
							?>
						</p>

	
						<div class="flex items-center justify-between mt-6">
							
						<?php
							\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
						?>
					</div>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'No ideas found.', 'roadmapwp-free' ); ?></p>
		<?php
	endif;

	wp_reset_postdata();
	wp_die();
}


add_action( 'wp_ajax_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );
add_action( 'wp_ajax_nopriv_filter_ideas', __NAMESPACE__ . '\\filter_ideas' );



// Handles the AJAX request for deleting a custom taxonomy
function handle_delete_custom_taxonomy() {
	check_ajax_referer( 'wp_roadmap_delete_taxonomy_nonce', 'nonce' );

	$taxonomy          = sanitize_text_field( $_POST['taxonomy'] );
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	if ( isset( $custom_taxonomies[ $taxonomy ] ) ) {
		unset( $custom_taxonomies[ $taxonomy ] );
		update_option( 'wp_roadmap_custom_taxonomies', $custom_taxonomies );
		wp_send_json_success();
	} else {
		wp_send_json_error( array( 'message' => __( 'Taxonomy not found.', 'roadmapwp-free' ) ) );
	}
}
// add_action( 'wp_ajax_delete_custom_taxonomy', __NAMESPACE__ . '\\handle_delete_custom_taxonomy' );

// Handles the AJAX request for deleting selected terms
function handle_delete_selected_terms() {
	check_ajax_referer( 'wp_roadmap_delete_terms_nonce', 'nonce' );

	$taxonomy = sanitize_text_field( $_POST['taxonomy'] );
	$terms    = array_map( 'intval', (array) $_POST['terms'] );

	foreach ( $terms as $term_id ) {
		wp_delete_term( $term_id, $taxonomy );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_delete_selected_terms', __NAMESPACE__ . '\\handle_delete_selected_terms' );

/**
 * Loads ideas for a given status via AJAX.
 */
function load_ideas_for_status() {

	check_ajax_referer( 'roadmap_nonce', 'nonce' );

	$status                  = isset( $_POST['status'] ) ? sanitize_text_field( $_POST['status'] ) : '';
	$selected_taxonomiesSlugs = isset( $_POST['selectedTaxonomies'] ) ? explode( ',', sanitize_text_field( $_POST['selectedTaxonomies'] ) ) : array();

	// Initialize the tax query with the status term
	$tax_query = array(
		'relation' => 'AND',
		array(
			'taxonomy' => 'idea-status',
			'field'    => 'slug',
			'terms'    => $status,
		),
	);

	$taxonomy_queries        = array();
	$empty_taxonomy_selected = false;

	$args = array(
		'post_type'      => 'idea',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'tax_query'      => $tax_query,
	);

	$query = new \WP_Query( $args );

	ob_start();

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			$idea_id = get_the_ID();
			// Retrieve all taxonomies associated with the 'idea' post type, excluding 'idea-status'
			$idea_taxonomies     = get_object_taxonomies( 'idea', 'names' );
			$excluded_taxonomies = array( 'idea-status' ); // Add more taxonomy names to exclude if needed
			$included_taxonomies = array_diff( $idea_taxonomies, $excluded_taxonomies );

			$idea_class = Functions\get_idea_class_with_votes($idea_id);

			// Fetch terms for each included taxonomy
			$tags = array();
			foreach ( $included_taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $idea_id, $taxonomy, array( 'fields' => 'all' ) );
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					$tags[ $taxonomy ] = $terms;
				}
			}
			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
		

			?>

			<div class="wp-roadmap-idea rounded-lg border bg-card text-card-foreground shadow-sm <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
				<div class="flex flex-col space-y-1.5 p-6">
					<h3 class="text-2xl font-semibold leading-none tracking-tight">
						<a href="<?php echo esc_url( get_permalink( $idea_id ) ); ?>"><?php echo esc_html( get_the_title() ); ?></a>
					</h3>

					<?php if ( ! empty( $tags ) ) : ?>
						<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
							<?php foreach ( $tags as $tag_name => $tag_terms ) : ?>
								<?php foreach ( $tag_terms as $tag_term ) : ?>
									<?php $tag_link = get_term_link( $tag_term, $tag_name ); ?>
									<?php if ( ! is_wp_error( $tag_link ) ) : ?>
										<a href="<?php echo esc_url( $tag_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm !no-underline text-white">
											<?php echo esc_html( $tag_term->name ); ?>
										</a>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>

				<div class="p-6">
					<p class="text-gray-700 mt-4 break-all">
						<?php
							echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ) . ' <a class="text-blue-500 hover:underline" href="' . esc_url( get_permalink() ) . '" rel="ugc">read more...</a>';
						?>
					</p>
				</div>

				<?php
					\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
				?>
			</div>

			<?php
		}
	} else {
		echo '<p>No ideas found for this status.</p>';
	}

	wp_reset_postdata();

	$html = ob_get_clean();
	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );
add_action( 'wp_ajax_nopriv_load_ideas_for_status', __NAMESPACE__ . '\\load_ideas_for_status' );


