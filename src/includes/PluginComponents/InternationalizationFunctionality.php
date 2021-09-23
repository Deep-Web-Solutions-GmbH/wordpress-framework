<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Plugin\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterInterface;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the registration of translations and other i18n actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
class InternationalizationFunctionality extends AbstractPluginFunctionality implements HooksServiceRegisterInterface {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region MAGIC METHODS

	/**
	 * {@inheritDoc}
	 */
	public function __construct( LoggingService $logging_service, ?string $component_id = null, ?string $component_name = null ) {
		parent::__construct( $logging_service, $component_id ?: 'internationalization', $component_name ?: 'Internationalization' );
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'init', $this, 'load_plugin_textdomain' );
	}

	// endregion

	// region HOOKS

	/**
	 * Registers the plugin's textdomain with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function load_plugin_textdomain(): void {
		\load_plugin_textdomain(
			$this->get_plugin()->get_plugin_language_domain(),
			false,
			\str_replace( WP_PLUGIN_DIR, '', $this->get_plugin()::get_plugin_languages_base_path() )
		);
	}

	// endregion
}
