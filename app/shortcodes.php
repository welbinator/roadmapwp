<?php
/**
 * Shortcode to display the new idea submission form.
 *
 * @return string The HTML output for the new idea form.
 */
function wp_road_map_new_idea_form_shortcode() {
    global $wp_road_map_new_idea_shortcode_loaded;
    $wp_road_map_new_idea_shortcode_loaded = true;

    $output = '';

    if (isset($_GET['new_idea_submitted']) && $_GET['new_idea_submitted'] == '1') {
        $output .= '<p>Thank you for your submission!</p>';
    }
    
    $output .= '<div class="new_taxonomy_form__frontend">';
    $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    $output .= '<ul class="flex-outer">';
    $output .= '<li class="new_taxonomy_form_input"><label for="idea_title">Title:</label>';
    $output .= '<input type="text" name="idea_title" id="idea_title" required></li>';
    $output .= '<li class="new_taxonomy_form_input"><label for="idea_description">Description:</label>';
    $output .= '<textarea name="idea_description" id="idea_description" required></textarea></li>';

    $taxonomies = get_object_taxonomies('idea', 'objects');
    foreach ($taxonomies as $taxonomy) {
        if ($taxonomy->name !== 'status') {
            $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));

            if (!empty($terms) && !is_wp_error($terms)) {
                $output .= '<li class="new_taxonomy_form_input">';
                $output .= '<label>' . esc_html($taxonomy->labels->singular_name) . ':</label>';
                $output .= '<div class="taxonomy-checkboxes">';

                foreach ($terms as $term) {
                    $output .= '<label class="taxonomy-term-label">';
                    $output .= '<input type="checkbox" name="idea_taxonomies[' . esc_attr($taxonomy->name) . '][]" value="' . esc_attr($term->term_id) . '"> ';
                    $output .= esc_html($term->name);
                    $output .= '</label>';
                }

                $output .= '</div>';
                $output .= '</li>';
            }
        }
    }

    $output .= wp_nonce_field('wp_road_map_new_idea', 'wp_road_map_new_idea_nonce');
    $output .= '<li class="new_taxonomy_form_input"><input type="submit" value="Submit Idea"></li>';
    $output .= '</ul>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}
add_shortcode('new_idea_form', 'wp_road_map_new_idea_form_shortcode');

/**
 * Function to handle the submission of the new idea form.
 */
function wp_road_map_handle_new_idea_submission() {
    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['idea_title']) && isset($_POST['wp_road_map_new_idea_nonce']) && wp_verify_nonce($_POST['wp_road_map_new_idea_nonce'], 'wp_road_map_new_idea')) {
        $title = sanitize_text_field($_POST['idea_title']);
        $description = sanitize_textarea_field($_POST['idea_description']);

        $idea_id = wp_insert_post(array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',
            'post_type'     => 'idea',
        ));

        if (isset($_POST['idea_taxonomies']) && is_array($_POST['idea_taxonomies'])) {
            foreach ($_POST['idea_taxonomies'] as $tax_slug => $term_ids) {
                $term_ids = array_map('intval', $term_ids);
                wp_set_object_terms($idea_id, $term_ids, $tax_slug);
            }
        }

        $redirect_url = add_query_arg('new_idea_submitted', '1', esc_url_raw($_SERVER['REQUEST_URI']));
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('template_redirect', 'wp_road_map_handle_new_idea_submission');

/**
 * Shortcode to display ideas.
 *
 * @return string The HTML output for displaying ideas.
 */
