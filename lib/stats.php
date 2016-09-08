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
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widgets' ) );
		add_filter( 'example_cta_html_display', array( $this, 'record_stats' ), 10, 2 );

		add_action( 'rest_api_init', array( $this, 'register_routes' ), 10, 2 );
	}

	public function register_routes() {
		$version = '1';
		$namespace = 'examplecta/v' . $version;
		$base = 'stats';
		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_stats_endpoint' ),
				'permission_callback' => array( $this, 'get_stats_permissions_check' ),
				'args'            => array(
					'detailed' => array(
						'required' => false,
					),
				),
			),
		) );
	}

	public function get_stats_permissions_check() {
		return true; // Publicly available (even to logged out users)
	}

	public function get_stats_endpoint( $request ) {
		$params = $request->get_params();

		$stats = $this->get_stats();

		return $stats;
	}

	/**
	 * Get current stats from database
	 *
	 * @return void
	 */
	public function get_stats() {
		// Get our option from the database, or use an empty array if it isn't found in the database
		$stats = get_option( 'example_cta_stats', array() );

		// Make sure we use our defaults if any values aren't set already
		$stats = array_merge( $this->default_stats, $stats );
		return $stats;
	}

	/**
	 * Record some stats each time CTA is displayed
	 *
	 * @return void
	 */
	public function record_stats( $build, $post_id ) {
		// Load the current stats
		$stats = $this->get_stats();

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

	public function add_dashboard_widgets() {
		wp_add_dashboard_widget(
			'example_cta_stats',         // Widget slug.
			'Example CTA Stats',         // Title.
			array( $this, 'display_dashboard_widget' ) // Display function.
		);	
	}

	public function display_dashboard_widget() {
		$stats = $this->get_stats();

		echo "<table>";
			echo "<tbody>";
				foreach ($stats as $stat_key => $stat_value) {
					echo "<tr style='padding: 8px 4px;'>";
						echo "<th style='text-align: right;'>";
							echo $stat_key;
						echo "</th>";

						echo "<td style='text-align: left;'>";
							if ( is_array( $stat_value ) ) {
								echo '<pre>'.print_r($stat_value, true).'</pre>';
							} else {
								echo $stat_value;
							}
						echo "</td>";
					echo "</tr>";
				}
			echo "</tbody>";
		echo "</table>";
	}

	// End the class.
}

// Instantiate our class.
$EXCTA_Stats = new EXCTA_Stats();
$EXCTA_Stats->init();
