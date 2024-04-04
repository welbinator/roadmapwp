<?php
/**
 * Customizer Style Options for RoadMapWP
 *
 * This file registers custom style options for the RoadMapWP  plugin through the WordPress Customizer,
 * allowing users to tailor the appearance of various elements like vote buttons, tags, and roadmap tabs.
 * It includes color settings for backgrounds, text, and buttons, enhancing the visual customization capabilities
 * for end-users.
 *
 * @package RoadMapWP\Free\Settings\CustomizerStyles
 */

namespace RoadMapWP\Free\Settings\CustomizerStyles;

add_action( 'customize_register', __NAMESPACE__ . '\\register_customizer_styles' );


/**
 * Register customizer settings for Roadmap styles.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager object.
 */
function register_customizer_styles( $wp_customize ) {
	// Add Roadmap Styles panel to customizer.
	$wp_customize->add_panel(
		'roadmap_styles',
		array(
			'title'       => __( 'Roadmap Styles', 'roadmapwp-free' ),
			'description' => __( 'Customize Roadmap Styles', 'roadmapwp-free' ),
			'priority'    => 160,
		)
	);

	// Global Styles Section.
	$wp_customize->add_section(
		'roadmap_global_styles',
		array(
			'title'    => __( 'Global Styles', 'roadmapwp-free' ),
			'panel'    => 'roadmap_styles',
			'priority' => 1,
		)
	);

	// Roadmap Tabs Styles Section.
	$wp_customize->add_section(
		'roadmap_tabs_styles',
		array(
			'title'    => __( 'Roadmap Tabs Styles', 'roadmapwp-free' ),
			'panel'    => 'roadmap_styles',
			'priority' => 1,
		)
	);

	/*
	* Global Styles
	*/

	// Vote Button Background Color.
	$wp_customize->add_setting(
		'vote_button_background_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'vote_button_background_color',
			array(
				'label'    => __( 'Vote Button Background Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'vote_button_background_color',
			)
		)
	);

	// Vote Button Text Color.
	$wp_customize->add_setting(
		'vote_button_text_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'vote_button_text_color',
			array(
				'label'    => __( 'Vote Button Text Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'vote_button_text_color',
			)
		)
	);

	// Tags Background Color.
	$wp_customize->add_setting(
		'tags_background_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'tags_background_color',
			array(
				'label'    => __( 'Tags Background Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'tags_background_color',
			)
		)
	);

	// Tags Text Color.
	$wp_customize->add_setting(
		'tags_text_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'tags_text_color',
			array(
				'label'    => __( 'Tags Text Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'tags_text_color',
			)
		)
	);

	// Submit Idea Button Background Color.
	$wp_customize->add_setting(
		'submit_idea_button_background_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'submit_idea_button_background_color',
			array(
				'label'    => __( 'Submit Idea Button Background Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'submit_idea_button_background_color',
			)
		)
	);

	// Submit Idea Button Text Color.
	$wp_customize->add_setting(
		'submit_idea_button_text_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'submit_idea_button_text_color',
			array(
				'label'    => __( 'Submit Idea Button Text Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'submit_idea_button_text_color',
			)
		)
	);

	// Filter Box Background Color.
	$wp_customize->add_setting(
		'filter_box_background_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'filter_box_background_color',
			array(
				'label'    => __( 'Filter Box Background Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'filter_box_background_color',
			)
		)
	);

	// Filter Box Text Color.
	$wp_customize->add_setting(
		'filter_box_text_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'filter_box_text_color',
			array(
				'label'    => __( 'Filter Box Text Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_global_styles',
				'settings' => 'filter_box_text_color',
			)
		)
	);

	/*
	* Roadmap Tabs Styles
	*/

	// Tabs Container Background Color.
	$wp_customize->add_setting(
		'tabs_container_background_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'tabs_container_background_color',
			array(
				'label'    => __( 'Container Background Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_tabs_styles',
				'settings' => 'tabs_container_background_color',
			)
		)
	);

	// Roadmap Tabs Tab Background Color.
	$wp_customize->add_setting(
		'tabs_tab_background_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'tabs_tab_background_color',
			array(
				'label'    => __( 'Tab Background Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_tabs_styles',
				'settings' => 'tabs_tab_background_color',
			)
		)
	);

	// Roadmap Tabs Tab Text Color.
	$wp_customize->add_setting(
		'tabs_tab_text_color',
		array(
			'sanitize_callback' => 'sanitize_hex_color',
		)
	);

	$wp_customize->add_control(
		new \WP_Customize_Color_Control(
			$wp_customize,
			'tabs_tab_text_color',
			array(
				'label'    => __( 'Tab Text Color', 'roadmapwp-free' ),
				'section'  => 'roadmap_tabs_styles',
				'settings' => 'tabs_tab_text_color',
			)
		)
	);
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\custom_styles' );

/**
 * Enqueue custom styles for LearnDash.
 */
function custom_styles() {
	
	 // Correctly resolve the path to the stylesheet.
	 $css_file_path = plugin_dir_path( __FILE__ ) . 'assets/css/customizer-styles.css'; // Adjusted path

	 // Use plugin_dir_url() to get the URL to the stylesheet.
	 $css_file_url = plugin_dir_url( __FILE__ ) . 'assets/css/customizer-styles.css'; // Adjusted URL
 
	 // Check if the CSS file exists to avoid filemtime() warning.
	 if ( file_exists( $css_file_path ) ) {
		 wp_enqueue_style(
			 'rmwp-custom-styles',
			 $css_file_url,
			 array(), 
			 RMWP_PLUGIN_VERSION
		 );
	 } else {
		 // Optionally handle the case where the file does not exist.
		 wp_enqueue_style(
			 'rmwp-custom-styles',
			 $css_file_url,
			 array(), 
			 RMWP_PLUGIN_VERSION
			 
		 );
	 }

	/*
	* Global styles
	*/
	$custom_css = '
	.idea-vote-button, .idea-vote-count { 
        background-color: ' . get_theme_mod( 'vote_button_background_color', '' ) . '!important;
		color: ' . get_theme_mod( 'vote_button_text_color', '' ) . '!important;
    }
    .idea-tags a { 
        background-color: ' . get_theme_mod( 'tags_background_color', '' ) . '!important;
		color: ' . get_theme_mod( 'tags_text_color', '' ) . '!important;
    }
    .rmwp__new_idea_form_input input[type="submit"] { 
        background-color: ' . get_theme_mod( 'submit_idea_button_background_color', '' ) . '!important;
		color: ' . get_theme_mod( 'submit_idea_button_text_color', '' ) . '!important;
    }
    .rmwp__filters-wrapper { 
        background-color: ' . get_theme_mod( 'filter_box_background_color', '' ) . '!important;
    }
    .rmwp__filters-wrapper * { 
		color: ' . get_theme_mod( 'filter_box_text_color', '' ) . '!important;
    }
	';

	/*
	* Roadmap Tabs Styles
	*/

	$custom_css .= '
    .roadmap-tabs-wrapper .roadmap-tabs { 
		background-color: ' . get_theme_mod( 'tabs_container_background_color', '' ) . '!important;
    }
    .roadmap-tabs-wrapper .roadmap-tabs .roadmap-tab { 
		background-color: ' . get_theme_mod( 'tabs_tab_background_color', '' ) . '!important;
        color: ' . get_theme_mod( 'tabs_tab_text_color', '' ) . '!important;
    }
    ';

	wp_add_inline_style( 'rmwp-custom-styles', $custom_css );
}
