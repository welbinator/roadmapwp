<?php
/**
 * RoadMapWP Pro Plugin - Roadmap Tabs Shortcode
 *
 * This file contains the shortcode [roadmap_tabs] which is used to display
 * a tabbed interface for roadmap statuses in the RoadMapWP Pro plugin.
 */

namespace RoadMapWP\Free\Shortcodes\RoadmapTabs;

/**
 * Shortcode to display roadmap tabs.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output for the roadmap tabs.
 */
function roadmap_tabs_shortcode( $atts ) {
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

	// Assume true if the attribute is not passed
	$statuses = array();
	if ( ! empty( $atts['status'] ) ) {
		// Use the 'status' attribute if it's provided (for the shortcode)
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
					<?php printf(
						esc_html__( '%s', 'roadmapwp-free' ),
						esc_html( $status )
					); ?>
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

	<script type="text/javascript">
	document.addEventListener('DOMContentLoaded', function() {
		var tabs = document.querySelectorAll('.roadmap-tab');
		var ideasContainer = document.querySelector('.roadmap-ideas-container');
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
		var nonce = '<?php echo wp_create_nonce( 'roadmap_nonce' ); ?>';
		console.log(nonce);

		// Function to reset all tabs to inactive
		function resetTabs() {
			tabs.forEach(function(tab) {
				tab.setAttribute('data-state', 'inactive');
			});
		}

		tabs.forEach(function(tab) {
			tab.addEventListener('click', function() {
				resetTabs();
				this.setAttribute('data-state', 'active');

				var status = this.getAttribute('data-status');
				loadIdeas(status);
			});
		});

		function loadIdeas(status) {
			var formData = new FormData();
			formData.append('action', 'load_ideas_for_status');
			formData.append('status', status);
			formData.append('nonce', nonce);

			fetch(ajaxurl, {
				method: 'POST',
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				
				if (data.success && data.data && data.data.html) {
					ideasContainer.innerHTML = data.data.html;
				} else {
					ideasContainer.innerHTML = '<p>Error: Invalid response format.</p>';
				}
			})
			.catch(error => {
				console.error('Error loading ideas:', error);
				ideasContainer.innerHTML = '<p>Error loading ideas.</p>';
			});
		}

		if (tabs.length > 0) {
			tabs[0].click();
			tabs[0].setAttribute('data-state', 'active');
		}
	});
</script>



	<?php

	return ob_get_clean(); // Return the buffered output
}
add_shortcode( 'roadmap_tabs', __NAMESPACE__ . '\\roadmap_tabs_shortcode' );
