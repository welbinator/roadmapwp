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
                        echo apply_filters('wp_roadmap_default_idea_status_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap') . '</a>');
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Choose Idea Template', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_choose_idea_template_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap') . '</a>');
                        ?>
                    </td>
                </tr>

                <!-- Hide New Idea Heading Setting -->
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Custom "Submit Idea" Heading', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_hide_custom_idea_heading_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap') . '</a>');
                        ?>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Custom "Browse Ideas" Heading', 'wp-roadmap'); ?></th>
                    <td>
                        <?php
                        // Filter hook to allow the Pro version to override this setting
                        echo apply_filters('wp_roadmap_hide_display_ideas_heading_setting', '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap') . '</a>');
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

     // Check if a new term is being added
     if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['new_term']) && !empty($_POST['taxonomy_slug'])) {
        $new_term = sanitize_text_field($_POST['new_term']);
        $taxonomy_slug = sanitize_text_field($_POST['taxonomy_slug']);

        if (!term_exists($new_term, $taxonomy_slug)) {
            $inserted_term = wp_insert_term($new_term, $taxonomy_slug);
            if (is_wp_error($inserted_term)) {
                echo "term could not be added";// Handle error: Term could not be added
            } else {
                echo "Term added successfully";
            }
        } else {
            echo "term already exists";// Handle error: Term already exists
        }
    }
    $pro_feature = apply_filters('wp_roadmap_pro_add_taxonomy_feature', '');

    echo '<h2>Add Custom Taxonomy</h2>';

    if ($pro_feature) {
        echo $pro_feature;
        echo '<h2>Existing Taxonomies</h2>';
    } else {
        echo '<a target="_blank" href="https://roadmapwp.com/pro" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'wp-roadmap') . '</a>';
        echo '<h2>Existing Taxonomies</h2>';
    }

    $taxonomies = get_taxonomies(array('object_type' => array('idea')), 'objects');
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());

    foreach ($taxonomies as $taxonomy) {
        if ($taxonomy->name === 'status') {
            continue;
        }

        echo '<h3>' . esc_html($taxonomy->labels->name) . '</h3>';

        if (array_key_exists($taxonomy->name, $custom_taxonomies)) {
            echo '<ul><li data-taxonomy-slug="' . esc_attr($taxonomy->name) . '">';
            echo '<a href="#" class="delete-taxonomy" data-taxonomy="' . esc_attr($taxonomy->name) . '">Delete this taxonomy</a>';
            echo '</li></ul>';
        }

        $terms = get_terms(array('taxonomy' => $taxonomy->name, 'hide_empty' => false));
        if (!empty($terms) && !is_wp_error($terms)) {
            echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr($taxonomy->name) . '">';
            echo '<ul class="terms-list">';
            foreach ($terms as $term) {
                echo '<li>';
                echo '<input type="checkbox" name="terms[]" value="' . esc_attr($term->term_id) . '"> ' . esc_html($term->name);
                echo '</li>';
            }
            echo '</ul>';
            echo '<input type="submit" value="Delete Selected Terms" class="button delete-terms-button">';
            echo '</form>';
        } else {
            echo '<p>No terms found for ' . esc_html($taxonomy->labels->name) . '.</p>';
        }

        echo '<form action="' . esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')) . '" method="post">';
        echo '<input type="text" name="new_term" placeholder="New Term for ' . esc_attr($taxonomy->labels->singular_name) . '" />';
        echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr($taxonomy->name) . '" />';
        echo '<input type="submit" value="Add Term" />';
        echo wp_nonce_field('add_term_to_' . $taxonomy->name, 'wp_roadmap_add_term_nonce');
        echo '</form>';
        echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
    }
}

