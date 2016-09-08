<?php
/**
 * Example CTA Plugin - helper functions
 *
 * Contains various helper related functions.
 *
 * @package Example CTA Plugin
 */

/**
 * Set up and load our class.
 */
class EXCTA_Stats
{

	public $default_stats = array(
		'total' => 0,
		'logged_in' => 0,
		'logged_out' => 0,
		'mobile' => 0,
		'desktop' => 0,
		'total_by_post_id' => array(),
	);

	/**
	 * Load our hooks and filters.
	 *
	 * @return void
	 */
	public function init() {
		add_filter( 'example_cta_html_display', array( $this, 'record_stats' ), 10, 2 );
	}

	/**
	 * Record some stats each time CTA is displayed
	 *
	 * @return void
	 */
	public function record_stats( $build, $post_id ) {
		// Load the current stats (using defaults if values aren't present)
		$stats = get_option( 'example_cta_stats' );
		if ( empty( $stats ) ) {
			$stats = array();
		}
		$stats = array_merge( $this->default_stats, $stats );

		// Record stats
		$stats['total']++;
		if ( is_user_logged_in() ) {
			$stats['logged_in']++;
		} else {
			$stats['logged_out']++;
		}

		if ( wp_is_mobile() ) {
			$stats['mobile']++;
		} else {
			$stats['desktop']++;
		}

		if ( empty( $stats['total_by_post_id'][$post_id] ) ) {
			$stats['total_by_post_id'][$post_id] = 1;
		} else {
			$stats['total_by_post_id'][$post_id]++;
		}

		update_option( 'example_cta_stats', $stats );

		// Since this is a filter, we want to return the original value unmodified
		return $build;
	}

	// End the class.
}

// Instantiate our class.
$EXCTA_Stats = new EXCTA_Stats();
$EXCTA_Stats->init();
