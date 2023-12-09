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

    // Adjust relation based on match type
    $relation = 'OR';
    foreach ($filter_data as $data) {
        if (isset($data['matchType']) && $data['matchType'] === 'all') {
            $relation = 'AND';
            break;
        }
    }
    if (count($tax_query) > 1) {
        $tax_query['relation'] = $relation;
    }

    $args = array(
        'post_type' => 'idea',
        'posts_per_page' => -1,
        'tax_query' => $tax_query
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) : $query->the_post();
            ?>
            <article class="wp-roadmap-idea">
                <div class="idea-vote-box" data-idea-id="<?php echo get_the_ID(); ?>">
                    <button class="idea-vote-button">^</button>
                    <div class="idea-vote-count"><?php echo get_post_meta(get_the_ID(), 'idea_votes', true) ?: '0'; ?></div>
                </div>
                <div class="idea-wrapper">
                    <div class="idea-header">
                        <h4 class="idea-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                        <p class="idea-meta">Posted on: <?php the_date(); ?></p>
                    </div>
                    <div class="idea-body">
                        <p class="idea-excerpt"><?php the_excerpt(); ?></p>
                    </div>
                </div>
            </article>
            <?php
        endwhile;
    } else {
        echo '<p>No ideas found.</p>';
    }

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
