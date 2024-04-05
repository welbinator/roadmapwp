<?php
/**
 * This file contains filters to modify the idea heading settings in RoadMapWP Free.
 *
 * @package RoadMapWP\Free\Settings\SubmitIdeaCustomHeading
 */

namespace RoadMapWP\Free\Settings\SubmitIdeaCustomHeading;

/**
 * Determines whether to hide the custom idea heading based on settings.
 *
 * @param bool $hide_submit_idea_heading Current hide setting.
 * @return bool Updated hide setting.
 */
function hide_custom_idea_heading( $hide_submit_idea_heading ) {
	$options = get_option( 'wp_roadmap_settings', array() );
	return ! empty( $options['hide_custom_idea_heading'] );
}

add_filter( 'wp_roadmap_hide_custom_idea_heading', __NAMESPACE__ . '\\hide_custom_idea_heading' );

/**
 * Retrieves custom idea heading text based on settings.
 *
 * @param string $default_heading Default heading text.
 * @return string Updated heading text.
 */
function custom_idea_heading_text( $default_heading ) {
	$options = get_option( 'wp_roadmap_settings', array() );
	return ! empty( $options['custom_idea_heading'] ) ? $options['custom_idea_heading'] : $default_heading;
}

add_filter( 'wp_roadmap_custom_idea_heading_text', __NAMESPACE__ . '\\custom_idea_heading_text' );

/**
 * Adds new heading text field in settings.
 *
 * @param string $content Existing content.
 * @return string Modified content with new settings.
 */
function hide_custom_idea_heading_setting( $content ) {
	$options                          = get_option( 'wp_roadmap_settings', array() );
	$hide_submit_idea_heading_checked = ! empty( $options['hide_custom_idea_heading'] ) ? 'checked' : '';
	$new_submit_idea_heading          = isset( $options['custom_idea_heading'] ) ? $options['custom_idea_heading'] : '';

	// Checkbox for hiding the custom heading
	$content  = '<label>Hide Heading: </label>';
	$content .= '<input type="checkbox" name="wp_roadmap_settings[hide_custom_idea_heading]" id="hide_custom_idea_heading" value="1" ' . $hide_submit_idea_heading_checked . ' />';
	$content .= '<br/>';

	// JavaScript for toggling the visibility of the custom heading input
	$content .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var checkbox = document.getElementById("hide_custom_idea_heading");
        var label = document.querySelector("label[for=\'custom_idea_heading\']");
        var input = document.querySelector("input[name=\'wp_roadmap_settings[custom_idea_heading]\']");

        function toggleInput() {
            if (checkbox.checked) {
                label.style.display = "none";
                input.style.display = "none";
            } else {
                label.style.display = "inline";
                input.style.display = "inline";
            }
        }

        checkbox.addEventListener("change", toggleInput);
        toggleInput(); // Initialize on page load
    });
    </script>';

	// Input field for custom heading
	$content .= '<label for="custom_idea_heading">Custom Heading: </label>';
	$content .= '<input type="text" name="wp_roadmap_settings[custom_idea_heading]" value="' . esc_attr( $new_submit_idea_heading ) . '" />';

	return $content;
}
add_filter( 'wp_roadmap_hide_custom_idea_heading_setting', __NAMESPACE__ . '\\hide_custom_idea_heading_setting' );
