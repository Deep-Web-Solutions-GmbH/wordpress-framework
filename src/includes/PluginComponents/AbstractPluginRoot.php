<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Installation;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Internationalization;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\FunctionalityInitFailureException;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\PluginInitFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableLocalTrait;
use DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DeepWebSolutions\Framework\Foundations\Plugin\PluginInterface;
use DeepWebSolutions\Framework\Foundations\Plugin\PluginTrait;
use DeepWebSolutions\Framework\Helpers\FileSystem\Files;
use DeepWebSolutions\Framework\Helpers\FileSystem\FilesystemAwareTrait;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterInterface;
use Exception;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;
use function DeepWebSolutions\Framework\dws_wp_framework_get_core_init_status;
use function DeepWebSolutions\Framework\dws_wp_framework_get_whitelabel_support_url;
use function DeepWebSolutions\Framework\dws_wp_framework_output_initialization_error;

\defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the most often required abilities of a main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\PluginComponents
 */
abstract class AbstractPluginRoot extends AbstractPluginFunctionality implements HooksServiceRegisterInterface, PluginInterface {
	// region TRAITS

	use FilesystemAwareTrait;
	use InitializableLocalTrait;
	use PluginTrait;
	use SetupHooksTrait;

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
		return $this->plugin_file_path ?? '';
	}

	// endregion

	// region WP-SPECIFIC METHODS

	/**
	 * On first activation, run the installation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  null|InstallFailureException
	 */
	public function activate(): ?InstallFailureException {
		$installer = $this->get_container_entry( Installation::class );
		return ( \is_null( $installer->get_original_version() ) )
			? $installer->install_or_update()
			: null;
	}

	/**
	 * On uninstall, run the uninstallation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  null|UninstallFailureException
	 */
	public function uninstall(): ?UninstallFailureException {
		return $this->get_container_entry( Installation::class )
					->uninstall();
	}

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
		$hooks_service->add_filter( 'network_admin_plugin_action_links_' . $this->get_plugin_basename(), $this, 'register_plugin_actions', 10, 4 );
		$hooks_service->add_filter( 'plugin_action_links_' . $this->get_plugin_basename(), $this, 'register_plugin_actions', 10, 4 );
	}

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
		if ( ! \is_null( $container ) ) {
			$this->di_container = $container;
		}
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
		if ( ! dws_wp_framework_get_core_init_status() ) {
			return new PluginInitFailureException(); // The framework will display an error message when this is false.
		}

		$result = parent::initialize();
		if ( ! \is_null( $result ) ) {
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
		if ( \is_null( $this->plugin_file_path ) || ! $this->get_wp_filesystem()->is_file( $this->plugin_file_path ) ) {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->log_event( 'The plugin file path is not set', array(), 'framework' )
				->set_log_level( LogLevel::ERROR )
				->doing_it_wrong( __FUNCTION__, '1.0.0' )
				->return_exception( PluginInitFailureException::class )
				->finalize();
		}

		$this->initialize_plugin_data();

		parent::__construct( $this->get_container_entry( LoggingService::class ), $this->get_plugin_basename(), $this->get_plugin_name() );

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

	// region HOOKS

	/**
	 * Registers a few default plugin actions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   string[]    $actions        An array of plugin action links.
	 * @param   string      $plugin_file    Path to the plugin file relative to the plugins directory.
	 * @param   array       $plugin_data    An array of plugin data. See `get_plugin_data()`.
	 * @param   string      $context        The plugin context. By default this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 *
	 * @return  string[]
	 */
	public function register_plugin_actions( array $actions, string $plugin_file, array $plugin_data, string $context ): array {
		/* @noinspection HtmlUnknownTarget */
		$actions[] = sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			dws_wp_framework_get_whitelabel_support_url(),
			\_x( 'Get support', 'action-links', 'dws-wp-framework-core' )
		);

		return $actions;
	}

	// endregion

	// region METHODS

	/**
	 * Returns the name of a plugin based on the path to its main file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_basename(): string {
		return \plugin_basename( $this->get_plugin_file_path() );
	}

	/**
	 * Returns the path to the plugin's main content folder.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_base_path(): string {
		return \dirname( self::get_base_path() );
	}

	/**
	 * Appends a given path to the plugin's base path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $relative_path  Path to append.
	 *
	 * @return  string
	 */
	public function get_plugin_custom_base_path( string $relative_path ): string {
		return Files::generate_full_path( self::get_plugin_base_path(), $relative_path );
	}

	/**
	 * Returns the relative URL to the plugin's main content folder.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_base_relative_url(): string {
		return \plugins_url( '', self::get_base_relative_url() );
	}

	/**
	 * Appends a given path to the plugin's base relative URL.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $relative_path  Path to append.
	 *
	 * @return  string
	 */
	public function get_plugin_custom_base_relative_url( string $relative_path ): string {
		return $this->get_plugin_base_relative_url() . \trailingslashit( $relative_path );
	}

	/**
	 * Returns the path to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_assets_base_path(): string {
		return $this->get_plugin_custom_base_path( 'assets' );
	}

	/**
	 * Returns the relative URL to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_assets_base_relative_url(): string {
		return $this->get_plugin_custom_base_relative_url( 'assets' );
	}

	/**
	 * Returns the path to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_templates_base_path(): string {
		return $this->get_plugin_custom_base_path( 'templates' );
	}

	/**
	 * Returns the relative URL to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_templates_base_relative_url(): string {
		return $this->get_plugin_custom_base_relative_url( 'templates' );
	}

	/**
	 * Returns the path to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_languages_base_path(): string {
		return $this->get_plugin_custom_base_path( 'languages' );
	}

	/**
	 * Returns the relative URL to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_languages_base_relative_url(): string {
		return $this->get_plugin_custom_base_relative_url( 'languages' );
	}

	/**
	 * Returns the path to the classes folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_plugin_includes_base_path(): string {
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
	public function get_plugin_includes_base_relative_url(): string {
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
		$plugin_data                  = \get_file_data(
			$this->get_plugin_file_path(),
			array(
				'Name'        => 'Plugin Name',
				'Version'     => 'Version',
				'Description' => 'Description',
				'Author'      => 'Author',
				'AuthorURI'   => 'Author URI',
				'TextDomain'  => 'Text Domain',
			),
			'plugin'
		);
		$this->plugin_name            = $plugin_data['Name'];
		$this->plugin_version         = $plugin_data['Version'];
		$this->plugin_description     = $plugin_data['Description'];
		$this->plugin_author_name     = $plugin_data['Author'];
		$this->plugin_author_uri      = $plugin_data['AuthorURI'];
		$this->plugin_language_domain = $plugin_data['TextDomain'];
		$this->plugin_slug            = \dirname( $this->get_plugin_basename() );
	}

	// endregion
}
