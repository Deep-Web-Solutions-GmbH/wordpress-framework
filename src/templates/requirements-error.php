<?php
/**
 * A very early error message displayed if environment requirements are not met.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\wordpress-framework\admin
 *
 * @see     dws_wp_framework_output_requirements_error
 *
 * @var     string  $component_name
 * @var     string  $min_php_version
 * @var     string  $min_wp_version
 */

defined( 'ABSPATH' ) || exit;

?>

<?php do_action('dws_wp_framework_requirements_error_before'); ?>

<div class="error notice">
	<p>
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: DWS WP Framework Name */
				__( '%s error: Your environment doesn\'t meet all of the system requirements listed below.', 'dws-wp-framework' ),
				$component_name
			)
		);
		?>
	</p>

	<ul class="ul-disc">
		<?php do_action('dws_wp_framework_requirements_error_list_before'); ?>

		<li>
			<strong>PHP <?php echo esc_html( $min_php_version ); ?>+</strong>
			<em><?php echo esc_html( sprintf( /* translators: %s: PHP version */ __( 'You\'re running version %s', 'dws-wp-framework' ), PHP_VERSION ) ); ?></em>
		</li>
		<li>
			<strong>WordPress <?php echo esc_html( $min_wp_version ); ?>+</strong>
			<em><?php echo esc_html( sprintf( /* translators: %s: WordPress version */ __( 'You\'re running version %s', 'dws-wp-framework' ), $GLOBALS['wp_version'] ) ); ?></em>
		</li>

		<?php do_action('dws_wp_framework_requirements_error_list_after'); ?>
	</ul>

	<p>
		<?php
		esc_html_e(
			'If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.',
			'dws-wp-framework'
		);
		?>
	</p>
</div>

<?php do_action('dws_wp_framework_requirements_error_after'); ?>