<?php
/**
 * A very early error message displayed if the plugin initialization failed.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\templates
 *
 * @see     \DeepWebSolutions\Framework\Core\Abstracts\PluginBase::initialize()
 *
 * @var     PluginInitializationFailure     $error
 * @var     PluginBase                      $plugin
 * @var     array                           $args
 */

use DeepWebSolutions\Framework\Core\Abstracts\PluginBase;
use DeepWebSolutions\Framework\Core\Exceptions\PluginInitializationFailure;
use function DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_support_email;
use function DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_support_url;

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'dws_wp_framework_plugin_initialization_error_before', $error, $plugin, $args ); ?>

<div class="error notice dws-plugin-initialization-error">
	<?php do_action( 'dws_wp_framework_plugin_initialization_error_start', $error, $plugin, $args ); ?>

	<p>
		<?php
		echo wp_kses(
			sprintf(
				/* translators: 1: Support email, 2: Support website */
				__( 'Plugin initialization failed. Please contact us at <strong><a href="mailto:%1$s">%1$s</a></strong> or visit our <strong><a href="%2$s" target="_blank">support website</a></strong> to get help. Please include this error notice in your support query:', 'dws-wp-framework-core' ),
				dws_wp_framework_get_whitelabel_support_email(),
				dws_wp_framework_get_whitelabel_support_url()
			),
			array(
				'strong' => array(),
				'a'      => array(
					'href'   => array(),
					'target' => array(),
				),
			)
		);
		?>
	</p>

	<ul class="ul-disc">
		<?php do_action( 'dws_wp_framework_plugin_initialization_error_list_before', $error, $plugin, $args ); ?>

		<li>
			<?php echo esc_html( $error->getMessage() ); ?>
		</li>

		<?php do_action( 'dws_wp_framework_plugin_initialization_error_list_after', $error, $plugin, $args ); ?>
	</ul>

	<?php do_action( 'dws_wp_framework_plugin_initialization_error_end', $error, $plugin, $args ); ?>
</div>

<?php do_action( 'dws_wp_framework_plugin_initialization_error_after', $error, $plugin, $args ); ?>
