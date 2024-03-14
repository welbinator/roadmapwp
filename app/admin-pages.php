<?php
/**
 * Function to display WP RoadMap settings page.
 */

namespace RoadMapWP\Free\Admin\Pages;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
};

function settings_page() {
	// Fetch current settings
	$options       = get_option( 'wp_roadmap_settings' );
	$selected_page = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';

	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form action="options.php" method="post">
		<?php
		settings_fields( 'wp_roadmap_settings' );
		do_settings_sections( 'wp_roadmap_settings' );
		wp_nonce_field( 'wp_roadmap_settings_action', 'wp_roadmap_settings_nonce' );
		?>
			<?php
			settings_fields( 'wp_roadmap_settings' );
			do_settings_sections( 'wp_roadmap_settings' );
			?>

			<table class="form-table">
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Allow Comments on Ideas', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo wp_kses_post( apply_filters( 'wp_roadmap_enable_comments_setting', '<a target="_blank" href="' . esc_url( 'https://roadmapwp.com/#pricing' ) . '" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>' ) );
						?>
					</td>
				</tr>
				
				<!-- Default Status Setting -->
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Set New Idea Default Status', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo wp_kses_post( apply_filters( 'wp_roadmap_default_idea_status_setting', '<a target="_blank" href="' . esc_url( 'https://roadmapwp.com/#pricing' ) . '" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>' ) );
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Single Idea Template', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						// This filter will be handled in choose-idea-template.php
						echo wp_kses_post(apply_filters('wp_roadmap_single_idea_template_setting', '<a target="_blank" href="' . esc_url('https://roadmapwp.com/#pricing') . '" class="button button-primary" style="text-decoration: none;">' . esc_html__('Available in Pro', 'roadmapwp-free') . '</a>' ) );
						?>
					</td>
				</tr>
				<tr valign="top" id="single_idea_page_setting" style="display: none;">
					<th scope="row"><?php esc_html_e( 'Set page for single idea', 'roadmapwp-free' ); ?></th>
					<td>
						<select name="wp_roadmap_settings[single_idea_page]">
							<?php
							$pages = get_pages();
							foreach ( $pages as $page ) {
								echo '<option value="' . esc_attr( $page->ID ) . '"' . selected( $selected_page, $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
							}
							?>
						</select>
					</td>
				</tr>
				

				<!-- Hide New Idea Heading Setting -->
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Custom "Submit Idea" Heading', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo wp_kses_post( apply_filters( 'wp_roadmap_hide_custom_idea_heading_setting', '<a target="_blank" href="https://roadmapwp.com/#pricing" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>' ) );
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Custom "Browse Ideas" Heading', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo wp_kses_post( apply_filters( 'wp_roadmap_hide_display_ideas_heading_setting', '<a target="_blank" href="https://roadmapwp.com/#pricing" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>' ) );
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
function taxonomies_page() {
	// Check if the current user has the 'manage_options' capability.
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'roadmapwp-free' ) );
	}

	// Fetch custom taxonomies
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	// Check if a new term is being added
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['new_term'] ) && ! empty( $_POST['taxonomy_slug'] ) ) {
		// Verify the nonce
		if ( ! isset( $_POST['wp_roadmap_add_term_nonce'] ) || ! check_admin_referer( 'add_term_to_' . sanitize_text_field( $_POST['taxonomy_slug'] ), 'wp_roadmap_add_term_nonce' ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'roadmapwp-free' ) );
		}

		$new_term      = sanitize_text_field( $_POST['new_term'] );
		$taxonomy_slug = sanitize_text_field( $_POST['taxonomy_slug'] );

		if ( ! term_exists( $new_term, $taxonomy_slug ) ) {
			$inserted_term = wp_insert_term( $new_term, $taxonomy_slug );
			if ( is_wp_error( $inserted_term ) ) {
				echo 'term could not be added'; // Handle error: Term could not be added
			} else {
				echo 'Term added successfully';
			}
		} else {
			echo 'term already exists'; // Handle error: Term already exists
		}
	}
	$pro_feature = apply_filters( 'wp_roadmap_pro_add_taxonomy_feature', '' );

	echo '<h2>Add Custom Taxonomy</h2>';

	if ( $pro_feature ) {
		echo wp_kses_post ( $pro_feature );
		echo '<h2>Existing Taxonomies</h2>';
	} else {
		echo '<a target="_blank" href="https://roadmapwp.com/#pricing" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>';
		echo '<h2>Existing Taxonomies</h2>';
	}

	$taxonomies = get_taxonomies( array( 'object_type' => array( 'idea' ) ), 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( $taxonomy->name !== 'idea-tag' ) {
			continue; // Skip non-idea-tag taxonomies if Pro is not active
		}
		if ( $taxonomy->name === 'status' ) {
			continue; // Always skip 'status' taxonomy
		}

		echo '<h3>' . esc_html( $taxonomy->labels->name ) . '</h3>';

		if ( array_key_exists( $taxonomy->name, $custom_taxonomies ) ) {
			echo '<ul><li data-taxonomy-slug="' . esc_attr( $taxonomy->name ) . '">';
			echo '<a href="#" class="delete-taxonomy" data-taxonomy="' . esc_attr( $taxonomy->name ) . '">Delete this taxonomy</a>';
			echo '</li></ul>';
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy->name,
				'hide_empty' => false,
			)
		);
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			echo '<form method="post" class="delete-terms-form" data-taxonomy="' . esc_attr( $taxonomy->name ) . '">';
			echo '<ul class="terms-list">';
			foreach ( $terms as $term ) {
				echo '<li>';
				echo '<input type="checkbox" name="terms[]" value="' . esc_attr( $term->term_id ) . '"> ' . esc_html( $term->name );
				echo '</li>';
			}
			echo '</ul>';
			echo '<input type="submit" value="Delete Selected Terms" class="button delete-terms-button">';
			echo '</form>';
		} else {
			echo '<p>No terms found for ' . esc_html( $taxonomy->labels->name ) . '.</p>';
		}

		echo '<form action="' . esc_url( admin_url( 'admin.php?page=wp-roadmap-taxonomies' ) ) . '" method="post">';
		echo '<input type="text" name="new_term" placeholder="New Term for ' . esc_attr( $taxonomy->labels->singular_name ) . '" />';
		echo '<input type="hidden" name="taxonomy_slug" value="' . esc_attr( $taxonomy->name ) . '" />';
		echo '<input type="submit" value="Add Term" />';
		echo wp_nonce_field( 'add_term_to_' . sanitize_key($taxonomy->name), 'wp_roadmap_add_term_nonce' );
		echo '</form>';
		echo '<hr style="margin:20px; border:2px solid #8080802e;" />';
	}
}

