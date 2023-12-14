<?php

/**
 * Check for the presence of specific shortcodes on the page and set options for enqueuing CSS files.
 */

/**
 * Checks if the 'new_idea_form' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_new_idea_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'new_idea_form')) {
        update_option('wp_roadmap_new_idea_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_check_for_new_idea_shortcode');

/**
 * Checks if the 'display_ideas' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_ideas_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'display_ideas')) {
        update_option('wp_roadmap_ideas_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_check_for_ideas_shortcode');

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_roadmap_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        update_option('wp_roadmap_roadmap_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_check_for_roadmap_shortcode');

/**
 * Checks if the 'roadmap' shortcode is present on the current page.
 * Sets an option for enqueuing related CSS files if the shortcode is found.
 */
function wp_roadmap_check_for_single_idea_shortcode() {
    global $post;

    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
        update_option('wp_roadmap_single_idea_shortcode_loaded', true);
    }
}
add_action('wp', 'wp_roadmap_check_for_single_idea_shortcode');

/**
 * Enqueues admin styles for specific admin pages and post types.
 * 
 * @param string $hook The current admin page hook.
 */
function wp_roadmap_enqueue_admin_styles($hook) {
    global $post;

    // Enqueue CSS for 'idea' post type editor
    if ('post.php' == $hook && isset($post) && 'idea' == $post->post_type) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/idea-editor-styles.css';
        wp_enqueue_style('wp-roadmap-idea-admin-styles', $css_url);
    }

    // Enqueue CSS for specific plugin admin pages
    if (in_array($hook, ['roadmap_page_wp-roadmap-taxonomies', 'roadmap_page_wp-roadmap-settings'])) {
        $css_url = plugin_dir_url(__FILE__) . 'assets/css/admin-styles.css';
        wp_enqueue_style('wp-roadmap-general-admin-styles', $css_url);
    }

    // Enqueue JS for the 'Taxonomies' admin page
    if ('roadmap_page_wp-roadmap-taxonomies' == $hook) {
        wp_enqueue_script('wp-roadmap-taxonomies-js', plugin_dir_url(__FILE__) . 'assets/js/taxonomies.js', array('jquery'), null, true);
        wp_localize_script('wp-roadmap-taxonomies-js', 'wpRoadmapAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'delete_taxonomy_nonce' => wp_create_nonce('wp_roadmap_delete_taxonomy_nonce'),
            'delete_terms_nonce' => wp_create_nonce('wp_roadmap_delete_terms_nonce')
        ));
    }
}
add_action('admin_enqueue_scripts', 'wp_roadmap_enqueue_admin_styles');

/**
 * Enqueues front end styles and scripts for the plugin.
 *
 * This function checks whether any of the plugin's shortcodes are loaded or if it's a singular 'idea' post,
 * and enqueues the necessary styles and scripts.
 */
function wp_roadmap_enqueue_frontend_styles() {
    global $post;

    // Initialize flags
    $has_new_idea_form_shortcode = false;
    $has_display_ideas_shortcode = false;
    $has_roadmap_shortcode = false;
    $has_single_idea_shortcode = false;
    $has_block = false;

    // Check for shortcode presence in the post content
    if (is_a($post, 'WP_Post')) {
        $has_new_idea_form_shortcode = has_shortcode($post->post_content, 'new_idea_form');
        $has_display_ideas_shortcode = has_shortcode($post->post_content, 'display_ideas');
        $has_roadmap_shortcode = has_shortcode($post->post_content, 'roadmap');
        $has_single_idea_shortcode = has_shortcode($post->post_content, 'single_idea');

        // Check for block presence
        $has_block = has_block('wp-roadmap-pro/new-idea-form', $post) ||
                     has_block('wp-roadmap-pro/display-ideas', $post) ||
                     has_block('wp-roadmap-pro/roadmap', $post);
    }

    // Enqueue styles if a shortcode or block is loaded
    if ($has_new_idea_form_shortcode || $has_display_ideas_shortcode || $has_roadmap_shortcode || $has_single_idea_shortcode|| $has_block || is_singular('idea')) {

        // Enqueue Tailwind CSS
        $tailwind_css_url = plugin_dir_url(__FILE__) . '../dist/styles.css';
        wp_enqueue_style('wp-roadmap-tailwind-styles', $tailwind_css_url);

        // Enqueue your custom frontend styles
        $custom_css_url = plugin_dir_url(__FILE__) . 'assets/css/wp-roadmap-frontend.css';
        wp_enqueue_style('wp-roadmap-frontend-styles', $custom_css_url);
    

        // Enqueue scripts and localize them as before
        wp_enqueue_script('wp-roadmap-voting', plugin_dir_url(__FILE__) . 'assets/js/voting.js', array('jquery'), null, true);
        wp_localize_script('wp-roadmap-voting', 'wpRoadMapVoting', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-roadmap-vote-nonce')
        ));

        wp_enqueue_script('wp-roadmap-idea-filter', plugin_dir_url(__FILE__) . 'assets/js/idea-filter.js', array('jquery'), '', true);
        wp_localize_script('wp-roadmap-idea-filter', 'wpRoadMapAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp-roadmap-vote-nonce')
        ));
    }
}

