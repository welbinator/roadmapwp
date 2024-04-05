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
	$options             = get_option( 'wp_roadmap_settings', array( 'default_status_term' => 'new-idea' ) );
	$status_terms        = get_terms(
		array(
			'taxonomy'   => 'idea-status',
			'hide_empty' => false,
		)
	);
	$default_status_term = isset( $options['default_status_term'] ) ? $options['default_status_term'] : 'new-idea';
	$selected_page = isset( $options['single_idea_page'] ) ? $options['single_idea_page'] : '';
	

	?>
	<div class="wrap">
		<h1 class="rmwp-h1"><?php echo esc_html( get_admin_page_title() ); ?></h1>
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
								
				<!-- Default Status Setting -->
				<tr valign="top">
				<th scope="row"><?php esc_html_e( 'Set Default Status Term for New Ideas', 'roadmapwp-free' ); ?></th>
				<td>
					<select name="wp_roadmap_settings[default_status_term]">
						<?php foreach ( $status_terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $default_status_term, $term->slug ); ?>>
								<?php echo esc_html( $term->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
				

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Set Published/Pending/Draft', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						// Filter hook to allow the Pro version to override this setting
						echo wp_kses_post( apply_filters( 'wp_roadmap_default_idea_status_setting', '<a target="_blank" href="https://roadmapwp.com/#pricing" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>' ) );
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

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Allow Comments on Ideas', 'roadmapwp-free' ); ?></th>
					<td>
						<?php				
							$allow_comments = isset( $options['allow_comments'] ) ? $options['allow_comments'] : '';
							$html = '<input type="checkbox" name="wp_roadmap_settings[allow_comments]" value="1"' . checked( 1, $allow_comments, false ) . '/>';
							echo $html;
						?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Restrict Voting to Logged-in Users', 'roadmapwp-free' ); ?></th>
					<td>
						<?php
						$restrict_voting = isset($options['restrict_voting']) ? $options['restrict_voting'] : '';
						?>
						<input type="checkbox" name="wp_roadmap_settings[restrict_voting]" value="1" <?php checked(1, $restrict_voting, true); ?>/>
						
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
						echo apply_filters( 'wp_roadmap_hide_custom_idea_heading_setting', 'roadmapwp-free' );
						?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Custom "Browse Ideas" Heading', 'roadmapwp-free' ); ?></th>
					<td>
					<?php
						// Filter hook to allow the Pro version to override this setting
						echo apply_filters( 'wp_roadmap_hide_display_ideas_heading_setting', 'roadmapwp-free' );
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

	// Fetch custom taxonomies.
	$custom_taxonomies = get_option( 'wp_roadmap_custom_taxonomies', array() );

	// Check if a new term is being added.
	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['new_term'] ) && ! empty( $_POST['taxonomy_slug'] ) ) {
		
		if ( ! isset( $_POST['wp_roadmap_add_term_nonce'] ) || ! check_admin_referer( 'add_term_to_' . sanitize_text_field( $_POST['taxonomy_slug'] ), 'wp_roadmap_add_term_nonce' ) ) {
			wp_die( esc_html__( 'Nonce verification failed.', 'roadmapwp-free' ) );
		}

		$new_term      = sanitize_text_field( $_POST['new_term'] );
		$taxonomy_slug = sanitize_text_field( $_POST['taxonomy_slug'] );

		if ( ! term_exists( $new_term, $taxonomy_slug ) ) {
			$inserted_term = wp_insert_term( $new_term, $taxonomy_slug );
			if ( is_wp_error( $inserted_term ) ) {
				echo 'term could not be added';
			} else {
				echo 'Term added successfully';
			}
		} else {
			echo 'term already exists';
		}
	}
	$pro_feature = apply_filters( 'wp_roadmap_pro_add_taxonomy_feature', '' );

	echo '<h2 class="rmwp-h2">Add Custom Taxonomy</h2>';

	if ( $pro_feature ) {
		echo wp_kses_post ( $pro_feature );
		echo '<h2 class="rmwp-h2">Existing Taxonomies</h2>';
	} else {
		echo '<a target="_blank" href="https://roadmapwp.com/#pricing" class="button button-primary" style="text-decoration: none;">' . esc_html__( 'Available in Pro', 'roadmapwp-free' ) . '</a>';
		echo '<h2 class="rmwp-h2">Existing Taxonomies</h2>';
	}

	$taxonomies = get_taxonomies( array( 'object_type' => array( 'idea' ) ), 'objects' );

	foreach ( $taxonomies as $taxonomy ) {
		if ( $taxonomy->name !== 'idea-tag' ) {
			continue;
		}
		if ( $taxonomy->name === 'idea-status' ) {
			continue;
		}

		echo '<h3 class="rmwp-h3">' . esc_html( $taxonomy->labels->name ) . '</h3>';

		if ( array_key_exists( $taxonomy->name, $custom_taxonomies ) ) {
			echo '<ul><li data-taxonomy-slug="' . esc_attr( $taxonomy->name ) . '">';
			echo '<a class="text-slate-600" href="#" class="delete-taxonomy" data-taxonomy="' . esc_attr( $taxonomy->name ) . '">Delete this taxonomy</a>';
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
			echo '<input type="submit" value="Delete Selected Terms" class="button rmwp__delete-terms-button">';
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

/**
 * Function to display the help page.
 */
function free_help_page() {
	?>
	<div class="wrap">
	
		<h1 class="rmwp-h1"><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<div class="container px-4 md:px-6 mt-6">
		<h2 class="rmwp-h2" class="text-xl font-bold tracking-tight mb-2"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/getting-started/" target="_blank">Getting Started</a></h2>
		A roadmap consists of 3 main parts:
		<ol>
			<li>
				The ability for your users to submit ideas/feedback
			</li>
			<li>
				The ability for your users to browse through existing ideas, to see what’s already been submitted, vote on ideas they like and leave comments
			</li>
			<li>
				The roadmap itself, which helps you keep your users in the loop regarding what’s being worked on, what will get worked on, and what won’t get worked on.
			</li>
		</ol>

		Each of these parts has their own shortcode (or block for pro users) which means getting up and running is literally as easy as 1, 2, 3!
		</div>
		<div class="container px-4 md:px-6 mt-6">
		
		<h2 class="rmwp-h2" class="text-xl font-bold tracking-tight mb-2"><a class="text-slate-600" href="https://roadmapwp.com/kb_category/shortcodes/" target="_blank">Shortcodes</a></h2>
		
			<div class="grid gap-6">
				<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
					<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/new-idea-form-shortcode/" target="_blank">[new_idea_form]</a><span class="copy-tooltip" data-text="[new_idea_form]"><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
					<p class="text-gray-500 leading-6 m-0">Displays form for submitting ideas</p>
				</div>

				<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
					<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/display-ideas-shortcode/" target="_blank">[display_ideas]</a><span class="copy-tooltip" data-text="[display_ideas]"><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
					<p class="text-gray-500 leading-6 m-0">Displays grid filled with published ideas</p>
				</div>

				<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
					<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/roadmap-shortcode/" target=_blank">[roadmap status=""]</a><span class="copy-tooltip" data-text='[roadmap status=""]'><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
					<p class="text-gray-500 leading-6 m-0">Displays columns filled with ideas based on statuses entered in the status parameter</p>
					<p class="text-gray-500 leading-6 m-0">Use "status" parameter to choose which status or statuses to display Example: [roadmap status="Up Next, On Roadmap"]</p>
					<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
					<ul class="list-disc list-inside mt-2 ml-4">
					<li>New Idea</li>
						<li>Not Now</li>
						<li>Maybe</li>
						<li>Up Next</li>
						<li>On Roadmap</li>
						<li>Closed</li>
					</ul>
				</div>

				<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
					<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/roadmap-with-tabs-shortcode/" target="_blank">[roadmap_tabs status=""]</a><span class="copy-tooltip" data-text='[roadmap_tabs status=""]'><span class="no-underline text-gray-500 dashicons dashicons-admin-page cursor-pointer"></span></span></h3>
					<p class="text-gray-500 leading-6 m-0">Displays tabs based on statuses entered in the status parameter. Clicking a tab displays corresponding ideas</p>
					<p class="text-gray-500 leading-6 m-0">Use "status" parameter to choose which status or statuses to display Example: [roadmap_tabs status="Up Next, On Roadmap"]</p>
					<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
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
		
		<h2 class="rmwp-h2" class="text-xl font-bold tracking-tight mt-6 mb-2"><a class="text-slate-600" href="https://roadmapwp.com/kb_category/blocks/" target="_blank">Blocks (pro only)</a></h2>
		<div class="grid gap-6">
			<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
				<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/new-idea-form-block/" target="_target">New Idea Form</a> </h3>
				<p class="text-gray-500 leading-6 m-0">Displays form for submitting ideas</p>
			</div>

			<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
				<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/display-ideas-block/" target="_blank">Display Ideas</a> </h3>
				<p class="text-gray-500 leading-6 m-0">Displays grid filled with published ideas</p>
			</div>

			<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
				<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/roadmap-block/" target="_blank">Roadmap</a> </h3>
				<p class="text-gray-500 leading-6 m-0">Displays columns filled with ideas based on statuses selected.</p>
				<p class="text-gray-500 leading-6 m-0">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
				<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
				<ul class="list-disc list-inside mt-2 ml-4">
					<li>New Idea</li>
					<li>Not Now</li>
					<li>Maybe</li>
					<li>Up Next</li>
					<li>On Roadmap</li>
					<li>Closed</li>
				</ul>
			</div>

			<div class="border-2 border-gray-200 border-solid rounded-lg p-4">
				<h3 class="rmwp-h3" class="font-semibold text-lg m-0"><a class="text-slate-600" href="https://roadmapwp.com/kb_article/roadmap-tabs-block/" target="_blank">Roadmap Tabs</a> </h3>
				<p class="text-gray-500 leading-6 m-0">Displays tabs based on statuses selected. Clicking a tab displays corresponding ideas</p>
				<p class="text-gray-500 leading-6 m-0">After adding the block to the page, in the block editor choose which statuses you want to display.</p>
				<p class="text-gray-500 leading-6 m-0">Values included in free status parameter (Pro users can change these on the Taxonomies page):</p>
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


