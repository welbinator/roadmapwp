<?php
/**
 * Ajax handling for voting functionality.
 */
function wp_roadmap_handle_vote() {
    check_ajax_referer('wp-roadmap-vote-nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $user_id = get_current_user_id();

    // Generate a unique key for non-logged-in user
    $user_key = $user_id ? 'user_' . $user_id : 'guest_' . md5($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);

    // Retrieve the current vote count
    $current_votes = get_post_meta($post_id, 'idea_votes', true) ?: 0;
    
    // Check if this user or guest has already voted
    $has_voted = get_post_meta($post_id, 'voted_' . $user_key, true);

    if ($has_voted) {
        // User or guest has voted, remove their vote
        $new_votes = max($current_votes - 1, 0);
        delete_post_meta($post_id, 'voted_' . $user_key);
    } else {
        // User or guest hasn't voted, add their vote
        $new_votes = $current_votes + 1;
        update_post_meta($post_id, 'voted_' . $user_key, true);
    }

    // Update the post meta with the new vote count
    update_post_meta($post_id, 'idea_votes', $new_votes);

    wp_send_json_success(array('new_count' => $new_votes, 'voted' => !$has_voted));

    wp_die();
}

add_action('wp_ajax_wp_roadmap_handle_vote', 'wp_roadmap_handle_vote');
add_action('wp_ajax_nopriv_wp_roadmap_handle_vote', 'wp_roadmap_handle_vote');

/**
 * Handle AJAX requests for ideas filter.
 */
function wp_roadmap_filter_ideas() {
    check_ajax_referer('wp-roadmap-vote-nonce', 'nonce');

    $filter_data = $_POST['filter_data'];
    $tax_query = array();

    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
    $display_taxonomies = array_merge(array('idea-tag'), array_keys($custom_taxonomies));

    foreach ($filter_data as $taxonomy => $data) {
        if (!empty($data['terms'])) {
            $tax_query[] = array(
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $data['terms'],
                'operator' => ($data['matchType'] === 'all') ? 'AND' : 'IN'
            );
        }
    }

    if (count($tax_query) > 1) {
        $tax_query['relation'] = 'AND';
    }

    $args = array(
        'post_type' => 'idea',
        'posts_per_page' => -1,
        'tax_query' => $tax_query
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
    
                        <div class="flex flex-wrap space-x-2 mt-2">
                            <?php $terms = wp_get_post_terms($idea_id, $display_taxonomies);
                            foreach ($terms as $term) :
                                $term_link = get_term_link($term);
                                if (!is_wp_error($term_link)) : ?>
                                    <a href="<?php echo esc_url($term_link); ?>" class="inline-flex items-center border font-semibold bg-blue-500 text-white px-3 py-1 rounded-full text-sm"><?php echo esc_html($term->name); ?></a>
                                <?php endif;
                            endforeach; ?>
                        </div>
    
                        <p class="text-gray-500 mt-2 text-sm">Submitted on: <?php echo get_the_date(); ?></p>
                        <p class="text-gray-700 mt-4"><?php echo get_the_excerpt(); ?></p>
    
                        <div class="flex items-center justify-between mt-6">
                            <a class="text-blue-500 hover:underline" href="<?php echo esc_url(get_permalink()); ?>" rel="ugc">Read More</a>
                            <div class="flex items-center idea-vote-box" data-idea-id="<?php echo $idea_id; ?>">
                                <button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg idea-vote-button">Upvote</button>
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
    wp_die();
}


add_action('wp_ajax_filter_ideas', 'wp_roadmap_filter_ideas');
add_action('wp_ajax_nopriv_filter_ideas', 'wp_roadmap_filter_ideas');



// Handles the AJAX request for deleting a custom taxonomy
function handle_delete_custom_taxonomy() {
    check_ajax_referer('wp_roadmap_delete_taxonomy_nonce', 'nonce');

    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());

    if (isset($custom_taxonomies[$taxonomy])) {
        unset($custom_taxonomies[$taxonomy]);
        update_option('wp_roadmap_custom_taxonomies', $custom_taxonomies);
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Taxonomy not found.'));
    }
}
add_action('wp_ajax_delete_custom_taxonomy', 'handle_delete_custom_taxonomy');

// Handles the AJAX request for deleting selected terms
function handle_delete_selected_terms() {
    check_ajax_referer('wp_roadmap_delete_terms_nonce', 'nonce');

    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    $terms = array_map('intval', (array) $_POST['terms']);

    foreach ($terms as $term_id) {
        wp_delete_term($term_id, $taxonomy);
    }

    wp_send_json_success();
}
add_action('wp_ajax_delete_selected_terms', 'handle_delete_selected_terms');


