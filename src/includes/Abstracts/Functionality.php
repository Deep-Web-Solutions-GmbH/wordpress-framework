<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Utilities\Loader;
use Psr\Log\LoggerInterface;

defined( 'ABSPATH' ) || exit;

/**
 * A template for encapsulating all the piping required for a DWS functionality.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\Abstracts
 *
 * @see     Root
 */
abstract class Functionality extends Root {
    // region FIELDS AND CONSTANTS

    private static array $functionalities_by_id = array();

    private static array $functionalities_by_name = array();

    private static array $parent_functionality = array();

    private static array $children_functionalities = array();

    private static array $must_use = array();

    protected int $functionality_depth;

    private string $description;

    // endregion

	// region MAGIC METHODS

	/**
	 * Functionality constructor.
	 *
	 * @param   Loader          $loader             Instance of the hooks and shortcodes loader.
	 * @param   LoggerInterface $logger             Instance of the PSR-3-compatible logger used throughout out plugin.
	 * @param   string|false    $functionality_id   The unique ID of the class instance. Must be persistent across requests.
	 * @param   string|false    $functionality_name The 'nice_name' of the class instance. Must be persistent across requests. Mustn't be unique.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct( Loader $loader, LoggerInterface $logger, $functionality_id = false, $functionality_name = false ) {
		parent::__construct( $loader, $logger, $functionality_id, $functionality_name );

		self::$functionalities_by_id[$functionality_id] = $this;


		$this->loader->add_action( 'plugins_loaded', $this, 'configure_instance', 100 );
		$this->load_dependencies();
	}

	// endregion

    // region GETTERS

    // endregion

	// region METHODS

	/**
	 * A late sort-of-constructor. Executed when all the plugins should have finished loading to make sure that everything
	 * is available.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public function configure_instance() : void {
		$this->local_configure_instance();

		$this->loader->add_action( 'init', $this, 'init' );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_enqueue_assets' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_assets' );
	}

	/**
	 * Children classes should define their dependencies in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function load_dependencies() : void {
		/* Children classes can overwrite this. Normally, this is not needed since the auto-loader should handle it... */
	}

	/**
	 * Children classes can run their own configuration here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function local_configure_instance() : void {
		/* Children classes can overwrite this to add extra local configuration. */
	}

	/**
	 * Children classes can run their init actions in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function init() : void {
		/** Children classes should run their init actions here. */
	}

	/**
	 * Children classes should enqueue their admin-side assets in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $hook   Name of the php file currently being rendered.
	 */
	public function admin_enqueue_assets( $hook ) {
		/* Children classes can overwrite this. */
	}

	/**
	 * Children classes should enqueue their public-side assets in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function enqueue_assets() {
		/* Children classes can overwrite this. */
	}

	// endregion

	// region HELPERS

	/**
	 * Returns a meaningful probably unique name for an internal hook.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string          $name       The actual descriptor of the hook's purpose.
	 * @param   string|array    $extra      Further descriptor of the hook's purpose.
	 * @param   string          $root       Prepended to all hooks inside the same class.
	 *
	 * @return  string  The resulting internal hook.
	 */
	public static function get_hook_name( $name, $extra = array(), $root = '' ) {
		return str_replace(
			' ',
			'-',
			join(
				'_',
				array_filter(
					array_merge(
						array( static::get_plugin_name(), $root, $name ),
						is_array( $extra ) ? $extra : array( $extra )
					)
				)
			)
		);
	}

	/**
	 * Returns a meaningful potentially unique handle for an asset.
	 *
	 * @since   1.0.0
	 * @version 1.5.2
	 *
	 * @param   string  $name   The actual descriptor of the asset's purpose. Leave blank for default.
	 *
	 * @return  string  A valid asset handle.
	 */
	public static function get_asset_handle( $name = '' ) {
		return str_replace(
			' ',
			'-',
			join(
				'_',
				array_filter(
					array(
						static::get_plugin_name(),
						self::get_root_public_name(),
						$name,
					)
				)
			)
		);
	}

	// endregion
}
