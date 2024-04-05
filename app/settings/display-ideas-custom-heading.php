<?php
/**
 * This file contains functions for managing display settings of idea headings in the RoadMapWP Free plugin.
 *
 * @package RoadMapWP\Free\Settings\DisplayIdeas\CustomHeading
 */

namespace RoadMapWP\Free\Settings\DisplayIdeasCustomHeading;

/**
 * Determines whether to hide the display ideas heading based on the plugin settings.
 *
 * @param bool $hide_display_ideas_heading Current value of the setting.
 * @return bool New value of the setting.
 */
function hide_display_ideas_heading( $hide_display_ideas_heading ) {
	$options = get_option( 'wp_roadmap_settings', array() );
	return ! empty( $options['hide_display_ideas_heading'] );
}

add_filter( 'wp_roadmap_hide_display_ideas_heading', __NAMESPACE__ . '\\hide_display_ideas_heading' );

/**
 * Customizes the display ideas heading text based on the plugin settings.
 *
 * @param string $default_heading The default heading text.
 * @return string New heading text.
 */
function custom_display_ideas_heading_text( $default_heading ) {
	$options = get_option( 'wp_roadmap_settings', array() );
	return ! empty( $options['custom_display_ideas_heading'] ) ? $options['custom_display_ideas_heading'] : $default_heading;
}

add_filter( 'wp_roadmap_custom_display_ideas_heading_text', __NAMESPACE__ . '\\custom_display_ideas_heading_text' );

/**
 * Adds new heading text field in the settings.
 *
 * @param string $content The current settings content.
 * @return string Modified settings content with the new field.
 */
function hide_display_ideas_heading_setting( $content ) {
	$options                            = get_option( 'wp_roadmap_settings', array() );
	$hide_display_ideas_heading_checked = isset( $options['hide_display_ideas_heading'] ) && $options['hide_display_ideas_heading'] == '1' ? 'checked' : '';
	$new_display_ideas_heading          = isset( $options['custom_display_ideas_heading'] ) ? $options['custom_display_ideas_heading'] : '';

	$content  = '<label>Hide Heading: </label>';
	$content .= '<input type="checkbox" name="wp_roadmap_settings[hide_display_ideas_heading]" id="hide_display_ideas_heading" value="1" ' . $hide_display_ideas_heading_checked . ' />';
	$content .= '<br/>';

		// Add JavaScript to toggle the visibility of the label and input field based on checkbox state
		$content .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var checkbox = document.getElementById("hide_display_ideas_heading");
            var label = document.querySelector("label[for=\'custom_display_ideas_heading\']");
            var input = document.querySelector("input[name=\'wp_roadmap_settings[custom_display_ideas_heading]\']");

            checkbox.addEventListener("change", function() {
                if (checkbox.checked) {
                    label.style.display = "none";
                    input.style.display = "none";
                } else {
                    label.style.display = "inline";
                    input.style.display = "inline";
                }
            });

            // Initialize visibility based on the initial checkbox state
            if (checkbox.checked) {
                label.style.display = "none";
                input.style.display = "none";
            }
        });
    </script>';

	$content .= '<label for="custom_display_ideas_heading">Custom Heading: </label>';
	$content .= '<input type="text" name="wp_roadmap_settings[custom_display_ideas_heading]" value="' . esc_attr( $new_display_ideas_heading ) . '" />';

	return $content;
}

add_filter( 'wp_roadmap_hide_display_ideas_heading_setting', __NAMESPACE__ . '\\hide_display_ideas_heading_setting' );
