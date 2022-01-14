<?php
/**
 * A very early error message displayed if initialization failed.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core
 *
 * @var     InitializationFailureException  $error
 * @var     AbstractPluginFunctionalityRoot $plugin
 * @var     array                           $args
 */

use DeepWebSolutions\Framework\Core\AbstractPluginFunctionalityRoot;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use function DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_support_email;
use function DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_support_url;

defined( 'ABSPATH' ) || exit;
?>

<?php do_action( 'dws_wp_framework/core/initialization_error/before', $error, $plugin, $args ); ?>

<div class="error notice dws-plugin-initialization-error">
	<?php do_action( 'dws_wp_framework/core/initialization_error/start', $error, $plugin, $args ); ?>

	<p>
		<?php
		echo wp_kses_post(
			wp_sprintf(
				/* translators: 1. Plugin name, 2. Plugin version, 3. Support email, 4. Support website */
				__( '<strong>%1$s (v%2$s)</strong> initialization failed. Please contact us at <strong><a href="mailto:%3$s">%3$s</a></strong> or visit our <strong><a href="%4$s" target="_blank">support website</a></strong> to get help. Please include this error notice in your support query:', 'dws-wp-framework-core' ),
				$plugin->get_plugin_name(),
				$plugin->get_plugin_version(),
				dws_wp_framework_get_whitelabel_support_email(),
				dws_wp_framework_get_whitelabel_support_url()
			)
		);
		?>
	</p>

	<?php do_action( 'dws_wp_framework/core/initialization_error_list/before', $error, $plugin, $args ); ?>

	<ul class="ul-disc">
		<?php do_action( 'dws_wp_framework/core/initialization_error_list/start', $error, $plugin, $args ); ?>

		<li>
			<?php echo wp_kses_post( $error->getMessage() ); ?>
		</li>

		<?php do_action( 'dws_wp_framework/core/initialization_error_list/end', $error, $plugin, $args ); ?>
	</ul>

	<?php do_action( 'dws_wp_framework/core/initialization_error_list/after', $error, $plugin, $args ); ?>

	<?php do_action( 'dws_wp_framework/core/initialization_error/end', $error, $plugin, $args ); ?>
</div>

<?php do_action( 'dws_wp_framework/core/initialization_error/after', $error, $plugin, $args ); ?>