add_action('wp_enqueue_scripts', 'wp_roadmap_enqueue_frontend_styles');


/**
 * Adds admin menu pages for the plugin.
 *
 * This function creates a top-level menu item 'RoadMap' in the admin dashboard,
 * along with several submenu pages like Settings, Ideas, and Taxonomies.
 */
function wp_roadmap_add_admin_menu() {
    add_menu_page(
        __('RoadMap', 'wp-roadmap'), 
        __('RoadMap', 'wp-roadmap'), 
        'manage_options', 
        'wp-roadmap', 
        'wp_roadmap_settings_page', 
        'dashicons-chart-line', 
        6
    );

    add_submenu_page(
        'wp-roadmap',
        __('Settings', 'wp-roadmap'),
        __('Settings', 'wp-roadmap'),
        'manage_options',
        'wp-roadmap-settings',
        'wp_roadmap_settings_page'
    );

    add_submenu_page(
        'wp-roadmap',
        __('Ideas', 'wp-roadmap'),
        __('Ideas', 'wp-roadmap'),
        'manage_options',
        'edit.php?post_type=idea'
    );

    add_submenu_page(
        'wp-roadmap',
        __('Taxonomies', 'wp-roadmap'),
        __('Taxonomies', 'wp-roadmap'),
        'manage_options',
        'wp-roadmap-taxonomies',
        'wp_roadmap_taxonomies_page'
    );

    // Check if Pro version is active, then add the submenu
    if (function_exists('is_wp_roadmap_pro_active') && is_wp_roadmap_pro_active()) {
        add_submenu_page(
            'wp-roadmap',
            __('License', 'wp-roadmap'),
            __('License', 'wp-roadmap'),
            'manage_options',
            'roadmapwp-license', // You can use a constant here if defined
            'roadmapwp_pro_license_page' // Ensure this function exists and renders the license page
        );
    }

    remove_submenu_page('wp-roadmap', 'wp-roadmap');
}
add_action('admin_menu', 'wp_roadmap_add_admin_menu');

/**
 * Adds the plugin license page to the admin menu.
 *
 * @return void
 */

 function roadmapwp_pro_license_page() {
	add_settings_section(
		'roadmapwp_pro_license',
		__( 'License' ),
		'roadmapwp_pro_license_key_settings_section',
		ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE
	);
	add_settings_field(
		'roadmapwp_pro_license_key',
		'<label for="roadmapwp_pro_license_key">' . __( 'License Key' ) . '</label>',
		'roadmapwp_pro_license_key_settings_field',
		ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE,
		'roadmapwp_pro_license',
	);
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'License Options' ); ?></h2>
		<form method="post" action="options.php">

			<?php
			do_settings_sections( ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE );
			settings_fields( 'roadmapwp_pro_license' );
			submit_button();
			?>

		</form>
	<?php
}

/**
 * Adds content to the settings section.
 *
 * @return void
 */
function roadmapwp_pro_license_key_settings_section() {
	esc_html_e( 'This is where you enter your license key.' );
}

/**
 * Outputs the license key settings field.
 *
 * @return void
 */
function roadmapwp_pro_license_key_settings_field() {
	$license = get_option( 'roadmapwp_pro_license_key' );
	$status  = get_option( 'roadmapwp_pro_license_status' );

	?>
	<p class="description"><?php esc_html_e( 'Enter your license key.' ); ?></p>
	<?php
	printf(
		'<input type="text" class="regular-text" id="roadmapwp_pro_license_key" name="roadmapwp_pro_license_key" value="%s" />',
		esc_attr( $license )
	);
	$button = array(
		'name'  => 'edd_license_deactivate',
		'label' => __( 'Deactivate License' ),
	);
	if ( 'valid' !== $status ) {
		$button = array(
			'name'  => 'edd_license_activate',
			'label' => __( 'Activate License' ),
		);
	}
	wp_nonce_field( 'roadmapwp_pro_nonce', 'roadmapwp_pro_nonce' );
	?>
	<input type="submit" class="button-secondary" name="<?php echo esc_attr( $button['name'] ); ?>" value="<?php echo esc_attr( $button['label'] ); ?>"/>
	<?php
}

