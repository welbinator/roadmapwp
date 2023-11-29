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
 */
function wp_roadmap_taxonomies_page() {
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

    $message = '';

    // Check if a new term is being added
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_roadmap_add_term_nonce']) && wp_verify_nonce($_POST['wp_roadmap_add_term_nonce'], 'add_term_to_' . $_POST['taxonomy_slug'])) {
        $new_term = sanitize_text_field($_POST['new_term']);
        $taxonomy_slug = sanitize_key($_POST['taxonomy_slug']);

        if (!empty($new_term)) {
            if (!term_exists($new_term, $taxonomy_slug)) {
                $inserted_term = wp_insert_term($new_term, $taxonomy_slug);
                if (is_wp_error($inserted_term)) {
                    $message = 'Error adding term: ' . $inserted_term->get_error_message();
                } else {
                    $message = 'Term "' . esc_html($new_term) . '" added successfully to ' . esc_html($taxonomy_slug) . '.';
                }
            } else {
                $message = 'The term "' . esc_html($new_term) . '" already exists in ' . esc_html($taxonomy_slug) . '.';
            }
        }
    }

    // Display the message
    if (!empty($message)) {
        echo '<div class="notice notice-info"><p>' . $message . '</p></div>';
    }

    // Check if the user has the right capability
    if (!current_user_can('manage_options')) {
        return;
    }

    $error_message = '';

    // Handle taxonomy deletion
    if (isset($_GET['action'], $_GET['taxonomy'], $_GET['_wpnonce']) && $_GET['action'] == 'delete') {
        if (wp_verify_nonce($_GET['_wpnonce'], 'delete_taxonomy_' . $_GET['taxonomy'])) {
            $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
            unset($custom_taxonomies[$_GET['taxonomy']]);
            update_option('wp_roadmap_custom_taxonomies', $custom_taxonomies);
            // error_log('Deleted taxonomy: ' . $_GET['taxonomy']);
        }
    }

    // Check if the form has been submitted
    if (isset($_POST['wp_roadmap_nonce'], $_POST['taxonomy_slug'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['wp_roadmap_nonce'], 'wp_roadmap_add_taxonomy')) {
            $error_message = 'Invalid nonce...';
        } else {
            // Sanitize and validate inputs
            $raw_taxonomy_slug = $_POST['taxonomy_slug'];
            $taxonomy_slug = sanitize_key($raw_taxonomy_slug);

            // Validate slug: only letters, numbers, and underscores
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $raw_taxonomy_slug)) {
                $error_message = 'Invalid taxonomy slug. Only letters, numbers, and underscores are allowed.';
            } elseif (taxonomy_exists($raw_taxonomy_slug)) {
                $error_message = 'The taxonomy "' . esc_html($raw_taxonomy_slug) . '" already exists.';
            } else {
                $hierarchical = isset($_POST['hierarchical']) ? (bool) $_POST['hierarchical'] : false;
                $public = isset($_POST['public']) ? (bool) $_POST['public'] : false;
                $taxonomy_singular = sanitize_text_field($_POST['taxonomy_singular']);
                $taxonomy_plural = sanitize_text_field($_POST['taxonomy_plural']);

                // Register the taxonomy
                $labels = array(
                    'name' => _x($taxonomy_plural, 'taxonomy general name'),
                    'singular_name' => _x($taxonomy_singular, 'taxonomy singular name'),
                    'search_items' => __('Search ' . $taxonomy_plural),
                    'all_items' => __('All ' . $taxonomy_plural),
                    'parent_item' => __('Parent ' . $taxonomy_singular),
                    'parent_item_colon' => __('Parent ' . $taxonomy_singular . ':'),
                    'edit_item' => __('Edit ' . $taxonomy_singular),
                    'update_item' => __('Update ' . $taxonomy_singular),
                    'add_new_item' => __('Add New ' . $taxonomy_singular),
                    'new_item_name' => __('New ' . $taxonomy_singular . ' Name'),
                    'menu_name' => __($taxonomy_plural),
                );

                $taxonomy_data = array(
                    'labels' => $labels,
                    'public' => $public,
                    'hierarchical' => false,
                    'show_ui' => true,
                    'show_in_rest' => true,
                    'show_admin_column' => true,
                    'query_var' => true,
                    'rewrite' => array('slug' => $taxonomy_slug),
                );

                register_taxonomy($taxonomy_slug, 'idea', $taxonomy_data);
                // error_log('Registered taxonomy: ' . $taxonomy_slug);

                // Add the new taxonomy
                $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
                $custom_taxonomies[$taxonomy_slug] = $taxonomy_data;

                // Update the option
                update_option('wp_roadmap_custom_taxonomies', $custom_taxonomies);
                // error_log('Taxonomy saved: ' . $taxonomy_slug . '; Current taxonomies: ' . print_r($custom_taxonomies, true));

                if (empty($error_message)) {
                    echo '<div class="notice notice-success is-dismissible"><p>Taxonomy created successfully.</p></div>';
                }
            }
        }

        // Display error message if it exists
        if (!empty($error_message)) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_message) . '</p></div>';
        }
    }

    // Form HTML
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <div class="new_taxonomy_form">
        <h2>Add New Taxonomy</h2>
            <form action="" method="post">
                <?php wp_nonce_field('wp_roadmap_add_taxonomy', 'wp_roadmap_nonce'); ?>
                <ul class="flex-outer">
                    <li class="new_taxonomy_form_input">
                        <label for="taxonomy_slug">Slug:</label>
                        <input type="text" id="taxonomy_slug" name="taxonomy_slug" required>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <label for="taxonomy_singular">Singular Name:</label>
                        <input type="text" id="taxonomy_singular" name="taxonomy_singular" required>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <label for="taxonomy_plural">Plural Name:</label>
                        <input type="text" id="taxonomy_plural" name="taxonomy_plural" required>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <label for="public">Public:</label>
                        <select id="public" name="public">
                            <option value="1">Yes</option>
                            <option value="0">No</option>
                        </select>
                    </li>

                    <li class="new_taxonomy_form_input">
                        <input type="submit" value="Add Taxonomy">
                    </li>
                </ul>
            </form>
        </div>
    </div>
    <hr style="margin-block: 50px;" />
    <?php

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

    // Retrieve and display taxonomies
    echo '<h2>Your Custom Taxonomies</h2>';
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', array());
    if (!empty($custom_taxonomies)) {
        echo '<ul>';

        foreach ($custom_taxonomies as $taxonomy_slug => $taxonomy_data) {
            // Always display the taxonomy slug
            echo '<li><h3 style="display:inline;">Taxonomy Slug: ' . esc_html($taxonomy_slug) . '</h3>';

            // Add a delete link
            $delete_url = wp_nonce_url(
                admin_url('admin.php?page=wp-roadmap-taxonomies&action=delete&taxonomy=' . urlencode($taxonomy_slug)),
                'delete_taxonomy_' . $taxonomy_slug
            );
            echo ' <a href="' . esc_url($delete_url) . '" style="color:red;">Delete</a>';

            // Display existing terms for this taxonomy
            $terms = get_terms(array(
                'taxonomy' => $taxonomy_slug,
                'hide_empty' => false,
            ));
            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<h4>Existing Terms</h4>';
                echo '<ul class="terms-list">';
                foreach ($terms as $term) {
                    echo '<li>' . esc_html($term->name) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<p>No terms found for this taxonomy.</p>';
            }

            // Form for adding terms to this taxonomy
            echo '<form action="' . esc_url(admin_url('admin.php?page=wp-roadmap-taxonomies')) . '" method="post">';
            echo '<input type="text" name="new_term" placeholder="New Term" />';
            echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr($taxonomy_slug) . '" />';
            echo '<input type="submit" value="Add Term" />';
            echo wp_nonce_field('add_term_to_' . $taxonomy_slug, 'wp_roadmap_add_term_nonce');
            echo '</form>';

            echo '</li>';
            echo '<hr style="margin-block: 20px;" />';
        }

        echo '</ul>';
    }
}
