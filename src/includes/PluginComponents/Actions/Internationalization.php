<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents\Actions;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterInterface;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the registration of translations and other i18n actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents\Actions
 */
class Internationalization extends AbstractPluginFunctionality implements HooksServiceRegisterInterface {
	// region TRAITS

	use HooksServiceRegisterTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers actions and filters with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
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
			\str_replace( WP_PLUGIN_DIR, '', $this->get_plugin()::get_languages_base_path() )
		);
	}

	// endregion
}
