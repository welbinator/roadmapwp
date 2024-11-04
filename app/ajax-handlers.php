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

	$filter_data = isset($_POST['filter_data']) ? (array) $_POST['filter_data'] : array();
	$search_term = isset($_POST['search_term']) ? sanitize_text_field($_POST['search_term']) : '';
	$tax_query   = array();

	$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );
	
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
        's'              => $search_term,
		'post_status'    => 'publish',
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
                $idea_class = Functions\get_idea_class_with_votes($idea_id);
				
				?>
	
				<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
					<?php include plugin_dir_path( __FILE__ ) . 'includes/display-ideas-grid.php'; ?>
				</div>
			<?php endwhile; ?>
		</div>
	<?php else : ?>
		<p><?php esc_html_e( 'No ideas found.', 'roadmapwp-pro' ); ?></p>
		<?php
	endif;

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
			// Retrieve all taxonomies associated with the 'idea' post type, excluding 'idea-status'
			$idea_taxonomies     = get_object_taxonomies( 'idea', 'names' );
			
			$custom_taxonomies  = get_option( 'wp_roadmap_custom_taxonomies', array() );
			$taxonomies = array_merge( array( 'idea-tag' ), array_keys( $custom_taxonomies ) );

			$idea_class = Functions\get_idea_class_with_votes($idea_id);

			$vote_count = intval( get_post_meta( $idea_id, 'idea_votes', true ) );
			?>
			<div class="wut wp-roadmap-idea rounded-lg border bg-card text-card-foreground shadow-lg <?php echo esc_attr($idea_class); ?>" data-v0-t="card">
				<?php include plugin_dir_path(__FILE__) . 'includes/display-ideas-grid.php'; ?>
				
			</div>

			<?php
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

// delete taxonomy terms
add_action( 'wp_ajax_delete_selected_terms', __NAMESPACE__ . '\\delete_selected_terms_callback' );

function delete_selected_terms_callback() {
	
    // Check if nonce is valid
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wp_roadmap_delete_terms_nonce' ) ) {
        wp_send_json_error( array( 'message' => 'Nonce verification failed.' ) );
    }

    // Check for taxonomy and terms
    if ( empty( $_POST['taxonomy'] ) || empty( $_POST['terms'] ) ) {
        wp_send_json_error( array( 'message' => 'Invalid taxonomy or terms.' ) );
    }

    $taxonomy = sanitize_text_field( $_POST['taxonomy'] );
    $terms = array_map( 'intval', $_POST['terms'] ); // Sanitize term IDs

    // Loop through terms and delete them
    foreach ( $terms as $term_id ) {
        $result = wp_delete_term( $term_id, $taxonomy );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( array( 'message' => $result->get_error_message() ) );
        }
    }

    // If successful, return a success message
    wp_send_json_success( array( 'message' => 'Terms deleted successfully.' ) );
}
