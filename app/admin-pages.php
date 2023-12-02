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
        wp_nonce_field('wp_roadmap_pro_settings_action', 'wp_roadmap_pro_settings_nonce');
        ?>
            <?php
            settings_fields('wp_roadmap_settings');
            do_settings_sections('wp_roadmap_settings');
            ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Allow Comments on Ideas', 'wp-roadmap'); ?></th>
                    <td>
                        <input type="checkbox" name="wp_roadmap_settings[allow_comments]" value="1" <?php checked(1, $allow_comments); ?>/>
                    </td>
                </tr>
                
                <!-- Default Status Setting -->
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Set New Idea Default Status', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_default_idea_status_setting', '<p>' . esc_html__('Setting a default status is a pro feature', 'wp-roadmap') . '</p>');
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Choose Idea Template', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_choose_idea_template_setting', '<p>' . esc_html__('Choosing a custom idea template is a pro feature', 'wp-roadmap') . '</p>');
                        ?>
                    </td>
                </tr>

                <!-- Hide New Idea Heading Setting -->
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Hide New Idea Heading', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_hide_custom_idea_heading_setting', '<p>' . esc_html__('Hiding the new idea heading is a pro feature', 'wp-roadmap') . '</p>');
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Hide Display Ideas Heading', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_hide_display_ideas_heading_setting', '<p>' . esc_html__('Hiding the display ideas heading is a pro feature', 'wp-roadmap') . '</p>');
                        ?>
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

    echo '<h2>Add Custom Taxonomy</h2>';

    if ($pro_feature) {
        echo $pro_feature;
    } else {
        echo '<p>Adding custom taxonomies is a Pro feature</p>';
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

     // Form for adding new tags
     echo '<form action="' . esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')) . '" method="post">';
     echo '<input type="text" name="new_idea_tag" placeholder="New Tag" />';
     echo '<input type="hidden" name="taxonomy_slug" value="idea-tag" />';
     echo '<input type="submit" value="Add Tag" />';
     echo wp_nonce_field('add_term_to_idea_tag', 'wp_roadmap_add_term_nonce');
     echo '</form>';
}
