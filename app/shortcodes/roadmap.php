<?php
/**
 * Shortcode to display the roadmap.
 *
 * @return string The HTML output for displaying the roadmap.
 */

 namespace RoadMapWP\Free\Shortcodes\Roadmap;

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

function roadmap_shortcode( $atts ) {
	// Flag to indicate the roadmap shortcode is loaded
	update_option( 'wp_roadmap_roadmap_shortcode_loaded', true );

	// Retrieve dynamic status terms
	$dynamic_status_terms = get_terms(
		array(
			'taxonomy'   => 'idea-status',
			'hide_empty' => false,
		)
	);
	$dynamic_statuses     = array_map(
		function ( $term ) {
			return $term->name;
		},
		$dynamic_status_terms
	);

	// Parse the shortcode attributes
	$atts = shortcode_atts(
		array(
			'idea-status' => implode( ',', $dynamic_statuses ), // Default to all dynamic statuses
		),
		$atts,
		'roadmap'
	);

	$statuses = ! empty( $atts['idea-status'] ) ? array_map( 'trim', explode( ',', $atts['idea-status'] ) ) : $dynamic_statuses;

	$num_statuses  = count( $statuses );
	$md_cols_class = 'md:grid-cols-' . ( $num_statuses > 3 ? 3 : $num_statuses ); // Set to number of statuses, but max out at 4
	$lg_cols_class = 'lg:grid-cols-' . ( $num_statuses > 4 ? 4 : $num_statuses );
	$xl_cols_class = 'xl:grid-cols-' . $num_statuses;
	ob_start(); // Start output buffering

	// Always include 'idea-tag' taxonomy
	$taxonomies = array( 'idea-tag' );

	// Include custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );
	$taxonomies        = array_merge( $taxonomies, array_keys( $custom_taxonomies ) );

	// Exclude 'status' taxonomy
	$exclude_taxonomies = array( 'idea-status' );
	$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );
	?>
	<div class="roadmap_wrapper container mx-auto">
	<div class="roadmap-columns grid gap-4 <?php echo htmlspecialchars($md_cols_class, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($lg_cols_class, ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($xl_cols_class, ENT_QUOTES, 'UTF-8'); ?>">
			<?php
			foreach ( $statuses as $status ) {
				$args  = array(
					'post_type'      => 'idea',
					'posts_per_page' => -1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'idea-status',
							'field'    => 'name',
							'terms'    => $status,
						),
					),
				);
				$query = new \WP_Query( $args );
				?>
				<div class="roadmap-column">
				
				<h3 style="text-align:center;">
					<?php 
						/* translators: %s: status name */
						echo sprintf( esc_html__( '%s', 'wp-roadmap-pro' ), esc_html( $status ) ); 
					?>
				</h3>
					<?php
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) :
							$query->the_post();
							$idea_id    = get_the_ID();
							$vote_count = get_post_meta( $idea_id, 'idea_votes', true ) ?: '0';
							?>
							<div class="border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden m-2 wp-roadmap-idea">
								<div class="p-6">
								<h4 class="idea-title"><a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a></h4>									<p class="text-gray-500 mt-2 mb-0 text-sm"><?php echo esc_html( get_the_date() ); ?></p>
									<div class="flex flex-wrap space-x-2 mt-2 idea-tags">
									<?php
									$terms = wp_get_post_terms( $idea_id, $taxonomies );
									foreach ( $terms as $term ) :
										$term_link = get_term_link( $term );
										if ( ! is_wp_error( $term_link ) ) :
											?>
											<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm"><?php echo esc_html( $term->name ); ?></a>
											<?php
										endif;
									endforeach;
									?>
									</div>
									<p class="idea-excerpt"><?php the_excerpt(); ?></p>
									<div class="flex items-center justify-start mt-6 gap-6">
										<a class="text-blue-500 hover:underline" href="<?php the_permalink(); ?>" rel="ugc">Read More</a>
										<div class="flex items-center idea-vote-box" data-idea-id="<?php echo esc_attr( $idea_id ) ?>">
											<button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg idea-vote-button">
												<svg
												xmlns="http://www.w3.org/2000/svg"
												width="24"
												height="24"
												viewBox="0 0 24 24"
												fill="none"
												stroke="currentColor"
												stroke-width="2"
												stroke-linecap="round"
												stroke-linejoin="round"
												class="w-5 h-5 mr-1"
												>
													<path d="M7 10v12"></path>
													<path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"></path>
												</svg>
												<div class="idea-vote-count"><?php echo $vote_count; ?></div>
											</button>
										</div>
									</div>
								</div>
								
							</div>
							<?php
						endwhile;
					} else {
						echo '<p>No ideas found for ' . esc_html( $status ) . '.</p>';
					}
					wp_reset_postdata();
					?>
				</div> <!-- Close column -->
				<?php
			}
			?>
		</div> <!-- Close grid -->
	</div>
	<?php
	return ob_get_clean(); // Return the buffered output
}
add_shortcode( 'roadmap', __NAMESPACE__ . '\\roadmap_shortcode' );
