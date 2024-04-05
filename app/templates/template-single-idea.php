<?php
/**
 * The Template for displaying all single posts of the 'idea' CPT.
 */

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

$options                = get_option( 'wp_roadmap_settings' );
$allow_comments         = isset( $options['allow_comments'] ) ? $options['allow_comments'] : false;

get_header(); ?>

<main id="primary" class="flex-grow px-4 py-8 site-main">

	<div class="roadmap-wrapper container mx-auto what">
		<article class="flex flex-col space-y-8">
			<?php
			while ( have_posts() ) :
				the_post();
				$idea_id    = get_the_ID();
				$vote_count = get_post_meta( $idea_id, 'idea_votes', true ) ?: '0';
				?>
				<div id="post-<?php the_ID(); ?>" <?php post_class( 'p-6 flex flex-col space-y-2' ); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title text-4xl font-bold">', '</h1>' ); ?>
						<p class="publish-date text-gray-600"><?php echo esc_html( get_the_date() ); ?></p> <!-- Published date -->
					</header><!-- .entry-header -->
						<?php
						// Always include 'idea-tag' taxonomy
						$taxonomies = array( 'idea-tag' );

						// Exclude 'idea-status' taxonomy
						$exclude_taxonomies = array( 'idea-status' );
						$taxonomies         = array_diff( $taxonomies, $exclude_taxonomies );

						$terms = wp_get_post_terms( get_the_ID(), $taxonomies, array( 'exclude' => $exclude_taxonomies ) );
						if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
							echo '<div class="idea-tags flex space-x-2">';
							foreach ( $terms as $term ) {
								$term_link = get_term_link( $term );
								if ( ! is_wp_error( $term_link ) ) {
									?>
									
									<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 px-3 py-1 rounded-full text-sm !no-underline text-white"><?php echo esc_html( $term->name ); ?></a>
									
									<?php
								}
							}
							echo '</div>';
						}
						?>
						
					<div class="p-6 prose prose-lg mt-4">
						<?php
							the_content();
							wp_link_pages(
								array(
									'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'roadmapwp-free' ),
									'after'  => '</div>',
								)
							);
						?>
					</div>
							
					<?php
						\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
					?>
				</div>
						

						<footer class="entry-footer">
							<?php
							edit_post_link(
								sprintf(
									wp_kses(
										/* translators: %s: Name of current post. Only visible to screen readers */
										__( 'Edit <span class="screen-reader-text">%s</span>', 'roadmapwp-free' ),
										array(
											'span' => array(
												'class' => array(),
											),
										)
									),
									wp_kses_post( get_the_title() )
								),
								'<span class="edit-link">',
								'</span>'
							);
							?>
						</footer><!-- .entry-footer -->
					


						<?php
					if ( ( $allow_comments ) && ( comments_open() || get_comments_number() ) ) :
						comments_template();
						endif;
						endwhile;
			?>
		</article>

	</div>
</main><!-- #main -->

<?php

get_footer();
?>