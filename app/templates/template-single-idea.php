<?php
/**
 * The Template for displaying all single posts of the 'idea' CPT.
 */

get_header(); ?>

<main id="primary" class="flex-grow px-4 py-8 site-main">
    <div class="flex flex-col space-y-8">
        <?php
        while ( have_posts() ) :
            the_post();

            ?>
            <article id="post-<?php the_ID(); ?>" <?php post_class('p-6 flex flex-col space-y-2'); ?>>
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title text-4xl font-bold">', '</h1>' ); ?>
                    <p class="publish-date text-gray-600"><?php echo get_the_date(); ?></p> <!-- Published date -->
                </header><!-- .entry-header -->

                <?php
                // Display terms from Tags taxonomy and any custom taxonomies
                $exclude_taxonomies = ['status']; // Exclude 'status' taxonomy
                $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', []);
                $taxonomies = array_keys($custom_taxonomies);
                $taxonomies[] = 'idea-tag'; // Add the 'idea-tag' taxonomy
                
                $terms = wp_get_post_terms(get_the_ID(), $taxonomies, ['exclude' => $exclude_taxonomies]);
                if (!empty($terms) && !is_wp_error($terms)) {
                    echo '<div class="idea-terms flex flex-wrap mt-2">';
                    foreach ($terms as $term) {
                        $term_link = get_term_link($term);
                        if (!is_wp_error($term_link)) {
                            echo '<a href="' . esc_url($term_link) . '" class="term-link bg-blue-500 text-white px-3 py-1 rounded-full mr-2 mb-2">' . esc_html($term->name) . '</a>';
                        }
                    }
                    echo '</div>';
                }
                ?>
                
                <div class="entry-content">
                    <div class="flex items-center gap-4 mt-4 idea-vote-box" data-idea-id="<?php echo get_the_ID(); ?>">
                        <button aria-label="Vote Post" class="inline-flex items-center justify-center rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2 idea-vote-button">
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
                        Vote
                        </button>
                        <div class="idea-vote-count">
                            <?php echo get_post_meta(get_the_ID(), 'idea_votes', true) ?: '0'; ?>
                        </div>
                    </div>

                    <?php
                    the_content();

                    wp_link_pages(
                        array(
                            'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'text-domain' ),
                            'after'  => '</div>',
                        )
                    );
                    ?>
                </div><!-- .entry-content -->

                <footer class="entry-footer">
                    <?php
                    // Edit post link
                    edit_post_link(
                        sprintf(
                            wp_kses(
                                /* translators: %s: Name of current post. Only visible to screen readers */
                                __( 'Edit <span class="screen-reader-text">%s</span>', 'text-domain' ),
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
            </article><!-- #post-<?php the_ID(); ?> -->
            <?php

            // If comments are open or there is at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;

        endwhile; // End of the loop.
        ?>
    </div>
</main><!-- #main -->

<?php
get_sidebar();
get_footer();
