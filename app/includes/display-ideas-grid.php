<?php use RoadMapWP\Free\Admin\Functions; ?>

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
				<a href="<?php echo esc_url( $term_link ); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm !no-underline"><?php echo esc_html( $term->name ); ?></a>
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



	<div class="flex items-center justify-start mt-6 gap-6">
		
	<?php
		\RoadMapWP\Free\ClassVoting\VotingHandler::render_vote_button($idea_id, $vote_count);
	?>
	</div>
</div>