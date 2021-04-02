<?php

namespace DeepWebSolutions\Framework\Core\PluginComponents;

use DeepWebSolutions\Framework\Core\Actions\Foundations\Initializable\InitializableTrait;
use DeepWebSolutions\Framework\Core\Actions\Initializable\SetupOnInitializationTrait;
use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Setupable\RunnablesOnSetupTrait;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Installation;
use DeepWebSolutions\Framework\Core\PluginComponents\Actions\Internationalization;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\FunctionalityInitFailureException;
use DeepWebSolutions\Framework\Core\PluginComponents\Exceptions\PluginInitFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\AddContainerChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\InitializeChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Plugin\AbstractPluginRoot;
use DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveableTrait;
use DeepWebSolutions\Framework\Foundations\States\ActiveableInterface;
use DeepWebSolutions\Framework\Foundations\States\Disableable\DisableableTrait;
use DeepWebSolutions\Framework\Foundations\States\DisableableInterface;
use DeepWebSolutions\Framework\Foundations\Utilities\DependencyInjection\ContainerAwareInterface;
use DeepWebSolutions\Framework\Foundations\Utilities\DependencyInjection\ContainerAwareTrait;
use DeepWebSolutions\Framework\Helpers\FileSystem\Files;
use DeepWebSolutions\Framework\Utilities\Actions\Setupable\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterInterface;
use Psr\Container\ContainerInterface;
use function DeepWebSolutions\Framework\dws_wp_framework_get_core_init_status;
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
abstract class AbstractPluginFunctionalityRoot extends AbstractPluginRoot implements ContainerAwareInterface, ActiveableInterface, DisableableInterface, HooksServiceRegisterInterface, SetupableInterface {
	// region TRAITS

	use ActiveableTrait;
	use AddContainerChildrenTrait;
	use ContainerAwareTrait;
	use DisableableTrait;
	use InitializableTrait {
		InitializableTrait::initialize as protected initialize_trait;
	}
	use InitializeChildrenTrait;
	use RunnablesOnSetupTrait;
	use SetupOnInitializationTrait;
	use SetupHooksTrait;

	// endregion

	// region MAGIC METHODS

	/**
	 * AbstractPluginRoot constructor. Parent constructor is called in the 'initialize_local' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   ContainerInterface  $di_container   Instance of the DI-container to user throughout the plugin.
	 */
	public function __construct( ContainerInterface $di_container ) {
		$this->set_container( $di_container );
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
			? $installer->install_or_update() : null;
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
		return $this->get_container_entry( Installation::class )->uninstall();
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * The starting point of the whole plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  FunctionalityInitFailureException|null
	 */
	public function initialize(): ?FunctionalityInitFailureException {
		if ( ! dws_wp_framework_get_core_init_status() ) {
			return new PluginInitFailureException(); // The framework will display an error message when this is false.
		}

		$result = $this->initialize_trait();
		if ( ! \is_null( $result ) ) {
			dws_wp_framework_output_initialization_error( $result, $this );
		}

		return new FunctionalityInitFailureException(
			$result->getMessage(),
			$result->getCode(),
			$result
		);
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

	/**
	 * Registers actions and filters with the hooks service.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   HooksService    $hooks_service      Instance of the hooks service.
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'network_admin_plugin_action_links_' . $this->get_plugin_basename(), $this, 'register_network_plugin_actions', 10, 4 );
		$hooks_service->add_filter( 'plugin_action_links_' . $this->get_plugin_basename(), $this, 'register_plugin_actions', 10, 4 );
		$hooks_service->add_filter( 'plugin_row_meta', $this, 'register_plugin_row_meta', 10, 4 );
	}

	// endregion

	// region HOOKS

	/**
	 * Registers plugin actions on network pages.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   array   $actions        An array of plugin action links.
	 * @param   string  $plugin_file    Path to the plugin file relative to the plugins directory.
	 * @param   array   $plugin_data    An array of plugin data. See `get_plugin_data()`.
	 * @param   string  $context        The plugin context. By default this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
	 *
	 * @return  array
	 */
	public function register_network_plugin_actions( array $actions, string $plugin_file, array $plugin_data, string $context ): array {
		return $actions;
	}

	/**
	 * Registers plugin actions on blog pages.
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
		return $actions;
	}

	/**
	 * Register plugin meta information and/or links.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @param   array   $plugin_meta    An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
	 * @param   string  $plugin_file    Path to the plugin file relative to the plugins directory.
	 * @param   array   $plugin_data    An array of plugin data. See `get_plugin_data()`.
	 * @param   string  $status         Status filter currently applied to the plugin list. Possible values are: 'all', 'active', 'inactive', 'recently_activated',
	 *                                  'upgrade', 'mustuse', 'dropins', 'search', 'paused', 'auto-update-enabled', 'auto-update-disabled'.
	 *
	 * @return  array
	 */
	public function register_plugin_row_meta( array $plugin_meta, string $plugin_file, array $plugin_data, string $status ): array {
		return $plugin_meta;
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
	public static function get_plugin_base_path(): string {
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
	public static function get_plugin_custom_base_path( string $relative_path ): string {
		return Files::generate_full_path( self::get_plugin_base_path(), $relative_path ) . DIRECTORY_SEPARATOR;
	}

	/**
	 * Returns the relative URL to the plugin's main content folder.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_base_relative_url(): string {
		return \trailingslashit( \dirname( self::get_base_relative_url() ) );
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
	public static function get_plugin_custom_base_relative_url( string $relative_path ): string {
		return self::get_plugin_base_relative_url() . \trailingslashit( $relative_path );
	}

	/**
	 * Returns the path to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_assets_base_path(): string {
		return self::get_plugin_custom_base_path( 'assets' );
	}

	/**
	 * Returns the relative URL to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_assets_base_relative_url(): string {
		return self::get_plugin_custom_base_relative_url( 'assets' );
	}

	/**
	 * Returns the path to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_templates_base_path(): string {
		return self::get_plugin_custom_base_path( 'templates' );
	}

	/**
	 * Returns the relative URL to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_templates_base_relative_url(): string {
		return self::get_plugin_custom_base_relative_url( 'templates' );
	}

	/**
	 * Returns the path to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_languages_base_path(): string {
		return self::get_plugin_custom_base_path( 'languages' );
	}

	/**
	 * Returns the relative URL to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_languages_base_relative_url(): string {
		return self::get_plugin_custom_base_relative_url( 'languages' );
	}

	/**
	 * Returns the path to the classes folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_plugin_includes_base_path(): string {
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
	public static function get_plugin_includes_base_relative_url(): string {
		return self::get_base_relative_url();
	}

	// endregion
}
