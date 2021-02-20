<?php

namespace DeepWebSolutions\Framework\Core\Actions;

use DeepWebSolutions\Framework\Core\Abstracts\PluginFunctionality;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\InitializationFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable\InitializeLocal;
use DeepWebSolutions\Framework\Core\Traits\Setup\Hooks;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the registration of translations and other i18n actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions
 */
class Internationalization extends PluginFunctionality {
	use InitializeLocal;

	// region INHERITED METHODS

	/**
	 * Registers the plugin's textdomain with WordPress.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  Exception  Thrown if the node does NOT belong to a plugin tree.
	 *
	 * @return  InitializationFailure|null
	 */
	protected function initialize_local(): ?InitializationFailure {
		load_plugin_textdomain(
			$this->get_plugin()->get_plugin_language_domain(),
			false,
			str_replace( WP_PLUGIN_DIR, '', $this->get_plugin()::get_languages_base_path() )
		);

		return null;
	}

	// endregion
}