/**
 * Registers the license key setting in the options table.
 *
 * @return void
 */
function roadmapwp_pro_register_option() {
	register_setting( 'roadmapwp_pro_license', 'roadmapwp_pro_license_key', 'edd_sanitize_license' );
}
add_action( 'admin_init', 'roadmapwp_pro_register_option' );

/**
 * Sanitizes the license key.
 *
 * @param string  $new The license key.
 * @return string
 */
function edd_sanitize_license( $new ) {
	$old = get_option( 'roadmapwp_pro_license_key' );
	if ( $old && $old !== $new ) {
		delete_option( 'roadmapwp_pro_license_status' ); // new license has been entered, so must reactivate
	}

	return sanitize_text_field( $new );
}

/**
 * Activates the license key.
 *
 * @return void
 */
function roadmapwp_pro_activate_license() {

	// listen for our activate button to be clicked
	if ( ! isset( $_POST['edd_license_activate'] ) ) {
		return;
	}

	// run a quick security check
	if ( ! check_admin_referer( 'roadmapwp_pro_nonce', 'roadmapwp_pro_nonce' ) ) {
		return; // get out if we didn't click the Activate button
	}

	// retrieve the license from the database
	$license = trim( get_option( 'roadmapwp_pro_license_key' ) );
	if ( ! $license ) {
		$license = ! empty( $_POST['roadmapwp_pro_license_key'] ) ? sanitize_text_field( $_POST['roadmapwp_pro_license_key'] ) : '';
	}
	if ( ! $license ) {
		return;
	}

	// data to send in our API request
	$api_params = array(
		'edd_action'  => 'activate_license',
		'license'     => $license,
		'item_id'     => ROADMAPWP_PRO_ITEM_ID,
		'item_name'   => rawurlencode( ROADMAPWP_PRO_ITEM_NAME ), // the name of our product in EDD
		'url'         => home_url(),
		'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	);

	// Call the custom API.
	$response = wp_remote_post(
		ROADMAPWP_PRO_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

		// make sure the response came back okay
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

		if ( is_wp_error( $response ) ) {
			$message = $response->get_error_message();
		} else {
			$message = __( 'An error occurred, please try again.' );
		}
	} else {

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( false === $license_data->success ) {

			switch ( $license_data->error ) {

				case 'expired':
					$message = sprintf(
						/* translators: the license key expiration date */
						__( 'Your license key expired on %s.', 'wp-roadmap' ),
						date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
					);
					break;

				case 'disabled':
				case 'revoked':
					$message = __( 'Your license key has been disabled.', 'wp-roadmap' );
					break;

				case 'missing':
					$message = __( 'Invalid license.', 'wp-roadmap' );
					break;

				case 'invalid':
				case 'site_inactive':
					$message = __( 'Your license is not active for this URL.', 'wp-roadmap' );
					break;

				case 'item_name_mismatch':
					/* translators: the plugin name */
					$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'wp-roadmap' ), ROADMAPWP_PRO_ITEM_NAME );
					break;

				case 'no_activations_left':
					$message = __( 'Your license key has reached its activation limit.', 'wp-roadmap' );
					break;

				default:
					$message = __( 'An error occurred, please try again.', 'wp-roadmap' );
					break;
			}
		}
	}

		// Check if anything passed on a message constituting a failure
	if ( ! empty( $message ) ) {
		$redirect = add_query_arg(
			array(
				'page'          => ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE,
				'sl_activation' => 'false',
				'message'       => rawurlencode( $message ),
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect );
		exit();
	}

	// $license_data->license will be either "valid" or "invalid"
	if ( 'valid' === $license_data->license ) {
		update_option( 'roadmapwp_pro_license_key', $license );
	}
	update_option( 'roadmapwp_pro_license_status', $license_data->license );
	wp_safe_redirect( admin_url( 'admin.php?page=' . ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE ) );
	exit();
}
add_action( 'admin_init', 'roadmapwp_pro_activate_license' );

/**
 * Deactivates the license key.
 * This will decrease the site count.
 *
 * @return void
 */
