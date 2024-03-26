<?php
/**
 * This file adds functionality to enable or disable comments on ideas in the RoadMapWP Free plugin.
 *
 * @package RoadMapWP\Free\Settings\Comments
 */

namespace RoadMapWP\Free\Settings\Comments;

/**
 * Enables or disables comments on ideas.
 *
 * @param string $content Existing content or settings HTML.
 * @return string Modified HTML with the checkbox for enabling comments.
 */
function enable_comments( $content ) {
	$options        = get_option( 'wp_roadmap_settings', array() );
	$allow_comments = isset( $options['allow_comments'] ) ? $options['allow_comments'] : '';

	$html = '<input type="checkbox" name="wp_roadmap_settings[allow_comments]" value="1"' . checked( 1, $allow_comments, false ) . '/>';
	return $html;
}

add_filter( 'wp_roadmap_enable_comments_setting', __NAMESPACE__ . '\\enable_comments' );
