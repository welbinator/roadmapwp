<?php
/**
 * Shortcode to display the new idea submission form.
 *
 * @return string The HTML output for the new idea form.
 */
function wp_roadmap_new_idea_form_shortcode() {
    global $wp_roadmap_new_idea_shortcode_loaded;
    $wp_roadmap_new_idea_shortcode_loaded = true;

    $output = '';

    if (isset($_GET['new_idea_submitted']) && $_GET['new_idea_submitted'] == '1') {
        $output .= '<p>Thank you for your submission!</p>';
    }
    
    $hide_submit_idea_heading = apply_filters('wp_roadmap_hide_custom_idea_heading', false);
    $new_submit_idea_heading = apply_filters('wp_roadmap_custom_idea_heading_text', 'Submit new Idea');
    
    $output .= '<div class="new_idea_form__frontend">';
    if (!$hide_submit_idea_heading) {
        $output .= '<h2>' . esc_html($new_submit_idea_heading) . '</h2>';
    }
    $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    $output .= '<ul class="flex-outer">';
    $output .= '<li class="new_idea_form_input"><label for="idea_title">Title:</label>';
    $output .= '<input type="text" name="idea_title" id="idea_title" required></li>';
    $output .= '<li class="new_idea_form_input"><label for="idea_description">Description:</label>';
    $output .= '<textarea name="idea_description" id="idea_description" required></textarea></li>';

    $taxonomies = get_object_taxonomies('idea', 'objects');
    foreach ($taxonomies as $taxonomy) {
        if ($taxonomy->name !== 'status' && ($taxonomy->name === 'idea-tag' || (function_exists('is_wp_roadmap_pro_active') && is_wp_roadmap_pro_active()))) {
            $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));

            if (!empty($terms) && !is_wp_error($terms)) {
                $output .= '<li class="new_idea_form_input">';
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

    $output .= wp_nonce_field('wp_roadmap_new_idea', 'wp_roadmap_new_idea_nonce');
    $output .= '<li class="new_idea_form_input"><input type="submit" value="Submit Idea"></li>';
    $output .= '</ul>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}

add_shortcode('new_idea_form', 'wp_roadmap_new_idea_form_shortcode');

/**
 * Function to handle the submission of the new idea form.
 */
function wp_roadmap_handle_new_idea_submission() {
    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['idea_title']) && isset($_POST['wp_roadmap_new_idea_nonce']) && wp_verify_nonce($_POST['wp_roadmap_new_idea_nonce'], 'wp_roadmap_new_idea')) {
        $title = sanitize_text_field($_POST['idea_title']);
        $description = sanitize_textarea_field($_POST['idea_description']);

        // Get the default post status option from the settings
        // Fetch Pro plugin settings
        $pro_options = get_option('wp_roadmap_pro_settings', []);
        // Retrieve the default status from Pro plugin settings
        $default_idea_status = isset($pro_options['default_idea_status']) ? $pro_options['default_idea_status'] : 'pending';

        $idea_id = wp_insert_post(array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => $default_idea_status, // Use the default post status
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
add_action('template_redirect', 'wp_roadmap_handle_new_idea_submission');


/**
 * Shortcode to display ideas.
 *
 * @return string The HTML output for displaying ideas.
 */
function wp_roadmap_display_ideas_shortcode() {
    global $wp_roadmap_ideas_shortcode_loaded;
    $wp_roadmap_ideas_shortcode_loaded = true;
    ob_start(); // Start output buffering

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

    // Retrieve color settings
    $options = get_option('wp_roadmap_settings');
    $vote_button_bg_color = isset($options['vote_button_bg_color']) ? $options['vote_button_bg_color'] : '#ff0000';
    $vote_button_text_color = isset($options['vote_button_text_color']) ? $options['vote_button_text_color'] : '#000000';
    $filter_tags_bg_color = isset($options['filter_tags_bg_color']) ? $options['filter_tags_bg_color'] : '#ff0000';
    $filter_tags_text_color = isset($options['filter_tags_text_color']) ? $options['filter_tags_text_color'] : '#000000';
    $filters_bg_color = isset($options['filters_bg_color']) ? $options['filters_bg_color'] : '#f5f5f5';

    // Check if the pro version is installed and settings are enabled
    $hide_display_ideas_heading = apply_filters('wp_roadmap_hide_display_ideas_heading', false);
    $new_display_ideas_heading = apply_filters('wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas');

    $output = '<h2>' . esc_html($new_display_ideas_heading) . '</h2>';
     if (!$hide_display_ideas_heading) {
        echo $output;
        
     }
    ?>
    <div class="filters-wrapper" style="background-color: <?php echo esc_attr($filters_bg_color); ?>;">
        <h4>Filters:</h4>
        <div class="filters-inner">
            <?php foreach ($taxonomies as $taxonomy_slug) : 
                $taxonomy = get_taxonomy($taxonomy_slug);
                if ($taxonomy && $taxonomy_slug != 'status') : ?>
                    <div class="wp-roadmap-ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr($taxonomy_slug); ?>">
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
    </div>
    </div>

    <div class="wp-roadmap-ideas-list">

    <?php
    $args = array(
        'post_type' => 'idea',
        'posts_per_page' => -1 // Adjust as needed
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) : ?>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8">
            <?php while ($query->have_posts()) : $query->the_post();
                $idea_id = get_the_ID();
                $vote_count = get_post_meta($idea_id, 'idea_votes', true) ?: '0'; ?>
    
                <div class="wp-roadmap-idea border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden" data-v0-t="card">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold"><a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a></h2>
    
                        <p class="text-gray-500 mt-2 text-sm">Submitted on: <?php echo get_the_date(); ?></p>
                        <div class="flex flex-wrap space-x-2 mt-2">
                            <?php $terms = wp_get_post_terms($idea_id, $taxonomies);
                            foreach ($terms as $term) :
                                $term_link = get_term_link($term);
                                if (!is_wp_error($term_link)) : ?>
                                    <a href="<?php echo esc_url($term_link); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm" style="background-color: <?php echo esc_attr($filter_tags_bg_color); ?>;color: <?php echo esc_attr($filter_tags_text_color); ?>;"><?php echo esc_html($term->name); ?></a>
                                <?php endif;
                            endforeach; ?>
                        </div>
    
                        
                        <p class="text-gray-700 mt-4"><?php echo get_the_excerpt(); ?></p>
    
                        <div class="flex items-center justify-between mt-6">
                            <a class="text-blue-500 hover:underline" href="<?php echo esc_url(get_permalink()); ?>" rel="ugc">Read More</a>
                            <div class="flex items-center idea-vote-box" data-idea-id="<?php echo $idea_id; ?>">
                                <button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg idea-vote-button" style="background-color: <?php echo esc_attr($vote_button_bg_color); ?>;color: <?php echo esc_attr($vote_button_text_color); ?>;">
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
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p>No ideas found.</p>
    <?php endif; 
    

    wp_reset_postdata();

    return ob_get_clean(); // Return the buffered output
}




add_shortcode('display_ideas', 'wp_roadmap_display_ideas_shortcode');

/**
 * Shortcode to display the roadmap.
 *
 * @return string The HTML output for displaying the roadmap.
 */
function wp_roadmap_roadmap_shortcode() {
    global $wp_roadmap_roadmap_shortcode_loaded;
    $wp_roadmap_roadmap_shortcode_loaded = true;

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
    <?php
    return ob_get_clean(); // Return the buffered output
}


add_shortcode('roadmap', 'wp_roadmap_roadmap_shortcode');
