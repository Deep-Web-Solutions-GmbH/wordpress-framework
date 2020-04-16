<?php
/**
 * A very early error message displayed if environment requirements are not met.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\wordpress-framework\admin
 *
 * @see     dws_wp_framework_requirements_error
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="error notice">
	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: DWS WP Framework Name */
				__( 'It seems like %s is corrupted. Please reinstall!', 'dws-wp-framework' ),
				DWS_WP_FRAMEWORK_NAME
			)
		);
		?>
	</p>
</div>