function wp_road_map_display_ideas_shortcode() {
    global $wp_road_map_ideas_shortcode_loaded;
    $wp_road_map_ideas_shortcode_loaded = true;
    ob_start(); // Start output buffering

    // Get custom taxonomies excluding 'status'
    $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
    $taxonomies = array_merge(array('idea-tag'), array_keys($custom_taxonomies));
    ?>
    <div class="wp-road-map-ideas-filter">
        <?php foreach ($taxonomies as $taxonomy_slug) : 
            $taxonomy = get_taxonomy($taxonomy_slug);
            if ($taxonomy && $taxonomy_slug != 'status') : ?>
                <div class="wp-road-map-ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr($taxonomy_slug); ?>">
                    <label><?php echo esc_html($taxonomy->labels->singular_name); ?>:</label>
                    <?php
                    $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));
                    foreach ($terms as $term) {
                        echo '<label class="taxonomy-term-label">';
                        echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr($taxonomy->name) . '][]" value="' . esc_attr($term->slug) . '"> ';
                        echo esc_html($term->name);
                        echo '</label>';
                    }
                    ?>
                    <div class="filter-match-type">
                        <label><input type="radio" name="match_type_<?php echo esc_attr($taxonomy->name); ?>" value="any" checked> Any</label>
                        <label><input type="radio" name="match_type_<?php echo esc_attr($taxonomy->name); ?>" value="all"> All</label>
                    </div>
                </div>
            <?php endif; 
        endforeach; ?>
    </div>

    <div class="wp-road-map-ideas-list">
        <?php
        $args = array(
            'post_type' => 'idea',
            'posts_per_page' => -1 // Adjust as needed
        );
        $query = new WP_Query($args);
    
        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                ?>
                <div class="wp-road-map-idea">
                    <div class="idea-header">
                        <h3 class="idea-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <?php
                            // Fetch and display terms for the idea
                            $terms = wp_get_post_terms(get_the_ID(), get_object_taxonomies('idea'));
                            if (!empty($terms) && !is_wp_error($terms)) {
                                echo '<div class="idea-terms">';
                                foreach ($terms as $term) {
                                    $term_link = get_term_link($term);
                                    if (!is_wp_error($term_link)) {
                                        echo '<a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a> ';
                                    }
                                }
                                echo '</div>';
                            }
                            ?>
                        
                        <p class="idea-meta">Posted on: <?php the_date(); ?></p>
                        <div class="idea-vote-box" data-idea-id="<?php echo get_the_ID(); ?>">
                            <button class="idea-vote-button">^</button>
                            <div class="idea-vote-count"><?php echo get_post_meta(get_the_ID(), 'idea_votes', true) ?: '0'; ?></div>
                        </div>
                    </div>
                    <div class="idea-body">
                        <p class="idea-excerpt"><?php the_excerpt(); ?></p>
                    </div>
                </div>
                <hr class="idea-divider" />
                <?php
            endwhile;
        } else {
            echo '<p>No ideas found.</p>';
        }
    
        wp_reset_postdata();
    
        return ob_get_clean(); // Return the buffered output
    }


add_shortcode('display_ideas', 'wp_road_map_display_ideas_shortcode');

/**
 * Shortcode to display the roadmap.
 *
 * @return string The HTML output for displaying the roadmap.
 */
function wp_road_map_roadmap_shortcode() {
    global $wp_road_map_roadmap_shortcode_loaded;
    $wp_road_map_roadmap_shortcode_loaded = true;

    $output = '<div class="roadmap-grid">';

    // Define the statuses to display in each column
    $statuses = array('Up Next', 'On Roadmap');

    foreach ($statuses as $status) {
        // Query for ideas with the current status
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

        // Column for each status
        $output .= '<div class="roadmap-column">';
        $output .= '<h2>' . esc_html($status) . '</h2>';

        if ($query->have_posts()) {
            while ($query->have_posts()) : $query->the_post();
                $output .= '<div class="roadmap-idea">';
                $output .= '<h3 class="idea-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
                $output .= '<p class="idea-excerpt">' . get_the_excerpt() . '</p>';
                
                // Display vote count
                $vote_count = get_post_meta(get_the_ID(), 'idea_votes', true) ?: '0';
                $output .= '<div class="idea-vote-box">';
                $output .= '<div class="idea-vote-count">' . esc_html($vote_count) . '</div>';
                $output .= '</div>'; // Close vote box

                $output .= '</div>'; // Close idea
            endwhile;
        } else {
            $output .= '<p>No ideas found for ' . esc_html($status) . '.</p>';
        }

        wp_reset_postdata();
        $output .= '</div>'; // Close column
    }

    $output .= '</div>'; // Close grid

    return $output;
}
add_shortcode('roadmap', 'wp_road_map_roadmap_shortcode');
