<?php
/**
 * A very early error message displayed if a child plugin fails to initialize if the environment requirements of the
 * framework are not met.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\wordpress-framework\admin
 *
 * @see     dws_wp_framework_output_child_requirements_error
 *
 * @var     string  $child_plugin_name
 * @var     array   $args
 */

defined( 'ABSPATH' ) || exit;

?>

<?php do_action( 'dws_wp_framework_child_requirements_error_before', $child_plugin_name, $args ); ?>

<div class="error notice">
    <p>
		<?php
		echo esc_html(
			sprintf(
			/* translators: %s: DWS Child Plugin Name */
				__( 'It seems like %s failed to load due to a dependency failure.', 'dws-wp-framework' ),
				$child_plugin_name
			)
		);
		?>
    </p>
</div>

<?php do_action( 'dws_wp_framework_child_requirements_error_after', $child_plugin_name, $args ); ?>
