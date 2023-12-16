<?php

/**
 * Shortcode to display the roadmap.
 *
 * @return string The HTML output for displaying the roadmap.
 */
function wp_roadmap_roadmap_shortcode() {
   // Flag to indicate the roadmap shortcode is loaded
    update_option('wp_roadmap_roadmap_shortcode_loaded', true);

    // Get custom taxonomies excluding 'status'
    $exclude_taxonomies = array('status');
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
    $taxonomies = array_merge(array('idea-tag'), array_keys($custom_taxonomies));
    $taxonomies = array_diff($taxonomies, $exclude_taxonomies); // Exclude 'status' taxonomy

    // Retrieve color settings
    $options = get_option('wp_roadmap_settings');
    $vote_button_bg_color = isset($options['vote_button_bg_color']) ? $options['vote_button_bg_color'] : '#ff0000';
    $vote_button_text_color = isset($options['vote_button_text_color']) ? $options['vote_button_text_color'] : '#000000';
    $filter_tags_bg_color = isset($options['filter_tags_bg_color']) ? $options['filter_tags_bg_color'] : '#ff0000';
    $filter_tags_text_color = isset($options['filter_tags_text_color']) ? $options['filter_tags_text_color'] : '#000000';
    $filters_bg_color = isset($options['filters_bg_color']) ? $options['filters_bg_color'] : '#f5f5f5';

    ob_start(); // Start output buffering
    ?>
    <div class="roadmap_wrapper container mx-auto">
    <div class="roadmap-grid">
        <?php
        $statuses = array('Up Next', 'On Roadmap');

        foreach ($statuses as $status) {
            $args = array(
                'post_type' => 'idea',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'status',
                        'field'    => 'name',
                        'terms'    => $status,
                    ),
                ),
            );
            $query = new WP_Query($args);
            ?>

            <div class="roadmap-column">
                <h1><?php echo esc_html($status); ?></h1>
                <?php
                if ($query->have_posts()) {
                    while ($query->have_posts()) : $query->the_post();
                        ?>
                        <div class="border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden m-2 wp-roadmap-idea">
                            <div class="p-6">
                                <h4 class="idea-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>
                                <p class="text-gray-500 mt-2 mb-0 text-sm"><?php echo get_the_date(); ?></p>
                                <div class="flex flex-wrap space-x-2 mt-2 idea-tags">
                                    <?php
                                    $terms = wp_get_post_terms(get_the_ID(), $taxonomies);
                                    foreach ($terms as $term) {
                                        $term_link = get_term_link($term);
                                        if (!is_wp_error($term_link)) { ?>
                                            <a href="<?php echo esc_url($term_link); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm" style="background-color: <?php echo esc_attr($filter_tags_bg_color); ?>;color: <?php echo esc_attr($filter_tags_text_color); ?>;"><?php echo esc_html($term->name); ?></a>
                                        <?php }
                                    }
                                    ?>
                                </div>
                                <p class="idea-excerpt"><?php the_excerpt(); ?></p>
                                <a class="text-blue-500 hover:underline" href="<?php the_permalink(); ?>" rel="ugc">Read More</a>
                            </div>
                        </div>
                        
                        <?php
                    endwhile;
                } else {
                    echo '<p>No ideas found for ' . esc_html($status) . '.</p>';
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
add_shortcode('roadmap', 'wp_roadmap_roadmap_shortcode');