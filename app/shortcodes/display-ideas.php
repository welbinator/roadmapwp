<?php

/**
 * Shortcode to display ideas.
 *
 * @return string The HTML output for displaying ideas.
 */

 namespace RoadMapWP\Free\Shortcodes\DisplayIdeas;

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

function display_ideas_shortcode() {

	$user_id = get_current_user_id();
	$display_shortcode = true;
    $display_shortcode = apply_filters('roadmapwp_pro_display_ideas_shortcode', $display_shortcode, $user_id);

    if (!$display_shortcode) {
        return '';
    }
	
	// Flag to indicate the display ideas shortcode is loaded
	update_option( 'wp_roadmap_ideas_shortcode_loaded', true );

	ob_start(); // Start output buffering

	$output = '';

	// Always include 'idea-tag' taxonomy
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'idea-status' taxonomy
	$exclude_taxonomies = array( 'idea-status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );

	// Check if the pro version is installed and settings are enabled
	$hide_display_ideas_heading = apply_filters( 'wp_roadmap_hide_display_ideas_heading', false );
	$new_display_ideas_heading  = apply_filters( 'wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas' );
?>
	<div class="roadmap_wrapper container mx-auto">
	<div class="browse_ideas_frontend">
	<?php
	$output .= '<h2>' . esc_html( $new_display_ideas_heading ) . '</h2>';
	if ( ! $hide_display_ideas_heading ) {
		echo wp_kses_post( $output );
	}

			// Flag to check if there are any terms in the taxonomies
			$show_filters = false;

			foreach ( $taxonomies as $taxonomy_slug ) :
				$taxonomy = get_taxonomy( $taxonomy_slug );
				if ( $taxonomy && $taxonomy_slug != 'idea-status' ) :
					$terms = get_terms(
						array(
							'taxonomy'   => $taxonomy->name,
							'hide_empty' => false,
						)
					);
					if ( !empty($terms) ) {
						// Set flag to true if there are terms
						$show_filters = true;
					}
				endif;
			endforeach;

			// Conditionally render the rmwp__filters-wrapper div
			if ( $show_filters ) :
				?>
				<div class="rmwp__filters-wrapper">
					<h4>Filters:</h4>
					<div class="rmwp__filters-inner">
						<?php
						// Reiterate through taxonomies to build the filters UI
						foreach ( $taxonomies as $taxonomy_slug ) :
							$taxonomy = get_taxonomy( $taxonomy_slug );
							if ( $taxonomy && $taxonomy_slug != 'idea-status' ) :
								$terms = get_terms(
									array(
										'taxonomy'   => $taxonomy->name,
										'hide_empty' => false,
									)
								);
								?>
								<div class="rmwp__ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr( $taxonomy_slug ); ?>">
									<label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
									<div class="rmwp__taxonomy-term-labels">
										<?php
										foreach ( $terms as $term ) {
											echo '<label class="rmwp__taxonomy-term-label">';
											echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr( $taxonomy->name ) . '][]" value="' . esc_attr( $term->slug ) . '"> ';
											echo esc_html( $term->name );
											echo '</label>';
										}
										?>
									</div>
									<div class="rmwp__filter-match-type">
										<label><input type="radio" name="match_type_<?php echo esc_attr( $taxonomy->name ); ?>" value="any" checked> Any</label>
										<label><input type="radio" name="match_type_<?php echo esc_attr( $taxonomy->name ); ?>" value="all"> All</label>
									</div>
								</div>
								<?php
							endif;
						endforeach;
						?>
					</div>
				</div>
				<?php
			endif;
			?>

		</div>

		<div class="rmwp__ideas-list">

		<?php
		$args  = array(
			'post_type'      => 'idea',
			'posts_per_page' => -1, // Adjust as needed
		);
		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) :
			?>
			<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
				<?php
				while ( $query->have_posts() ) :
					$query->the_post();
					$idea_id    = get_the_ID();
					$vote_count = get_post_meta( $idea_id, 'idea_votes', true ) ?: '0';
					?>
		
					<div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden" data-v0-t="card">
						<div class="p-6">
							<h2 class="text-2xl font-bold"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
		
							<p class="text-gray-500 mt-2 text-sm">Submitted on: <?php echo esc_html( get_the_date() ); ?></p>
							<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
								<?php
								$terms = wp_get_post_terms( $idea_id, $taxonomies );
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
		
							
							<p class="text-gray-700 mt-4"><?php echo wp_kses_post(get_the_excerpt()); ?></p>

		
							<div class="flex items-center justify-between mt-6">
								<a class="text-blue-500 hover:underline" href="<?php echo esc_url( get_permalink() ); ?>" rel="ugc">Read More</a>
								<?php
									\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
								?>
							</div>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
	<?php else : ?>
		<p>No ideas found.</p>
		<?php
	endif;

	wp_reset_postdata();

	return ob_get_clean(); // Return the buffered output
}




add_shortcode( 'display_ideas', __NAMESPACE__ . '\\display_ideas_shortcode' );