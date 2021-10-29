<?php

namespace DeepWebSolutions\Framework\Core;

use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\PluginComponents\InstallationFunctionality;
use DeepWebSolutions\Framework\Core\PluginComponents\InternationalizationFunctionality;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DeepWebSolutions\Framework\Foundations\Actions\Initializable\Integrations\MaybeSetupOnInitializationTrait;
use DeepWebSolutions\Framework\Foundations\Actions\Setupable\Integrations\RunnablesOnSetupTrait;
use DeepWebSolutions\Framework\Foundations\Actions\SetupableInterface;
use DeepWebSolutions\Framework\Foundations\DependencyInjection\ContainerAwareInterface;
use DeepWebSolutions\Framework\Foundations\DependencyInjection\ContainerAwareTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\AddContainerChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\InitializeChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Actions\MaybeSetupChildrenTrait;
use DeepWebSolutions\Framework\Foundations\Hierarchy\Plugin\AbstractPluginRoot;
use DeepWebSolutions\Framework\Foundations\States\Activeable\ActiveableTrait;
use DeepWebSolutions\Framework\Foundations\States\ActiveableInterface;
use DeepWebSolutions\Framework\Foundations\States\Disableable\DisableableTrait;
use DeepWebSolutions\Framework\Foundations\States\DisableableInterface;
use DeepWebSolutions\Framework\Helpers\FileSystem\Files;
use DeepWebSolutions\Framework\Helpers\HooksHelpersAwareInterface;
use DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
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
 * @package DeepWebSolutions\WP-Framework\Core\Plugin
 */
abstract class AbstractPluginFunctionalityRoot extends AbstractPluginRoot implements ContainerAwareInterface, ActiveableInterface, DisableableInterface, HooksHelpersAwareInterface, HooksServiceRegisterInterface, SetupableInterface {
	// region TRAITS

	use ActiveableTrait, DisableableTrait;
	use AddContainerChildrenTrait, ContainerAwareTrait;
	use InitializableTrait, InitializeChildrenTrait { // phpcs:ignore
		InitializableTrait::initialize as protected initialize_trait;
	}
	use MaybeSetupOnInitializationTrait, SetupHooksTrait;
	use MaybeSetupChildrenTrait, RunnablesOnSetupTrait;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * The absolute path to the plugin's entry point file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     string
	 */
	protected string $plugin_file_path;

	// endregion

	// region MAGIC METHODS

	/**
	 * AbstractPluginRoot constructor. Parent constructor is called in the 'initialize_local' method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string              $plugin_file_path   The absolute path to the plugin's entry point file.
	 * @param   ContainerInterface  $di_container       Instance of the DI-container to user throughout the plugin.
	 */
	public function __construct( string $plugin_file_path, ContainerInterface $di_container ) {
		$this->plugin_file_path = $plugin_file_path;
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
		$installer = $this->get_container_entry( InstallationFunctionality::class );
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
		return $this->get_container_entry( InstallationFunctionality::class )->uninstall();
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_plugin_file_path(): string {
		return $this->plugin_file_path;
	}

	/**
	 * The starting point of the whole plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InitializationFailureException|null
	 */
	public function initialize(): ?InitializationFailureException {
		$return = new InitializationFailureException();

		if ( dws_wp_framework_get_core_init_status() ) {
			$return = $this->initialize_trait();
			if ( ! \is_null( $return ) ) {
				dws_wp_framework_output_initialization_error( $return, $this );
			}
		}

		return $return;
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function get_di_container_children(): array {
		return array( InternationalizationFunctionality::class, InstallationFunctionality::class );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		if ( \is_admin() ) {
			$hooks_service->add_filter( 'network_admin_plugin_action_links_' . $this->get_plugin_basename(), $this, 'register_network_plugin_actions', 10, 4 );
			$hooks_service->add_filter( 'plugin_action_links_' . $this->get_plugin_basename(), $this, 'register_plugin_actions', 10, 4 );
			$hooks_service->add_filter( 'plugin_row_meta', $this, 'register_plugin_row_meta', 10, 4 );
		}
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
	 * @param   string      $context        The plugin context. By default, this can include 'all', 'active', 'inactive', 'recently_activated', 'upgrade', 'mustuse', 'dropins', and 'search'.
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
	 * Returns the path to a given plugin resource.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $relative_path  Relative path to append to the base path.
	 * @param   bool    $absolute       Whether to return the absolute path or the relative to the WP root directory path.
	 *
	 * @return  string
	 */
	public static function get_plugin_custom_path( string $relative_path, bool $absolute = false ): string {
		return Files::generate_full_path( \dirname( self::get_path( $absolute ) ), $relative_path );
	}

	/**
	 * Returns the URL to a given plugin resource.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $relative_path  Relative path to append to the base URL.
	 * @param   bool    $relative       Whether to return the relative or absolute URL.
	 *
	 * @return  string
	 */
	public static function get_plugin_custom_url( string $relative_path, bool $relative = true ): string {
		return Files::generate_full_path( \dirname( self::get_url( $relative ) ), $relative_path );
	}

	/**
	 * Returns the path to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $absolute   Whether to return the absolute path or the relative to the WP root directory path.
	 *
	 * @return  string
	 */
	public static function get_plugin_assets_path( bool $absolute = false ): string {
		return self::get_plugin_custom_path( 'assets', $absolute );
	}

	/**
	 * Returns the URL to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $relative   Whether to return the relative or absolute URL.
	 *
	 * @return  string
	 */
	public static function get_plugin_assets_url( bool $relative = true ): string {
		return self::get_plugin_custom_url( 'assets', $relative );
	}

	/**
	 * Returns the path to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $absolute   Whether to return the absolute path or the relative to the WP root directory path.
	 *
	 * @return  string
	 */
	public static function get_plugin_templates_path( bool $absolute = false ): string {
		return self::get_plugin_custom_path( 'templates', $absolute );
	}

	/**
	 * Returns the URL to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $relative   Whether to return the relative or absolute URL.
	 *
	 * @return  string
	 */
	public static function get_plugin_templates_url( bool $relative = true ): string {
		return self::get_plugin_custom_url( 'templates', $relative );
	}

	/**
	 * Returns the path to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $absolute   Whether to return the absolute path or the relative to the WP root directory path.
	 *
	 * @return  string
	 */
	public static function get_plugin_languages_path( bool $absolute = false ): string {
		return self::get_plugin_custom_path( 'languages', $absolute );
	}

	/**
	 * Returns the URL to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $relative   Whether to return the relative or absolute URL.
	 *
	 * @return  string
	 */
	public static function get_plugin_languages_url( bool $relative = true ): string {
		return self::get_plugin_custom_url( 'languages', $relative );
	}

	// endregion
}
