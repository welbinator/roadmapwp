<?php

// check if shortcode exists on page
function wp_road_map_check_for_shortcode() {
    global $wp_road_map_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        $wp_road_map_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_shortcode');

function wp_road_map_check_for_ideas_shortcode() {
    global $wp_road_map_ideas_shortcode_loaded, $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'display_ideas')) {
        $wp_road_map_ideas_shortcode_loaded = true;
    }
}
add_action('wp', 'wp_road_map_check_for_ideas_shortcode');

// enqueue admin styles
function wp_road_map_enqueue_admin_styles($hook) {
    global $post;

    // Enqueue CSS for 'idea' post type editor
    if ('post.php' == $hook && isset($post) && 'idea' == $post->post_type) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/idea-editor-styles.css';
        wp_enqueue_style('wp-road-map-idea-admin-styles', $css_url);
    }

    // Enqueue CSS for other specific plugin admin pages
    if (in_array($hook, ['roadmap_page_wp-road-map-taxonomies', 'roadmap_page_wp-road-map-settings'])) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
        wp_enqueue_style('wp-road-map-general-admin-styles', $css_url);
    }
}
add_action('admin_enqueue_scripts', 'wp_road_map_enqueue_admin_styles');


// enqueue front end styles
function wp_road_map_enqueue_frontend_styles() {
    global $wp_road_map_shortcode_loaded;
    global $wp_road_map_ideas_shortcode_loaded;

    // Enqueue general frontend styles if needed
    if ($wp_road_map_shortcode_loaded || $wp_road_map_ideas_shortcode_loaded) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-road-map-frontend.css'; 
        wp_enqueue_style('wp-road-map-frontend-styles', $css_url);
    }

    // Always check and enqueue style for 'idea' CPT
    if (is_singular('idea')) {
        wp_enqueue_style('wp-road-map-idea-style', plugin_dir_url(__FILE__) . 'assets/css/idea-style.css');
    }

    wp_enqueue_script('wp-road-map-voting', plugin_dir_url(__FILE__) . 'assets/js/voting.js', array('jquery'), null, true);
    wp_localize_script('wp-road-map-voting', 'wpRoadMapVoting', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wp-road-map-vote-nonce')
    ));
}

add_action('wp_enqueue_scripts', 'wp_road_map_enqueue_frontend_styles');



// add menus
function wp_road_map_add_admin_menu() {
    // Add top-level menu page
    add_menu_page(
        __('RoadMap', 'wp-road-map'), // Page title
        __('RoadMap', 'wp-road-map'), // Menu title
        'manage_options', // Capability
        'wp-road-map', // Menu slug
        'wp_road_map_settings_page', // Function to display the settings page
        'dashicons-chart-line', // Icon URL
        6 // Position
    );

    // Add submenu page for Settings
    add_submenu_page(
        'wp-road-map', // Parent slug
        __('Settings', 'wp-road-map'), // Page title
        __('Settings', 'wp-road-map'), // Menu title
        'manage_options', // Capability
        'wp-road-map-settings', // Menu slug
        'wp_road_map_settings_page' // Function to display the settings page
    );

    // Add submenu page for Ideas
    add_submenu_page(
        'wp-road-map', // Parent slug
        __('Ideas', 'wp-road-map'), // Page title
        __('Ideas', 'wp-road-map'), // Menu title
        'manage_options', // Capability
        'edit.php?post_type=idea' // Menu slug to the Ideas CPT
    );

    // Add submenu page for Taxonomies
add_submenu_page(
    'wp-road-map', // Parent slug
    __('Taxonomies', 'wp-road-map'), // Page title
    __('Taxonomies', 'wp-road-map'), // Menu title
    'manage_options', // Capability
    'wp-road-map-taxonomies', // Menu slug
    'wp_road_map_taxonomies_page' // Function to display the Taxonomies page
);

    // Remove duplicate RoadMap submenu item
    remove_submenu_page('wp-road-map', 'wp-road-map');
}
add_action('admin_menu', 'wp_road_map_add_admin_menu');

