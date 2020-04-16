<?php
/**
 * A very early error message displayed if environment requirements are not met.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\wordpress-framework\admin
 *
 * @see     dws_wp_framework_output_installation_error
 *
 * @var     string  $component_name
 * @var     array   $args
 */

defined( 'ABSPATH' ) || exit;

?>

<?php do_action( 'dws_wp_framework_installation_error_before', $component_name, $args ); ?>

<div class="error notice">
	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: DWS WP Framework Name */
				__( 'It seems like %s is corrupted. Please reinstall!', 'dws-wp-framework' ),
				$component_name
			)
		);
		?>
	</p>
</div>

<?php do_action( 'dws_wp_framework_installation_error_after', $component_name, $args ); ?>
