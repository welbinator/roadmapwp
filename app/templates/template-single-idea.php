<?php
/**
 * The Template for displaying all single posts of the 'idea' CPT.
 */

get_header(); ?>

<main id="primary" class="site-main">

    <?php
    while ( have_posts() ) :
        the_post();

        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
            </header><!-- .entry-header -->

            <div class="entry-content">
                <div class="idea-vote-box" data-idea-id="<?php echo get_the_ID(); ?>">
                    <button class="idea-vote-button">
                        <!-- <i class="fa fa-arrow-up"></i>  -->
                        ^
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

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
