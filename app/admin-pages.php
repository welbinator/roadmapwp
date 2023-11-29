<?php
/**
 * Function to display WP RoadMap settings page.
 */
function wp_roadmap_settings_page() {
    // Fetch current settings
    $options = get_option('wp_roadmap_settings');
    $allow_comments = isset($options['allow_comments']) ? $options['allow_comments'] : '';

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wp_roadmap_settings');
            do_settings_sections('wp_roadmap_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Allow Comments on Ideas</th>
                    <td>
                        <input type="checkbox" name="wp_roadmap_settings[allow_comments]" value="1" <?php checked(1, $allow_comments); ?>/>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * Function to display the Taxonomies management page.
 * This function allows adding terms to the "Tags" taxonomy.
 */
function wp_roadmap_taxonomies_page() {

    $pro_feature = apply_filters('wp_roadmap_pro_add_taxonomy_feature', '');

    if ($pro_feature) {
        echo $pro_feature;
    } else {
        echo '<p>Trying to add a custom taxonomy? This is a Pro feature.</p>';
    }


    // Check if a new tag is being added
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_roadmap_add_term_nonce']) && wp_verify_nonce($_POST['wp_roadmap_add_term_nonce'], 'add_term_to_idea_tag')) {
        $new_idea_tag = sanitize_text_field($_POST['new_idea_tag']);

        if (!empty($new_idea_tag)) {
            if (!term_exists($new_idea_tag, 'idea-tag')) {
                $inserted_tag = wp_insert_term($new_idea_tag, 'idea-tag');
                if (is_wp_error($inserted_tag)) {
                    echo '<div class="notice notice-error is-dismissible"><p>Error adding tag: ' . esc_html($inserted_tag->get_error_message()) . '</p></div>';
                } else {
                    echo '<div class="notice notice-success is-dismissible"><p>Tag added successfully.</p></div>';
                }
            } else {
                echo '<div class="notice notice-info is-dismissible"><p>The tag already exists.</p></div>';
            }
        }
    }

    // Display default taxonomy/taxonomies
    echo '<h2>Tags</h2>';
    
    // Form for adding new tags
    echo '<form action="' . esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')) . '" method="post">';
    echo '<input type="text" name="new_idea_tag" placeholder="New Tag" />';
    echo '<input type="hidden" name="taxonomy_slug" value="idea-tag" />';
    echo '<input type="submit" value="Add Tag" />';
    echo wp_nonce_field('add_term_to_idea_tag', 'wp_roadmap_add_term_nonce');
    echo '</form>';

    // Display existing tags
    $idea_tags = get_terms(array(
        'taxonomy' => 'idea-tag',
        'hide_empty' => false,
    ));
    if (!empty($idea_tags) && !is_wp_error($idea_tags)) {
        echo '<ul class="terms-list">';
        foreach ($idea_tags as $idea_tag) {
            echo '<li>' . esc_html($idea_tag->name) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No tags found.</p>';
    }
}
