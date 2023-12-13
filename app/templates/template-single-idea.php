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
                // Always include 'idea-tag' taxonomy
                $taxonomies = array('idea-tag');

                // Include custom taxonomies only if Pro version is active
                if (function_exists('is_wp_roadmap_pro_active') && is_wp_roadmap_pro_active()) {
                    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
                    $taxonomies = array_merge($taxonomies, array_keys($custom_taxonomies));
                }

                // Exclude 'status' taxonomy
                $exclude_taxonomies = array('status');
                $taxonomies = array_diff($taxonomies, $exclude_taxonomies);

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
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
        endwhile; // End of the loop.
        ?>
    </div>
</main><!-- #main -->

<?php 
if (is_active_sidebar('your-sidebar-id')) {
    get_sidebar();
} 
get_footer(); 
?>
