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

	/**
	 * Display our various postmeta inputs for a custom CTA.
	 *
	 * @param  object $post  The global post object.
	 *
	 * @return void
	 */
	public function postmeta( $post ) {

		// Fetch our postmeta items.
		$place  = EXCTA_Helper::get_single_postmeta( $post->ID, '_example_cta_postmeta', 'below', 'place' );
		$title  = EXCTA_Helper::get_single_postmeta( $post->ID, '_example_cta_postmeta', '', 'title' );
		$text   = EXCTA_Helper::get_single_postmeta( $post->ID, '_example_cta_postmeta', '', 'text' );

		// Our disable flag.
		$check  = EXCTA_Helper::get_single_postmeta( $post->ID, '_example_cta_post_disable' );

		// Set a class on our rows for when we're disabled.
		$class  = ! empty( $check ) ? 'example-cta-single-row example-cta-single-row-hidden' : 'example-cta-single-row';

		// Fetch our arguments for the WP_Editor call.
		$editor = EXCTA_Helper::get_wp_editor_args( 'example-cta-post[text]' );

		// Begin building out the table boxes.
		echo '<table class="example-cta-postmeta-table form-table">';
		echo '<tbody>';

			// Our checkbox to disable the whole thing.
			echo '<tr>';

				// The field label.
				echo '<th scope="row">';
					echo '<label>' . esc_html__( 'Disable CTA', 'example-cta-plugin' ) . '</label>';
				echo '</th>';

				// The input field.
				echo '<td>';
					echo '<label for="example-cta-post-disable">';
					echo '<input type="checkbox" name="example-cta-post-disable" id="example-cta-post-disable" value="1" ' . checked( $check, 1, false ) . '>';
					echo ' <em>' . esc_html__( 'Check this box to disable the CTA on this post.', 'example-cta-plugin' ) . '</em></label>';
				echo '</td>';

			// Close our checkbox field.
			echo '</tr>';

			// Our placement radio field.
			echo '<tr class="' . esc_attr( $class ) . '">';

				// The field label.
				echo '<th scope="row">';
					echo '<label>' . esc_html__( 'Placement', 'example-cta-plugin' ) . '</label>';
				echo '</th>';

				// The input field.
				echo '<td>';

					echo '<span class="example-cta-radio">';
						echo '<label for="example-cta-post-place-above">';
						echo '<input type="radio" id="example-cta-post-place-above" name="example-cta-post[place]" value="above" ' . checked( $place, 'above', false ) . ' />';
						echo ' ' . esc_html__( 'Place above post content.', 'example-cta-plugin' ) . '</label>';
					echo '</span>';

					echo '<span class="example-cta-radio">';
						echo '<label for="example-cta-post-place-below">';
						echo '<input type="radio" id="example-cta-post-place-below" name="example-cta-post[place]" value="below" ' . checked( $place, 'below', false ) . ' />';
						echo ' ' . esc_html__( 'Place below post content.', 'example-cta-plugin' ) . '</label>';
					echo '</span>';

					echo '<span class="example-cta-radio">';
						echo '<label for="example-cta-post-place-manual">';
						echo '<input type="radio" id="example-cta-post-place-manual" name="example-cta-post[place]" value="manual" ' . checked( $place, 'manual', false ) . ' />';
						echo ' ' . esc_html__( 'Manually insert post content via shortcode.', 'example-cta-plugin' ) . '</label>';
					echo '</span>';

				echo '</td>';

			// Close our placement radio field.
			echo '</tr>';

			// Our title input field.
			echo '<tr class="' . esc_attr( $class ) . '">';

				// The field label.
				echo '<th scope="row">';
					echo '<label for="example-cta-post-title">' . esc_html__( 'CTA Title', 'example-cta-plugin' ) . '</label>';
				echo '</th>';

				// The input field.
				echo '<td>';
					echo '<input type="text" id="example-cta-post-title" class="widefat" name="example-cta-post[title]" value="' . esc_attr( $title ) . '" />';
				echo '</td>';

			// Close our title field.
			echo '</tr>';

			// Our text input field.
			echo '<tr class="' . esc_attr( $class ) . '">';

				// The field label.
				echo '<th scope="row">';
					echo '<label for="example-cta-post-text">' . esc_html__( 'CTA Text', 'example-cta-plugin' ) . '</label>';
				echo '</th>';

				// The input field.
				echo '<td>';
					wp_editor( $text, 'squares_post_text', $editor );
				echo '</td>';

			// Close our title field.
			echo '</tr>';

			// Call our action to include any extra settings.
			do_action( 'example_cta_postmeta_box', $post->ID, $post );

			// Use nonce for verification.
			wp_nonce_field( 'example_cta_post_nonce', 'example_cta_post_nonce', false, true );

		// end table
		echo '</tbody>';
		echo '</table>';
	}

	/**
	 * Update the meta value for the single view checkbox.
	 *
	 * @param  integer $post_id  The post ID being passed on save.
	 *
	 * @return void
	 */
	function save_postmeta( $post_id ) {

		// Bail out if running an autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Bail out if running an ajax request.
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return;
		}

		// Bail out if running a cron.
		if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
			return;
		}

		// Make sure the current user has the capability to change
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		// Do our nonce comparison.
		if ( empty( $_POST['example_cta_post_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['example_cta_post_nonce'] ), 'example_cta_post_nonce' ) ) {
			return;
		}

		// Make sure we have our actual meta coming through.
		if ( empty( $_POST['example-cta-post'] ) && empty( $_POST['example-cta-post-disable'] ) ) {
			return;
		}

		// Check against the post types we've allowed.
		if ( ! in_array( get_post_type( $post_id ), EXCTA_Helper::get_supported_types() ) ) {
			return;
		}

		// First check for the enabled / disabled loading.
		if ( ! empty( $_POST['example-cta-post-disable'] ) ) {
			update_post_meta( $post_id, '_example_cta_post_disable', sanitize_key( $_POST['example-cta-post-disable'] ) );
		} else {
			delete_post_meta( $post_id, '_example_cta_post_disable' );
		}

		// Set an empty data array for sanitizing prior to saving.
		$data   = array();

		// Look for our posted title.
		if ( ! empty( $_POST['example-cta-post']['title'] ) ) {
			$data['title']  = sanitize_text_field( trim( $_POST['example-cta-post']['title'] ) );
		}

		// Look for our posted text.
		if ( ! empty( $_POST['example-cta-post']['text'] ) ) {
			$data['text']   = wp_kses_post( trim( $_POST['example-cta-post']['text'] ) );
		}

		// Sanitize the placement radio input.
		$data['place']  = ! empty( $_POST['example-cta-post']['place'] ) ? sanitize_text_field( $_POST['example-cta-post']['place'] ) : 'below';

		// And filter our data to allow additional settings to be added later.
		$data   = apply_filters( 'example_cta_postmeta_sanitize', $data );

		// Now store it (or delete it).
		if ( ! empty( $data ) ) {
			update_post_meta( $post_id, '_example_cta_postmeta', $data );
		} else {
			delete_post_meta( $post_id, '_example_cta_postmeta' );
		}
	}

	// End the class.
}

// Instantiate our class.
$EXCTA_Stats = new EXCTA_Stats();
$EXCTA_Stats->init();
