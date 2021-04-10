<?php
/**
 * A message displayed before the very first installation.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\templates\installation
 *
 * @var     \DeepWebSolutions\Framework\Core\PluginComponents\Installation      $this       Instance of the installation action.
 */

defined( 'ABSPATH' ) || exit;
?>

<p id="dws-install-<?php echo esc_attr( $this->get_plugin()->get_plugin_slug() ); ?>">
	<?php
	echo wp_kses(
		sprintf(
			/* translators: 1. Plugin name, 2. Plugin version, 3. Name of the install button */
			__( '<strong>%1$s (v%2$s)</strong> needs to run its installation routine before it can be used. Please click the "%3$s" button to proceed:', 'dws-wp-framework-core' ),
			$this->get_plugin()->get_plugin_name(),
			$this->get_plugin()->get_plugin_version(),
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
	<button class="button button-primary button-large dws-install" aria-describedby="dws-install-<?php echo esc_attr( $this->get_plugin()->get_plugin_slug() ); ?>">
		<?php esc_html_e( 'Install', 'dws-wp-framework-core' ); ?>
	</button>
</p>
