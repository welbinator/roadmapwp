<?php
/**
 * Shortcode to display the new idea submission form.
 *
 * @return string The HTML output for the new idea form.
 */

 namespace RoadMapWP\Free\Shortcodes\NewIdeaForm;

 // Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

function new_idea_form_shortcode() {

    $user_id = get_current_user_id();
	$display_shortcode = true;
    $display_shortcode = apply_filters('roadmapwp_pro_new_idea_form_shortcode', $display_shortcode, $user_id);

    if (!$display_shortcode) {
        return '';
    }
    
	ob_start();
    // Flag to indicate the new idea form shortcode is loaded
    update_option( 'wp_roadmap_new_idea_shortcode_loaded', true );

	$form_submitted_nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';
    if (isset($_GET['new_idea_submitted']) && $_GET['new_idea_submitted'] == '1' && isset($_GET['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'new_idea_submitted')) {
        echo '<p>Thank you for your submission!</p>';
    }

    $hide_submit_idea_heading = apply_filters( 'wp_roadmap_hide_custom_idea_heading', false );
    $new_submit_idea_heading  = apply_filters( 'wp_roadmap_custom_idea_heading_text', 'Submit new Idea' );
?>
<div class="roadmap_wrapper container mx-auto">
    <div class="new_idea_form__frontend">
        <?php if ( ! $hide_submit_idea_heading ): ?>
            <h2><?php echo esc_html( $new_submit_idea_heading ); ?></h2>
        <?php endif; 
        $form_action = esc_url_raw($_SERVER['REQUEST_URI']);
        ?>
        <form action="<?php echo esc_url($form_action); ?>" method="post">
            <ul class="rmwp__flex-outer">
                <li class="rmwp__new_idea_form_input">
                    <label for="idea_title">Title:</label>
                    <input type="text" name="idea_title" id="idea_title" required>
                </li>
                <li class="rmwp__new_idea_form_input">
                    <label for="idea_description">Description:</label>
                    <textarea name="idea_description" id="idea_description" required></textarea>
                </li>
                <?php
                $taxonomies = get_object_taxonomies( 'idea', 'objects' );
                foreach ( $taxonomies as $taxonomy ) {
                    if ( $taxonomy->name !== 'idea-status' && ( $taxonomy->name === 'idea-tag' ) ) {
                        $terms = get_terms(
                            array(
                                'taxonomy'   => $taxonomy->name,
                                'hide_empty' => false,
                            )
                        );

                        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ): ?>
                            <li class="rmwp__new_idea_form_input">
                                <label><?php echo esc_html( $taxonomy->labels->singular_name ); ?>:</label>
                                <div class="rmwp__taxonomy-term-labels">
                                    <?php foreach ( $terms as $term ): ?>
                                        <label class="rmwp__taxonomy-term-label">
                                            <input type="checkbox" name="idea_taxonomies[<?php echo esc_attr( $taxonomy->name ); ?>][]" value="<?php echo esc_attr( $term->term_id ); ?>"> <?php echo esc_html( $term->name ); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endif;
                    }
                }
                ?>
                <?php $form_submission_nonce = wp_create_nonce('wp_roadmap_new_idea'); ?>
                <input type="hidden" name="wp_roadmap_new_idea_nonce" value="<?php echo esc_attr($form_submission_nonce); ?>">
                <li class="rmwp__new_idea_form_input">
                    <input type="submit" value="Submit Idea">
                </li>
            </ul>
        </form>
    </div>
</div>
<?php
return ob_get_clean();
}


add_shortcode( 'new_idea_form', __NAMESPACE__ . '\\new_idea_form_shortcode' );

/**
 * Function to handle the submission of the new idea form.
 */
function handle_new_idea_submission() {
    $submission_nonce = ''; // Initialize outside to ensure scope availability
    $idea_id = 0; // Initialize to ensure scope availability

    if ('POST' === $_SERVER['REQUEST_METHOD'] && isset($_POST['idea_title']) && isset($_POST['wp_roadmap_new_idea_nonce'])) {
        $submission_nonce = sanitize_text_field(wp_unslash($_POST['wp_roadmap_new_idea_nonce']));
        if (wp_verify_nonce($submission_nonce, 'wp_roadmap_new_idea')) {
            $title = sanitize_text_field($_POST['idea_title']);
            $description = sanitize_textarea_field($_POST['idea_description']);

            $options = get_option('wp_roadmap_settings', array());
            $default_idea_status = isset($options['default_idea_status']) ? $options['default_idea_status'] : 'pending';

            $idea_id = wp_insert_post([
                'post_title' => $title,
                'post_content' => $description,
                'post_status' => $default_idea_status,
                'post_type' => 'idea',
            ]);
        }
    }

    // Ensure this conditional only runs if the nonce was verified successfully
    if ('POST' === $_SERVER['REQUEST_METHOD'] && !empty($submission_nonce) && $idea_id) {
        if (!empty($_POST['idea_taxonomies']) && is_array($_POST['idea_taxonomies'])) {
            foreach ($_POST['idea_taxonomies'] as $tax_slug => $term_ids) {
                if (!taxonomy_exists($tax_slug)) {
                    continue; // Skip processing for non-existing taxonomies
                }
                $term_ids = array_map('intval', $term_ids); // Ensure term IDs are integers
                wp_set_object_terms($idea_id, $term_ids, $tax_slug);
            }
        }

		$default_idea_status_term = isset( $options['default_status_term'] ) ? $options['default_status_term'] : 'New Idea';
		
			// Check if selected statuses is set, not empty, and contains valid numeric values.
			$valid_selected_statuses = isset( $_POST['selected_statuses'] ) && is_array( $_POST['selected_statuses'] )
										&& count( array_filter( $_POST['selected_statuses'], 'is_numeric' ) ) > 0;

			if ( $valid_selected_statuses ) {
				$selected_status_terms = array_map( 'intval', $_POST['selected_statuses'] );
				wp_set_object_terms( $idea_id, $selected_status_terms, 'idea-status' );
			} else {
				// Fallback to default status term if none or invalid selected.
				wp_set_object_terms( $idea_id, array( $default_idea_status_term ), 'idea-status' );
			}
			
        $redirect_nonce = wp_create_nonce('new_idea_submitted');
        $redirect_url = add_query_arg(['new_idea_submitted' => '1', 'nonce' => $redirect_nonce], esc_url_raw( $_SERVER['REQUEST_URI'] )); // Replace with your actual URL
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('template_redirect', __NAMESPACE__ . '\\handle_new_idea_submission');
