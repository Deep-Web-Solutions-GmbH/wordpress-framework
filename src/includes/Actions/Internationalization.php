<?php

namespace DeepWebSolutions\Framework\Core\Actions;

use DeepWebSolutions\Framework\Core\Abstracts\Functionality;
use DeepWebSolutions\Framework\Core\Traits\Setup\Hooks;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the registration of translations and other i18n actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\WP-Framework\Core\Actions
 */
class Internationalization extends Functionality {
	use Hooks;

	// region INHERITED FUNCTIONS

	/**
	 * Runs WP internationalization hooks.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Hooks::register_hooks()
	 *
	 * @param   HooksHandler    $hooks_handler      Instance of the hooks handler.
	 */
	protected function register_hooks( HooksHandler $hooks_handler ): void {
		$hooks_handler->add_action( 'plugins_loaded', $this, 'load_plugin_textdomain', 100 );
	}

	// endregion

	// region HOOKS

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool    True when textdomain is successfully loaded, false otherwise.
	 */
	public function load_plugin_textdomain(): bool {
		return load_plugin_textdomain(
			$this->get_plugin()->get_plugin_language_domain(),
			false,
			str_replace( WP_PLUGIN_DIR, '', $this->get_plugin()::get_languages_base_path() )
		);
	}

	// endregion
}
