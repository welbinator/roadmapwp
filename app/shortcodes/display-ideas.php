<?php
/**
 * Shortcode to display ideas.
 *
 * @return string The HTML output for displaying ideas.
 */

namespace RoadMapWP\Free\Shortcodes\DisplayIdeas;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function display_ideas_shortcode() {
    $user_id = get_current_user_id();
    $display_shortcode = apply_filters('display_ideas_shortcode', true, $user_id);

    if (!$display_shortcode) {
        return '';
    }

    // Flag to indicate the display ideas shortcode is loaded
    update_option('wp_roadmap_ideas_shortcode_loaded', true);

    ob_start(); // Start output buffering

    // Shared taxonomy logic
    $taxonomies = collect_taxonomies();
    $show_filters = should_show_filters($taxonomies);

    // Check if the Pro version is installed and settings are enabled
    $hide_display_ideas_heading = apply_filters('wp_roadmap_hide_display_ideas_heading', false);
    $new_display_ideas_heading = apply_filters('wp_roadmap_custom_display_ideas_heading_text', 'Browse Ideas');
    ?>

    <div class="roadmap_wrapper container mx-auto">
        <div class="browse_ideas_frontend">
            <h2><?php echo esc_html($new_display_ideas_heading); ?></h2>
            <?php
            if (!$hide_display_ideas_heading && $show_filters) {
                render_filters($taxonomies); // Render filters
            }
            ?>
        </div>

        <div class="rmwp__ideas-list">
            <?php 
            // Filter the output of render_ideas for modifications
            echo apply_filters('modify_render_ideas', render_ideas($taxonomies)); 
            ?>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

/**
 * Collect taxonomies for filtering.
 *
 * @return array
 */
function collect_taxonomies() {
    $taxonomies = ['idea-tag'];
    $custom_taxonomies = get_option('wp_roadmap_custom_taxonomies', []);
    return array_merge($taxonomies, array_keys($custom_taxonomies));
}

/**
 * Check if filters should be shown.
 *
 * @param array $taxonomies
 * @return bool
 */
function should_show_filters($taxonomies) {
    $show_filters = false;
    foreach ($taxonomies as $taxonomy_slug) {
        $taxonomy = get_taxonomy($taxonomy_slug);
        if ($taxonomy && $taxonomy_slug != 'idea-status') {
            $terms = get_terms(['taxonomy' => $taxonomy->name, 'hide_empty' => false]);
            if (!empty($terms)) {
                $show_filters = true;
            }
        }
    }
    return $show_filters;
}

/**
 * Render filters based on the taxonomies.
 *
 * @param array $taxonomies
 */
function render_filters($taxonomies) {
    ?>
    <div class="rmwp__filters-wrapper">
        <h4>Filters:</h4>
        <div class="rmwp__filters-inner">
            <?php
            foreach ($taxonomies as $taxonomy_slug) {
                $taxonomy = get_taxonomy($taxonomy_slug);
                if ($taxonomy && $taxonomy_slug != 'idea-status') {
                    $terms = get_terms(['taxonomy' => $taxonomy->name, 'hide_empty' => false]);
                    ?>
                    <div class="rmwp__ideas-filter-taxonomy" data-taxonomy="<?php echo esc_attr($taxonomy_slug); ?>">
                        <label><?php echo esc_html($taxonomy->labels->singular_name); ?>:</label>
                        <div class="rmwp__taxonomy-term-labels">
                            <?php
                            foreach ($terms as $term) {
                                echo '<label class="rmwp__taxonomy-term-label">';
                                echo '<input type="checkbox" name="idea_taxonomies[' . esc_attr($taxonomy->name) . '][]" value="' . esc_attr($term->slug) . '"> ';
                                echo esc_html($term->name);
                                echo '</label>';
                            }
                            ?>
                        </div>
                        <div class="rmwp__filter-match-type">
                            <label><input type="radio" name="match_type_<?php echo esc_attr($taxonomy->name); ?>" value="any" checked> Any</label>
                            <label><input type="radio" name="match_type_<?php echo esc_attr($taxonomy->name); ?>" value="all"> All</label>
                        </div>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * Render the list of ideas.
 *
 * @param array $taxonomies
 */
function render_ideas($taxonomies) {
    $args = apply_filters('roadmapwp_ideas_query_args', [
        'post_type' => 'idea',
        'posts_per_page' => -1
    ]);
    
    $query = new \WP_Query($args);

    ob_start(); // Start output buffering
    if ($query->have_posts()) : ?>
        <div class="<?php echo esc_attr(apply_filters('roadmapwp_ideas_grid_classes', 'grid gap-4 md:grid-cols-2 lg:grid-cols-3 px-6 py-8')); ?>">
            <?php while ($query->have_posts()) : 
                $query->the_post();
                $idea_id = get_the_ID();
                $vote_count = get_post_meta($idea_id, 'idea_votes', true) ?: '0';
                $terms = wp_get_post_terms($idea_id, $taxonomies); ?>
                
                <div class="wp-roadmap-idea flex flex-col justify-between border bg-card text-card-foreground rounded-lg shadow-lg overflow-hidden <?php echo esc_attr(apply_filters('roadmapwp_idea_classes', '', $idea_id)); ?>" data-v0-t="card">
                    <?php include plugin_dir_path(__FILE__) . '../includes/display-ideas-grid.php'; ?>

                    <?php
                    // Add hook here so Pro can insert its admin template
                    do_action('roadmapwp_after_idea_content', $idea_id);
                    ?>
                </div>

            <?php endwhile; ?>
        </div>
    <?php else : ?>
        <p>No ideas found.</p>
    <?php endif;

    wp_reset_postdata();
    return ob_get_clean(); // Return the buffered output
}


add_shortcode('display_ideas', __NAMESPACE__ . '\\display_ideas_shortcode');
