<?php
/**
 * A message displayed before the very first installation.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\templates\installation
 *
 * @var     array       $args       Array of arguments passed on to the template.
 */

defined( 'ABSPATH' ) || exit;
?>

<p id="dws-install-<?php echo esc_attr( $args['plugin']->get_plugin_slug() ); ?>">
	<?php
	echo wp_kses(
		sprintf(
			/* translators: 1. Plugin name, 2. Plugin version, 3. Name of the install button */
			__( '<strong>%1$s (v%2$s)</strong> needs to run its installation routine before it can be used. Please click the "%3$s" button to proceed:', 'dws-wp-framework-core' ),
			$args['plugin']->get_plugin_name(),
			$args['plugin']->get_plugin_version(),
			/* translators: Name of the install button */
			__( 'Install', 'dws-wp-framework-core' )
		),
		array(
			'strong' => array(),
		)
	);
	?>
</p>
<p>
	<button class="button button-primary button-large dws-install" aria-describedby="dws-install-<?php echo esc_attr( $args['plugin']->get_plugin_slug() ); ?>">
		<?php esc_html_e( 'Install', 'dws-wp-framework-core' ); ?>
	</button>
</p>
