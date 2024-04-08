<?php
/**
 * RoadMapWP Pro Plugin - Roadmap Tabs Shortcode
 *
 * This file contains the shortcode [roadmap_tabs] which is used to display
 * a tabbed interface for roadmap statuses in the RoadMapWP Pro plugin.
 *
 * @package RoadMapWP\Free\Shortcodes\RoadmapTabs
 */

namespace RoadMapWP\Free\Shortcodes\RoadmapTabs;

/**
 * Shortcode to display roadmap tabs.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for the roadmap tabs.
 */
function roadmap_tabs_shortcode( $atts ) {

	$user_id = get_current_user_id();
	$display_shortcode = true;
    $display_shortcode = apply_filters('roadmapwp_pro_roadmap_tabs_shortcode', $display_shortcode, $user_id);

    if (!$display_shortcode) {
        return '';
    }
	
	$atts = shortcode_atts(
		array(
			'status'        => '',
			'showNewIdea'   => true,
			'showUpNext'    => true,
			'showMaybe'     => true,
			'showOnRoadmap' => true,
			'showClosed'    => true,
			'showNotNow'    => true,
		),
		$atts,
		'roadmap-tabs'
	);

	// Assume true if the attribute is not passed.
	$statuses = array();
	if ( ! empty( $atts['status'] ) ) {
		// Use the 'idea-status' attribute if it's provided (for the shortcode)
		$statuses = array_map( 'trim', explode( ',', $atts['status'] ) );
	} else {
		// Otherwise, use the boolean attributes (for the block)
		if ( $atts['showNewIdea'] ) {
			$statuses[] = 'New Idea';
		}
		if ( $atts['showUpNext'] ) {
			$statuses[] = 'Up Next';
		}
		if ( $atts['showMaybe'] ) {
			$statuses[] = 'Maybe';
		}
		if ( $atts['showOnRoadmap'] ) {
			$statuses[] = 'On Roadmap';
		}
		if ( $atts['showClosed'] ) {
			$statuses[] = 'Closed';
		}
		if ( $atts['showNotNow'] ) {
			$statuses[] = 'Not Now';
		}
	}

	$options = get_option( 'wp_roadmap_settings' );
	
	ob_start();
	?>

	<!-- Tabbed interface -->
	<div dir="ltr" data-orientation="horizontal" class="w-full border-b roadmap-tabs-wrapper">
		<div role="tablist" aria-orientation="horizontal" class="h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground flex gap-5 px-2 py-6 scrollbar-none roadmap-tabs">
			<?php foreach ( $statuses as $status ) : ?>
				<button type="button" role="tab" aria-selected="true" aria-controls="radix-:r3a:-content-newIdea" data-state="inactive" id="radix-:r3a:-trigger-newIdea" class="inline-flex items-center justify-center whitespace-nowrap rounded-md px-3 py-1 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:text-foreground data-[state=active]:shadow roadmap-tab" data-status="<?php echo esc_attr( $status ); ?>">
					<?php 
						printf(
							/* translators: %s: Status of idea */
							esc_html__( '%s', 'roadmapwp-free' ),
							esc_html( $status )
						); 
					?>
				</button>
			<?php endforeach; ?>
		</div>
		<div
			data-state="active"
			data-orientation="horizontal"
			role="tabpanel"
			aria-labelledby="radix-:r3a:-trigger-newIdea"
			id="radix-:r3a:-content-newIdea"
			tabindex="0"
			class="mt-2 ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
			style="animation-duration: 0s;"
		>
		<div class="grid md:grid-cols-2 gap-4 mt-2 roadmap-ideas-container">
			<!-- Ideas will be loaded here via JavaScript -->
		</div>
	</div>

	<?php

	return ob_get_clean(); // Return the buffered output
}
add_shortcode( 'roadmap_tabs', __NAMESPACE__ . '\\roadmap_tabs_shortcode' );
