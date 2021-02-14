<?php

namespace DeepWebSolutions\Framework\Core\Actions;

use DeepWebSolutions\Framework\Core\Abstracts\PluginFunctionality;
use DeepWebSolutions\Framework\Core\Traits\Setup\Assets;
use DeepWebSolutions\Framework\Core\Traits\Setup\Hooks;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Utilities\Handlers\AdminNoticesHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;

defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the actions of install, update, uninstall, and reinstall of any derived plugins.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions
 */
class Installation extends PluginFunctionality {
	use Hooks;
	use Assets;

	// region FIELDS AND CONSTANTS

	/**
	 * Whether the user notice has been outputted during the current request or not.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  protected
	 * @var     bool
	 */
	protected bool $has_outputted_admin_notice = false;

	// endregion

	// region INHERITED METHODS

	/**
	 * Define functionality-related hooks.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     HooksDisabled::register_hooks()
	 *
	 * @param   HooksHandler    $hooks_handler  Instance of the hooks handler.
	 */
	protected function register_hooks( HooksHandler $hooks_handler ): void {
		$hooks_handler->add_action( 'plugins_loaded', $this, 'maybe_add_install_admin_notice', 100 );
		$hooks_handler->add_action( 'wp_ajax_' . $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine', $this, 'handle_ajax_installation' );
	}

	/**
	 * Maybe enqueue JS file for triggering the installation routine via AJAX.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     AssetsDisabled::enqueue_assets()
	 *
	 * @param   AssetsHandler   $assets_handler     Instance of the assets handler.
	 */
	public function enqueue_assets( AssetsHandler $assets_handler ): void {
		if ( false === $this->has_outputted_admin_notice ) {
			return; // The install/upgrade notice has not been outputted.
		}

		// Assets::register_plugin_script();
	}

	// endregion

	// region HOOKS

	/**
	 * Displays an admin notice if there are any functionalities that need installation or an update routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function maybe_add_install_admin_notice() {
		$installable_version = $this->get_installable_functionalities_versions();
		if ( empty( $installable_version ) ) {
			return;
		}

		$required_installation_routine = false;
		$installed_version             = $this->get_installed_functionalities_versions();
		foreach ( $installable_version as $class => $available_version ) {
			if ( ! isset( $installed_version[ $class ] ) || $installed_version[ $class ] !== $available_version ) {
				$required_installation_routine = true;
				break;
			}
		}

		if ( $required_installation_routine ) {
			ob_start();

			if ( is_null( $this->get_original_version() ) ) {
				/** @noinspection PhpIncludeInspection */ // phpcs:ignore
				include $this->get_plugin()::get_templates_base_path() . 'installation-required-original.php';

				$message   = ob_get_clean();
				$notice_id = 'dws-installation-required';
			} else {
				/** @noinspection PhpIncludeInspection */ // phpcs:ignore
				include $this->get_plugin()::get_templates_base_path() . 'installation-required-original.php';

				$message   = ob_get_clean();
				$notice_id = 'dws-update-required';
			}

			/** @var AdminNoticesHandler $admin_notices_handler */ // phpcs:ignore
			$admin_notices_handler = $this->get_plugin()->get_container()->get( AdminNoticesHandler::class );
			$admin_notices_handler->add_admin_notice(
				$message,
				$notice_id,
				array(
					'type'        => AdminNoticesHandler::INFO,
					'dismissible' => false,
					'capability'  => 'activate_plugins',
				)
			);

			$this->has_outputted_admin_notice = true;
		}
	}

	/**
	 * Intercepts an AJAX request for running the installation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function handle_ajax_installation() {
		if ( check_ajax_referer( 'dws-installation-routine' ) ) {
			$this->run_installation_routine();
			wp_die();
		}
	}

	// endregion

	// region METHODS

	/**
	 * Gets the first installed version of this plugin on the current WP installation.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array|null  Null if the plugin hasn't been installed yet, the first installed version otherwise.
	 */
	public function get_original_version(): ?array {
		return get_option( $this->get_plugin()->get_plugin_safe_slug() . '_original_version', null );
	}

	/**
	 * Gathers all installable classes and runs their installation or upgrade routines.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  bool
	 */
	public function run_installation_routine(): bool {
		if ( ! Users::has_capabilities( array( 'activate_plugins' ) ) ) {
			return false;
		}

		$installed_version = $this->get_installed_functionalities_versions();
		foreach ( get_declared_classes() as $declared_class ) {
			if ( ! in_array( 'DeepWebSolutions\Framework\Core\Interfaces\Installable', class_implements( $declared_class ), true ) ) {
				continue;
			}

			$available_version = call_user_func( array( $declared_class, 'get_current_version' ) );

			if ( ! isset( $installed_version[ $declared_class ] ) ) {
				$result = call_user_func( array( $declared_class, 'install' ) );
				if ( $result ) {
					$installed_version[ $declared_class ] = $available_version;
					$this->update_installed_functionalities_version( $installed_version );
				} else {
					$this->maybe_set_original_version( $installed_version );
					return false;
				}
			} elseif ( $installed_version[ $declared_class ] !== $available_version ) {
				$result = call_user_func( array( $declared_class, 'update' ) );
				if ( $result ) {
					$installed_version[ $declared_class ] = $available_version;
					$this->update_installed_functionalities_version( $installed_version );
				} else {
					$this->maybe_set_original_version( $installed_version );
					return false;
				}
			}
		}

		$this->maybe_set_original_version( $installed_version );
		return true;
	}

	// endregion

	// region HELPERS

	/**
	 * Gets the currently installable version of the installable functionalities of the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_installable_functionalities_versions(): array {
		$installable_versions = array();

		foreach ( get_declared_classes() as $declared_class ) {
			if ( ! in_array( 'DeepWebSolutions\Framework\Core\Interfaces\Installable', class_implements( $declared_class ), true ) ) {
				continue;
			}

			$installable_versions[ $declared_class ] = call_user_func( array( $declared_class, 'get_current_version' ) );
		}

		return $installable_versions;
	}

	/**
	 * Gets the currently installed version of the installable functionalities from the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_installed_functionalities_versions(): array {
		return get_option( $this->get_plugin()->get_plugin_safe_slug() . '_version', array() );
	}

	/**
	 * Stores the newly installed version of the installable functionalities to the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $version    The current version of the installable components.
	 *
	 * @return  bool
	 */
	protected function update_installed_functionalities_version( array $version ): bool {
		return update_option( $this->get_plugin()->get_plugin_safe_slug() . '_version', $version );
	}

	/**
	 * If not set yet, sets the given version as the originally installed version on the current WP installation.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $version    The version that should be potentially set as the originally installed one.
	 *
	 * @return  bool|null   Null if the plugin has been installed yet or the result of update_option otherwise.
	 */
	protected function maybe_set_original_version( array $version ): ?bool {
		$original_version = $this->get_original_version();
		return is_null( $original_version )
			? update_option( $this->get_plugin()->get_plugin_safe_slug() . '_original_version', $version )
			: null;
	}

	// endregion
}
