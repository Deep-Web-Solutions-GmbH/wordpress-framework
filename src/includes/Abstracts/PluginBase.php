<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Exceptions\FunctionalityInitializationFailure;
use DeepWebSolutions\Framework\Core\Exceptions\PluginInitializationFailure;
use DeepWebSolutions\Framework\Helpers\WordPress;
use DI\Container;
use Psr\Log\LogLevel;
use function DeepWebSolutions\Framework\dws_wp_framework_output_initialization_error;

defined( 'ABSPATH' ) || exit;

/**
 * Class Plugin
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts
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
	 * @var     \WP_Filesystem_Base|null
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
	protected ?string $plugin_file_path = null;

	/**
	 * The static instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     Container|null
	 */
	protected ?Container $container = null;

	// endregion

	// region WP-SPECIFIC METHODS

	/**
	 * The child plugin should define its activation routine in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function activate(): void;

	/**
	 * The child plugin should define its deactivation routine in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function deactivate(): void;

	/**
	 * The child plugin should define its uninstallation routine in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract public function uninstall(): void;

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
	public function get_plugin_name(): string {
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
	public function get_plugin_version(): string {
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
	public function get_plugin_author_name(): string {
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
	public function get_plugin_author_uri(): string {
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
	public function get_plugin_description(): string {
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
	public function get_plugin_slug(): string {
		return $this->plugin_slug;
	}

	/**
	 * Gets the static instance of the PHP-DI container.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Container
	 */
	public function get_container(): Container {
		if ( is_null( $this->container ) ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				WordPress::log_event_and_doing_it_wrong(
					$this->logger,
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
	public function get_plugin_file_path(): string {
		if ( is_null( $this->plugin_file_path ) ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				WordPress::log_event_and_doing_it_wrong(
					$this->logger,
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

	/**
	 * Gets the instance of the WP Filesystem class that should be used by this plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  \WP_Filesystem_Base|null
	 */
	public function get_wp_filesystem(): ?\WP_Filesystem_Base {
		return $this->wp_filesystem ?? $GLOBALS['wp_filesystem'];
	}

	// endregion

	// region SETTERS

	/**
	 * Children classes can overwrite this to make use of a different filesystem class than the default one of the installation.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function set_wp_filesystem(): void {
		global $wp_filesystem;

		if ( null === $wp_filesystem ) {
			/** @noinspection PhpIncludeInspection */ // phpcs:ignore
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$this->wp_filesystem = $wp_filesystem;
	}

	/**
	 * It is the responsibility of each plugin using this framework to set the PHP-DI container instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function set_container(): void;

	/**
	 * It is the responsibility of each plugin using this framework to set the plugin file path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function set_plugin_file_path(): void;

	// endregion

	// region INHERITED METHODS

	/**
	 * The starting point of the whole plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	final public function initialize(): ?FunctionalityInitializationFailure {
		if ( ! defined( 'DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_CORE_INIT' ) || ! \DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_CORE_INIT ) {
			return new FunctionalityInitializationFailure(); // The framework will display an error message when this is false.
		}

		$result = parent::initialize();
		if ( ! is_null( $result ) ) {
			dws_wp_framework_output_initialization_error( $result, $this );
			return $result;
		}

		$this->loader->run();
		return null;
	}

	/**
	 * Initialize local non-functionality fields.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function initialize_local(): ?PluginInitializationFailure {
		$this->set_wp_filesystem();

		$this->set_plugin_file_path();
		if ( is_null( $this->plugin_file_path ) || ! $this->wp_filesystem->is_file( $this->plugin_file_path ) ) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */ // phpcs:ignore
			return WordPress::log_event_and_doing_it_wrong_and_return_exception(
				$this->logger,
				__FUNCTION__,
				'The plugin file path was not set!',
				'1.0.0',
				PluginInitializationFailure::class,
				LogLevel::ERROR
			);
		}

		$this->set_container();
		if ( is_null( $this->container ) ) {
			/** @noinspection PhpIncompatibleReturnTypeInspection */ // phpcs:ignore
			return WordPress::log_event_and_doing_it_wrong_and_return_exception(
				$this->logger,
				__FUNCTION__,
				'The plugin dependency injection container was not set.',
				'1.0.0',
				PluginInitializationFailure::class,
				LogLevel::ERROR
			);
		}

		$plugin_data              = \get_plugin_data( $this->get_plugin_file_path() );
		$this->plugin_name        = $plugin_data['Name'];
		$this->plugin_version     = $plugin_data['Version'];
		$this->plugin_author_name = $plugin_data['Author'];
		$this->plugin_author_uri  = $plugin_data['AuthorURI'];
		$this->plugin_description = $plugin_data['Description'];
		$this->plugin_slug        = basename( dirname( $this->plugin_file_path ) );

		return null;
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
	public function get_plugin_safe_slug(): string {
		return strtolower( str_replace( '-', '_', $this->get_plugin_slug() ) );
	}

	/**
	 * Returns the path to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_assets_base_path(): string {
		return str_replace( 'includes/', '', self::get_custom_base_path( 'assets' ) );
	}

	/**
	 * Returns the relative URL to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_assets_base_relative_url(): string {
		return str_replace( 'includes/', '', self::get_custom_base_relative_url( 'assets' ) );
	}

	/**
	 * Returns the path to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_templates_base_path(): string {
		return str_replace( 'includes/', '', self::get_custom_base_path( 'templates' ) );
	}

	/**
	 * Returns the relative URL to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_templates_base_relative_url(): string {
		return str_replace( 'includes/', '', self::get_custom_base_relative_url( 'templates' ) );
	}

	/**
	 * Returns the path to the classes folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_includes_base_path(): string {
		return self::get_base_path();
	}

	/**
	 * Returns the path to the classes folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_includes_base_relative_url(): string {
		return self::get_base_relative_url();
	}

	// endregion
}