function roadmapwp_pro_deactivate_license() {

	// listen for our activate button to be clicked
	if ( isset( $_POST['edd_license_deactivate'] ) ) {

		// run a quick security check
		if ( ! check_admin_referer( 'roadmapwp_pro_nonce', 'roadmapwp_pro_nonce' ) ) {
			return; // get out if we didn't click the Activate button
		}

		// retrieve the license from the database
		$license = trim( get_option( 'roadmapwp_pro_license_key' ) );

		// data to send in our API request
		$api_params = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $license,
			'item_id'     => ROADMAPWP_PRO_ITEM_ID,
			'item_name'   => rawurlencode( ROADMAPWP_PRO_ITEM_NAME ), // the name of our product in EDD
			'url'         => home_url(),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);

		// Call the custom API.
		$response = wp_remote_post(
			ROADMAPWP_PRO_STORE_URL,
			array(
				'timeout'   => 15,
				'sslverify' => false,
				'body'      => $api_params,
			)
		);

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

			$redirect = add_query_arg(
				array(
					'page'          => ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE,
					'sl_activation' => 'false',
					'message'       => rawurlencode( $message ),
				),
				admin_url( 'admin.php' )
			);

			wp_safe_redirect( $redirect );
			exit();
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( 'deactivated' === $license_data->license ) {
			delete_option( 'roadmapwp_pro_license_status' );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=' . ROADMAPWP_PRO_PLUGIN_LICENSE_PAGE ) );
		exit();

	}
}
add_action( 'admin_init', 'roadmapwp_pro_deactivate_license' );

/**
 * Checks if a license key is still valid.
 * The updater does this for you, so this is only needed if you want
 * to do something custom.
 *
 * @return void
 */
function roadmapwp_pro_check_license() {

	$license = trim( get_option( 'roadmapwp_pro_license_key' ) );

	$api_params = array(
		'edd_action'  => 'check_license',
		'license'     => $license,
		'item_id'     => ROADMAPWP_PRO_ITEM_ID,
		'item_name'   => rawurlencode( ROADMAPWP_PRO_ITEM_NAME ),
		'url'         => home_url(),
		'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
	);

	// Call the custom API.
	$response = wp_remote_post(
		ROADMAPWP_PRO_STORE_URL,
		array(
			'timeout'   => 15,
			'sslverify' => false,
			'body'      => $api_params,
		)
	);

	if ( is_wp_error( $response ) ) {
		return false;
	}

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if ( 'valid' === $license_data->license ) {
		echo 'valid';
		exit;
		// this license is still valid
	} else {
		echo 'invalid';
		exit;
		// this license is no longer valid
	}
}

/**
 * This is a means of catching errors from the activation method above and displaying it to the customer
 */
function roadmapwp_pro_admin_notices() {
	if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

		switch ( $_GET['sl_activation'] ) {

			case 'false':
				$message = urldecode( $_GET['message'] );
				?>
				<div class="error">
					<p><?php echo wp_kses_post( $message ); ?></p>
				</div>
				<?php
				break;

			case 'true':
			default:
				// Developers can put a custom success message here for when activation is successful if they way.
				break;

		}
	}
}
add_action( 'admin_notices', 'roadmapwp_pro_admin_notices' );

/**
 * Registers settings for the RoadMap plugin.
 *
 * This function sets up a settings section for the plugin, allowing configuration of various features and functionalities.
 */
function wp_roadmap_register_settings() {
    register_setting('wp_roadmap_settings', 'wp_roadmap_settings');
}
add_action('admin_init', 'wp_roadmap_register_settings');

/**
 * Dynamically enables or disables comments on 'idea' post types.
 *
 * @param bool $open Whether the comments are open.
 * @param int $post_id The post ID.
 * @return bool Modified status of comments open.
 */
function wp_roadmap_filter_comments_open($open, $post_id) {
    $post = get_post($post_id);
    $options = get_option('wp_roadmap_settings');
     
    if ($post->post_type == 'idea') {
        return isset($options['allow_comments']) && $options['allow_comments'] == 1;
    }
    return $open;
}
add_filter('comments_open', 'wp_roadmap_filter_comments_open', 10, 2);

function wp_roadmap_redirect_single_idea($template) {
    global $post;

    if ('idea' === $post->post_type) {
        $options = get_option('wp_roadmap_settings');
        $single_idea_page_id = isset($options['single_idea_page']) ? $options['single_idea_page'] : '';
        $chosen_template = isset($options['single_idea_template']) ? $options['single_idea_template'] : 'plugin';

    }

    return $template;
}

add_filter('single_template', 'wp_roadmap_redirect_single_idea');