function free_help_page() {
	?>
	<div class="wrap">
	
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="container px-4 md:px-6 mt-6">
		
		<h2 class="text-xl font-bold tracking-tight mb-2"><a href="https://roadmapwp.com/kb_category/shortcodes/" target="_blank">Shortcodes</a></h2>
		
			<div class="grid gap-6">
				<div class="border-2 rounded-lg p-4">
					<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/new-idea-form-shortcode/" target="_blank">[new_idea_form]</a></h3>
					<p class="text-gray-500 leading-6">Displays form for submitting ideas</p>
				</div>

				<div class="border-2 rounded-lg p-4">
					<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/display-ideas-shortcode/" target="_blank">[display_ideas]</a> </h3>
					<p class="text-gray-500 leading-6">Displays grid filled with published ideas</p>
				</div>

				<div class="border-2 rounded-lg p-4">
					<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-shortcode/" target=_blank">[roadmap status=""]</a> </h3>
					<p class="text-gray-500 leading-6">Displays columns filled with ideas based on statuses entered in the status parameter</p>
					<p class="text-gray-500 leading-6">Use "status" parameter to choose which status or statuses to display Example: [roadmap status="Up Next, On Roadmap"]</p>
					<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
					<ul class="list-disc list-inside mt-2 ml-4">
					<li>New Idea</li>
						<li>Not Now</li>
						<li>Maybe</li>
						<li>Up Next</li>
						<li>On Roadmap</li>
						<li>Closed</li>
					</ul>
				</div>

				<div class="border-2 rounded-lg p-4">
					<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-with-tabs-shortcode/" target="_blank">[roadmap_tabs status=""]</a> </h3>
					<p class="text-gray-500 leading-6">Displays tabs based on statuses entered in the status parameter. Clicking a tab displays corresponding ideas</p>
					<p class="text-gray-500 leading-6">Use "status" parameter to choose which status or statuses to display Example: [roadmap_tabs status="Up Next, On Roadmap"]</p>
					<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
					<ul class="list-disc list-inside mt-2 ml-4">
					<li>New Idea</li>
						<li>Not Now</li>
						<li>Maybe</li>
						<li>Up Next</li>
						<li>On Roadmap</li>
						<li>Closed</li>
					</ul>
				</div>                
		</div><!-- grid gap-6 -->
		
		<h2 class="text-xl font-bold tracking-tight mt-6 mb-2"><a href="https://roadmapwp.com/kb_category/blocks/" target="_blank">Blocks (pro only)</a></h2>
		<div class="grid gap-6">
			<div class="border-2 rounded-lg p-4">
				<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/new-idea-form-block/" target="_target">New Idea Form</a> </h3>
				<p class="text-gray-500 leading-6">Displays form for submitting ideas</p>
			</div>

			<div class="border-2 rounded-lg p-4">
				<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/display-ideas-block/" target="_blank">Display Ideas</a> </h3>
				<p class="text-gray-500 leading-6">Displays grid filled with published ideas</p>
			</div>

			<div class="border-2 rounded-lg p-4">
				<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-block/" target="_blank">Roadmap</a> </h3>
				<p class="text-gray-500 leading-6">Displays columns filled with ideas based on statuses selected.</p>
				<p class="text-gray-500 leading-6">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
				<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
				<ul class="list-disc list-inside mt-2 ml-4">
					<li>New Idea</li>
					<li>Not Now</li>
					<li>Maybe</li>
					<li>Up Next</li>
					<li>On Roadmap</li>
					<li>Closed</li>
				</ul>
			</div>

			<div class="border-2 rounded-lg p-4">
				<h3 class="font-semibold text-lg"><a href="https://roadmapwp.com/kb_article/roadmap-tabs-block/" target="_blank">Roadmap Tabs</a> </h3>
				<p class="text-gray-500 leading-6">Displays tabs based on statuses selected. Clicking a tab displays corresponding ideas</p>
				<p class="text-gray-500 leading-6">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
				<p class="text-gray-500 leading-6">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
				<ul class="list-disc list-inside mt-2 ml-4">
					<li>New Idea</li>
					<li>Not Now</li>
					<li>Maybe</li>
					<li>Up Next</li>
					<li>On Roadmap</li>
					<li>Closed</li>
				</ul>
			</div>
		</div><!-- grid gap-6 -->
		<!-- Add more content or instructions here as needed -->
	</div>

</div>
	
	<?php
}


