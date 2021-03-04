<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Installation;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Internationalization;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\FunctionalityInitFailureException;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\PluginInitFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableLocalTrait;
use DeepWebSolutions\Framework\Foundations\Plugin\PluginInterface;
use DeepWebSolutions\Framework\Foundations\Plugin\PluginTrait;
use DeepWebSolutions\Framework\Helpers\FileSystem\FilesystemAwareTrait;
use Exception;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use function DeepWebSolutions\Framework\dws_wp_framework_output_initialization_error;
use const DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_CORE_INIT;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the most often required abilities of a main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
abstract class AbstractPluginRoot extends AbstractPluginFunctionality implements PluginInterface {
	// region TRAITS

	use FilesystemAwareTrait;
	use InitializableLocalTrait;
	use PluginTrait;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * The system path to the main WP plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string|null
	 */
	protected ?string $plugin_file_path = null;

	// endregion

	// region MAGIC METHODS

	/**
	 * AbstractPluginRoot constructor. Parent constructor is called in the 'initialize_local' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct() {
		/* empty on purpose */
	}

	// endregion

	// region GETTERS

	/**
	 * Gets the path to the main WP plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Plugin::get_plugin_file_path()
	 *
	 * @return  string
	 */
	public function get_plugin_file_path(): string {
		if ( is_null( $this->plugin_file_path ) ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				$this->log_event_and_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						'The %1$s cannot be retrieved before the %2$s action.',
						'plugin file path',
						'plugins_loaded'
					),
					'1.0.0',
					LogLevel::DEBUG,
					'framework'
				);
			}

			return '';
		}

		return $this->plugin_file_path;
	}

	// endregion

	// region WP-SPECIFIC METHODS

	/**
	 * On first activation, run the installation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  Exception   Thrown if an installable node does NOT belong to a plugin tree.
	 *
	 * @return  null|InstallFailureException
	 */
	public function activate(): ?InstallFailureException {
		$installer = $this->get_container()->get( Installation::class );
		return ( is_null( $installer->get_original_version() ) )
			? $installer->install_or_update()
			: null;
	}

	/**
	 * On uninstall, run the uninstallation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  Exception   Thrown if an installable node does NOT belong to a plugin tree.
	 *
	 * @return  null|UninstallFailureException
	 */
	public function uninstall(): ?UninstallFailureException {
		$installer = $this->get_container()->get( Installation::class );
		return $installer->uninstall();
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * Returns the current plugin instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  AbstractPluginRoot
	 */
	public function get_plugin(): AbstractPluginRoot {
		return $this;
	}

	/**
	 * Returns the plugin tree's DI container instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  ContainerInterface
	 */
	public function get_container(): ContainerInterface {
		return $this->di_container;
	}

	/**
	 * Sets a container on the instance.
	 *
	 * @throws  LogicException  Thrown when the container is null.
	 *
	 * @param   ContainerInterface|null     $container      Container to be used by the plugin from now on.
	 */
	public function set_container( ?ContainerInterface $container = null ): void {
		if ( is_null( $container ) ) {
			throw new LogicException( 'The root must be set a proper container.' );
		}

		$this->di_container = $container;
	}

	/**
	 * The starting point of the whole plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     AbstractPluginFunctionality::initialize()
	 *
	 * @return  FunctionalityInitFailureException|null
	 */
	public function initialize(): ?FunctionalityInitFailureException {
		if ( ! defined( 'DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_CORE_INIT' ) || ! DWS_WP_FRAMEWORK_CORE_INIT ) {
			return new PluginInitFailureException(); // The framework will display an error message when this is false.
		}

		$result = parent::initialize();
		if ( ! is_null( $result ) ) {
			dws_wp_framework_output_initialization_error( $result, $this );
		}

		return $result;
	}

	/**
	 * Initialize plugin fields.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     InitializableLocalTrait::initialize_local()
	 *
	 * @return  PluginInitFailureException|null
	 */
	public function initialize_local(): ?PluginInitFailureException {
		$this->initialize_plugin_file_path();
		if ( is_null( $this->plugin_file_path ) || ! $this->get_wp_filesystem()->is_file( $this->plugin_file_path ) ) {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				'The plugin file path is not set',
				'1.0.0',
				PluginInitFailureException::class,
				null,
				LogLevel::ERROR,
				'framework'
			);
		}

		$this->initialize_plugin_data();
		parent::__construct( $this->get_logging_service(), $this->get_plugin_file_path(), $this->get_plugin_name() );

		return null;
	}

	/**
	 * Define some plugin-level, overarching functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_di_container_children(): array {
		return array( Internationalization::class, Installation::class );
	}

	// endregion

	// region METHODS

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
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_relative_url( 'assets' ) );
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
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_path( 'templates' ) );
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
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_relative_url( 'templates' ) );
	}

	/**
	 * Returns the path to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_languages_base_path(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_path( 'languages' ) );
	}

	/**
	 * Returns the relative URL to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_languages_base_relative_url(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_relative_url( 'languages' ) );
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

	// region HELPERS

	/**
	 * It is the responsibility of each plugin using this framework to set the plugin file path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function initialize_plugin_file_path(): void;

	/**
	 * Uses the plugin file path to initialize the plugin data fields.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function initialize_plugin_data(): void {
		$plugin_data                  = \get_plugin_data( $this->get_plugin_file_path() );
		$this->plugin_name            = $plugin_data['Name'];
		$this->plugin_version         = $plugin_data['Version'];
		$this->plugin_author_name     = $plugin_data['Author'];
		$this->plugin_author_uri      = $plugin_data['AuthorURI'];
		$this->plugin_description     = $plugin_data['Description'];
		$this->plugin_language_domain = $plugin_data['TextDomain'];
		$this->plugin_slug            = basename( dirname( $this->plugin_file_path ) );
	}

	// endregion
}
