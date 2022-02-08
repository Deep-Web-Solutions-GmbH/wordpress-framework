<?php

namespace DeepWebSolutions\Framework\Core\Functionalities;

use DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\Integrations\SetupableDisabledTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\Integrations\SetupableInactiveTrait;
use DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterInterface;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the registration of translations and other i18n actions.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Functionalities
 */
class InternationalizationFunctionality extends AbstractPluginFunctionality implements HooksServiceRegisterInterface {
	// region TRAITS

	use SetupableDisabledTrait;
	use SetupableInactiveTrait;
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
		$hooks_service->add_action( 'init', $this, 'load_plugin_textdomain', 0, 0, 'direct' );
	}

	// endregion

	// region HOOKS

	/**
	 * Loads the plugin's MO files.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function load_plugin_textdomain(): void {
		$plugin            = $this->get_plugin();
		$plugin_textdomain = $plugin->get_plugin_language_domain();
		$plugin_rel_path   = \str_replace( WP_PLUGIN_DIR, '', $plugin::get_plugin_languages_path( true ) );

		// For plugins with premium versions that have the same textdomain as the free version hosted on WordPress.org,
		// we must use this hack to basically force-load the bundled MO files first such that the premium strings stay translated.
		$func = function( string $mofile, string $domain ) use ( $plugin_textdomain, $plugin_rel_path ) {
			if ( $domain === $plugin_textdomain && false === Strings::starts_with( $mofile, $plugin_rel_path ) ) {
				$mofile = '';
			}

			return $mofile;
		};
		\add_filter( 'load_textdomain_mofile', $func, 9999, 2 );
		\load_plugin_textdomain( $plugin_textdomain, false, $plugin_rel_path );
		\remove_filter( 'load_textdomain_mofile', $func, 9999 );

		// Load the MO files from the WP_LANG_DIR directory.
		\load_plugin_textdomain( $plugin_textdomain, false, $plugin_rel_path );
	}

	// endregion
}
