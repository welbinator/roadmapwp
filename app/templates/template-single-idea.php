<?php
/**
 * The Template for displaying all single posts of the 'idea' CPT.
 */

 // Retrieve color settings
 $options = get_option('wp_roadmap_settings');
 $vote_button_bg_color = isset($options['vote_button_bg_color']) ? $options['vote_button_bg_color'] : '#ff0000';
 $vote_button_text_color = isset($options['vote_button_text_color']) ? $options['vote_button_text_color'] : '#000000';
 $filter_tags_bg_color = isset($options['filter_tags_bg_color']) ? $options['filter_tags_bg_color'] : '#ff0000';
 $filter_tags_text_color = isset($options['filter_tags_text_color']) ? $options['filter_tags_text_color'] : '#000000';
 $filters_bg_color = isset($options['filters_bg_color']) ? $options['filters_bg_color'] : '#f5f5f5';
 
get_header(); ?>

<main id="primary" class="flex-grow px-4 py-8 site-main">
    <article class="flex flex-col space-y-8">
        <?php
        while ( have_posts() ) :
            the_post();
            $idea_id = get_the_ID();
            $vote_count = get_post_meta($idea_id, 'idea_votes', true) ?: '0';
            ?>
            <div id="post-<?php the_ID(); ?>" <?php post_class('p-6 flex flex-col space-y-2'); ?>>
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
                    echo '<div class="idea-terms flex space-x-2">';
                    foreach ($terms as $term) {
                        $term_link = get_term_link($term);
                        if (!is_wp_error($term_link)) { ?>
                            
                            <a href="<?php echo esc_url($term_link); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm" style="background-color: <?php echo esc_attr($filter_tags_bg_color); ?>;color: <?php echo esc_attr($filter_tags_text_color); ?>;"><?php echo esc_html($term->name); ?></a>
                            
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
                                'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'wp-roadmap' ),
                                'after'  => '</div>',
                            )
                        );
                        ?>
                    </div>
                    
                <div class="flex items-center gap-4 mt-4 idea-vote-box" data-idea-id="<?php echo get_the_ID(); ?>">
                    <button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg idea-vote-button" style="background-color: <?php echo esc_attr($vote_button_bg_color); ?>!important;background-image: none!important;color: <?php echo esc_attr($vote_button_text_color); ?>!important;">
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
                    <div class="text-gray-600 ml-2 idea-vote-count"><?php echo $vote_count; ?></div>
                </div>
                </div>
                

                <footer class="entry-footer">
                    <?php
                    edit_post_link(
                        sprintf(
                            wp_kses(
                                /* translators: %s: Name of current post. Only visible to screen readers */
                                __( 'Edit <span class="screen-reader-text">%s</span>', 'wp-roadmap' ),
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
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;
        endwhile; // End of the loop.
        ?>
    </article>
</main><!-- #main -->

<?php 

get_footer(); 
?>
