<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents\Actions;

use DeepWebSolutions\Framework\Core\PluginComponents\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableLocalTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the registration of translations and other i18n actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents\Actions
 */
class Internationalization extends AbstractPluginFunctionality {
	// region TRAITS

	use InitializableLocalTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * Registers the plugin's textdomain with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  Exception  Thrown if the node does NOT belong to a plugin tree.
	 *
	 * @return  InitializationFailureException|null
	 */
	protected function initialize_local(): ?InitializationFailureException {
		load_plugin_textdomain(
			$this->get_plugin()->get_plugin_language_domain(),
			false,
			str_replace( WP_PLUGIN_DIR, '', $this->get_plugin()::get_languages_base_path() )
		);

		return null;
	}

	// endregion
}
