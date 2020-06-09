<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\FunctionalityInitializationFailure;
use DI\Container;
use const DeepWebSolutions\Framework\Core\DWS_WP_FRAMEWORK_CORE_INIT;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\Abstracts
 *
 * @see     Functionality
 */
abstract class PluginBase extends Functionality {
	// region PROPERTIES

	/**
	 * The human-readable name of the plugin as set by the mandatory WP plugin header.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_name;

	/**
	 * The current version of the plugin as set by the mandatory WP plugin header.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_version;

	/**
	 * The name of the plugin's author as set by the mandatory WP plugin header.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_author_name;

	/**
	 * The URI of the plugin's author as set by the mandatory WP plugin header.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_author_uri;

	/**
	 * The description of the plugin as set by the mandatory WP plugin header.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_description;

	/**
	 * The slug of the plugin as deduced from the installation path.
	 *
	 * @since       1.0.0
	 * @version     1.0.0
	 *
	 * @access      private
	 * @var         string
	 */
	private string $plugin_slug;

	/**
	 * Instance of the WP Filesystem class that's to be used by the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     \WP_Filesystem_Base
	 */
	private ?\WP_Filesystem_Base $wp_filesystem = null;

	/**
	 * The system path to the main WP plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string|null
	 */
	private ?string $plugin_file_path = null;

	/**
	 * The static instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     Container|null
	 */
	private ?Container $container = null;

	// endregion

	// region WP-SPECIFIC METHODS

	/**
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public function activate() : void {

	}

	/**
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public function deactivate() : void {

	}

	// endregion

	// region GETTERS

	/**
	 * Gets the name of the plugin as set by the mandatory WP plugin header.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_name() : string {
		return $this->plugin_name;
	}

	/**
	 * Gets the (hopefully) semantic version of the plugin as set by the mandatory WP plugin header.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_version() : string {
		return $this->plugin_version;
	}

	/**
	 * Gets the name of the plugin's author as set by the mandatory WP plugin header.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_author_name() : string {
		return $this->plugin_author_name;
	}

	/**
	 * Gets the URI of the plugin's author as set by the mandatory WP plugin header.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_author_uri() : string {
		return $this->plugin_author_uri;
	}

	/**
	 * Gets the description of the plugin as set by the mandatory WP plugin header.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_description() : string {
		return $this->plugin_description;
	}

	/**
	 * Gets the slug of the plugin as deduced from the installation path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_slug() : string {
		return $this->plugin_slug;
	}

	/**
	 * Gets the instance of the WP Filesystem class that should be used by this plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  \WP_Filesystem_Base
	 */
	public function get_wp_filesystem() : \WP_Filesystem_Base {
		return $this->wp_filesystem ?? $GLOBALS['wp_filesystem'];
	}

	/**
	 * Gets the static instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Container
	 */
	public function get_container() : Container {
		if ( is_null( $this->container ) ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						/* translators: 1: Property name, 2: WP Action name */
						esc_html__( 'The %1$s cannot be retrieved before the %2$s action.', 'dws-wp-framework-core' ),
						esc_html_x( 'DI container', 'doing-it-wrong', 'dws-wp-framework-core' ),
						'plugins_loaded'
					),
					'1.0.0'
				);
			}

			return new Container(); // basically returning noop ...
		}

		return $this->container;
	}

	/**
	 * Gets the path to the main WP plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_file_path() : string {
		if ( is_null( $this->plugin_file_path ) ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						/* translators: 1: Property name, 2: WP Action name */
						esc_html__( 'The %1$s cannot be retrieved before the %2$s action.', 'dws-wp-framework-core' ),
						esc_html_x( 'plugin file path', 'doing-it-wrong', 'dws-wp-framework-core' ),
						'plugins_loaded'
					),
					'1.0.0'
				);
			}

			return '';
		}

		return $this->plugin_file_path;
	}

	// endregion

	// region SETTERS

	/**
	 * Children classes can overwrite this to make use of a different filesystem class than the default one of the installation.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function set_wp_filesystem() : void {
		$this->wp_filesystem = $GLOBALS['wp_filesystem'];
	}

	/**
	 * It is the responsibility of each plugin using this framework to set the PHP-DI container instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function set_container() : void;

	/**
	 * It is the responsibility of each plugin using this framework to set the plugin file path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function set_plugin_file_path() : void;

	// endregion

	// region INHERITED METHODS

	/**
	 * Exploiting the WP5.2 white-screen-of-death prevention, the plugin throws an error when initializing thus preventing
	 * it from being activated if something is wrong.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  FunctionalityInitializationFailure|\Exception   If a component fails to initialize, an exception is thrown.
	 *
	 * @return bool
	 */
	public function initialize(): bool {
		if ( ! DWS_WP_FRAMEWORK_CORE_INIT ) {
			return false; // The framework will display an error message when this happens.
		}

		$result = $this->try_initialization( $this );
		if ( ! is_null( $result ) ) {
			throw $result;
		}

		$this->initialized = true;
		$this->loader->run();

		return true;
	}

	/**
	 * Initialize local non-functionality fields.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	protected function local_initialize() : bool {
		$this->set_wp_filesystem();

		$this->set_plugin_file_path();
		if ( is_null( $this->plugin_file_path ) || ! $this->wp_filesystem->is_file( $this->plugin_file_path ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: name of property */
					esc_html__( 'The %s was not set!', 'dws-wp-framework-core' ),
					esc_html_x( 'plugin file path', 'doing-it-wrong', 'dws-wp-framework-core' )
				),
				'1.0.0'
			);
			return false;
		}

		$this->set_container();
		if ( is_null( $this->container ) ) {
			_doing_it_wrong(
				__FUNCTION__,
				sprintf(
					/* translators: name of property */
					esc_html__( 'The %s was not set!', 'dws-wp-framework-core' ),
					esc_html_x( 'DI container', 'doing-it-wrong', 'dws-wp-framework-core' )
				),
				'1.0.0'
			);
			return false;
		}

		$plugin_data              = \get_plugin_data( $this->get_plugin_file_path() );
		$this->plugin_name        = $plugin_data['Name'];
		$this->plugin_version     = $plugin_data['Version'];
		$this->plugin_author_name = $plugin_data['Author'];
		$this->plugin_author_uri  = $plugin_data['AuthorURI'];
		$this->plugin_description = $plugin_data['Description'];
		$this->plugin_slug        = basename( dirname( $this->plugin_file_path ) );

		return true;
	}

	// endregion

	// region HELPERS

	/**
	 * Converts the potentially unsafe plugin's slug to a PHP-friendlier version.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_safe_slug() : string {
		return strtolower( str_replace( '-', '_', $this->get_plugin_slug() ) );
	}

	// endregion
}