// Function to display WP RoadMap settings page
function wp_road_map_settings_page() {
    // Fetch current settings
    $options = get_option('wp_road_map_settings');
    $allow_comments = isset($options['allow_comments']) ? $options['allow_comments'] : '';

    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('wp_road_map_settings');
            do_settings_sections('wp_road_map_settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Allow Comments on Ideas</th>
                    <td>
                        <input type="checkbox" name="wp_road_map_settings[allow_comments]" value="1" <?php checked(1, $allow_comments); ?>/>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


// registering settings
function wp_road_map_register_settings() {
    register_setting('wp_road_map_settings', 'wp_road_map_settings');
}
add_action('admin_init', 'wp_road_map_register_settings');


// Function to display the Taxonomies management page
function wp_road_map_taxonomies_page() {

    // Check if a new tag is being added
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_road_map_add_term_nonce']) && wp_verify_nonce($_POST['wp_road_map_add_term_nonce'], 'add_term_to_idea_tag')) {
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
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_road_map_add_term_nonce']) && wp_verify_nonce($_POST['wp_road_map_add_term_nonce'], 'add_term_to_' . $_POST['taxonomy_slug'])) {
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
            $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
            unset($custom_taxonomies[$_GET['taxonomy']]);
            update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);
            // error_log('Deleted taxonomy: ' . $_GET['taxonomy']);
        }
    }

    // Check if the form has been submitted
    if (isset($_POST['wp_road_map_nonce'], $_POST['taxonomy_slug'])) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['wp_road_map_nonce'], 'wp_road_map_add_taxonomy')) {
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
                $custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
                $custom_taxonomies[$taxonomy_slug] = $taxonomy_data;

                // Update the option
                update_option('wp_road_map_custom_taxonomies', $custom_taxonomies);
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
                <?php wp_nonce_field('wp_road_map_add_taxonomy', 'wp_road_map_nonce'); ?>
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

// display default taxonomy/taxonomies

echo '<h2>Tags</h2>';
    
// Form for adding new tags
echo '<form action="' . esc_url(admin_url('admin.php?page=wp-road-map-taxonomies')) . '" method="post">';
echo '<input type="text" name="new_idea_tag" placeholder="New Tag" />';
echo '<input type="hidden" name="taxonomy_slug" value="idea-tag" />';
echo '<input type="submit" value="Add Tag" />';
echo wp_nonce_field('add_term_to_idea_tag', 'wp_road_map_add_term_nonce');
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
$custom_taxonomies = get_option('wp_road_map_custom_taxonomies', array());
if (!empty($custom_taxonomies)) {
    echo '<ul>';

    foreach ($custom_taxonomies as $taxonomy_slug => $taxonomy_data) {
        // Always display the taxonomy slug
        echo '<li><h3 style="display:inline;">Taxonomy Slug: ' . esc_html($taxonomy_slug) . '</h3>';

        // Add a delete link
        $delete_url = wp_nonce_url(
            admin_url('admin.php?page=wp-road-map-taxonomies&action=delete&taxonomy=' . urlencode($taxonomy_slug)),
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
        echo '<form action="' . esc_url(admin_url('admin.php?page=wp-road-map-taxonomies')) . '" method="post">';
        echo '<input type="text" name="new_term" placeholder="New Term" />';
        echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr($taxonomy_slug) . '" />';
        echo '<input type="submit" value="Add Term" />';
        echo wp_nonce_field('add_term_to_' . $taxonomy_slug, 'wp_road_map_add_term_nonce');
        echo '</form>';

        echo '</li>';
        echo '<hr style="margin-block: 20px;" />';
    }

    echo '</ul>';
}

}

// shortcode to display new idea form 
function wp_road_map_new_idea_form_shortcode() {
    global $wp_road_map_shortcode_loaded;
    $wp_road_map_shortcode_loaded = true;

    $output = '';

    // Check if the form has been submitted
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($_POST['wp_road_map_new_idea_nonce']) && wp_verify_nonce($_POST['wp_road_map_new_idea_nonce'], 'wp_road_map_new_idea')) {
        $title = sanitize_text_field($_POST['idea_title']);
        $description = sanitize_textarea_field($_POST['idea_description']);

        // Create new Idea post
        $idea_id = wp_insert_post(array(
            'post_title'    => $title,
            'post_content'  => $description,
            'post_status'   => 'publish',  // or 'pending' if you want to review submissions
            'post_type'     => 'idea',
        ));

        // Handle taxonomies
        if (isset($_POST['idea_taxonomies']) && is_array($_POST['idea_taxonomies'])) {
            foreach ($_POST['idea_taxonomies'] as $tax_slug => $term_ids) {
                $term_ids = array_map('intval', $term_ids); // Sanitize term IDs
                wp_set_object_terms($idea_id, $term_ids, $tax_slug);
            }
        }
        
        // Redirect to the same page to prevent form resubmission on refresh/back navigation
        wp_redirect(esc_url_raw($_SERVER['REQUEST_URI']));
        exit;

        $output .= '<p>Thank you for your submission!</p>';
    }

    // Display the form
    $output .= '<div class="new_taxonomy_form__frontend">';
    $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
    $output .= '<ul class="flex-outer">';
    $output .= '<li class="new_taxonomy_form_input"><label for="idea_title">Title:</label>';
    $output .= '<input type="text" name="idea_title" id="idea_title" required></li>';
    $output .= '<li class="new_taxonomy_form_input"><label for="idea_description">Description:</label>';
    $output .= '<textarea name="idea_description" id="idea_description" required></textarea></li>';

    // Fetch taxonomies associated with the 'idea' post type
    $taxonomies = get_object_taxonomies('idea', 'objects');
    foreach ($taxonomies as $taxonomy) {
        // Check if the taxonomy is public before generating the HTML
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
    

    // Nonce field for security
    $output .= wp_nonce_field('wp_road_map_new_idea', 'wp_road_map_new_idea_nonce');

    $output .= '<li class="new_taxonomy_form_input"><input type="submit" value="Submit Idea"></li>';
    $output .= '</form>';
    $output .= '</div>';

    return $output;
}
add_shortcode('new_idea_form', 'wp_road_map_new_idea_form_shortcode');

// shortcode to display ideas
function wp_road_map_display_ideas_shortcode() {
    global $wp_road_map_ideas_shortcode_loaded;
    $wp_road_map_ideas_shortcode_loaded = true;
    ob_start(); // Start output buffering

    ?>
    <div class="grid-container">
        <?php
        // Define the statuses to loop through
        $statuses = array('New Idea', 'Maybe', 'Not Now', 'On Roadmap');

        // Iterate through each status
        foreach ($statuses as $index => $status) {
            // WP Query to fetch ideas with the current status
            $args = array(
                'post_type' => 'idea',
                'posts_per_page' => -1, // Adjust the number as needed
                'tax_query' => array(
                    array(
                        'taxonomy' => 'status',
                        'field'    => 'name',
                        'terms'    => $status,
                    ),
                ),
            );
            $query = new WP_Query($args);

            // Check if there are posts for the current status
            if ($query->have_posts()) : ?>
                <div class="grid-column column-<?php echo ($index + 1); ?>">
                    <h2><?php echo esc_html($status); ?></h2> <!-- Heading for each status -->
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <div class="card">
                            <h3 class="card-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <p class="card-date"><?php the_date(); ?></p>
                            <p class="card-meta">Tags: <?php echo get_the_term_list(get_the_ID(), 'idea-tag', '', ', '); ?></p>
                            <p class="card-description"><?php the_excerpt(); ?></p>
                            <hr style="margin-block: 30px;" />
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; 
            wp_reset_postdata(); // Reset post data
        }
        ?>
    </div>
    <?php

    return ob_get_clean(); // Return the buffered output
}

add_shortcode('display_ideas', 'wp_road_map_display_ideas_shortcode');

// filter that dynamically enables or disables comments on idea posts
function wp_road_map_filter_comments_open($open, $post_id) {
    $post = get_post($post_id);
    $options = get_option('wp_road_map_settings');
    if ($post->post_type == 'idea') {
        if (isset($options['allow_comments']) && $options['allow_comments'] == 1) {
            return true; // Enable comments
        } else {
            return false; // Disable comments
        }
    }
    return $open;
}
add_filter('comments_open', 'wp_road_map_filter_comments_open', 10, 2);



