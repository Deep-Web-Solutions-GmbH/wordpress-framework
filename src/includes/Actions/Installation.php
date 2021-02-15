<?php

namespace DeepWebSolutions\Framework\Core\Actions;

use DeepWebSolutions\Framework\Core\Abstracts\PluginFunctionality;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Installable;
use DeepWebSolutions\Framework\Core\Traits\Setup\Assets;
use DeepWebSolutions\Framework\Core\Traits\Setup\Hooks;
use DeepWebSolutions\Framework\Helpers\WordPress\Users;
use DeepWebSolutions\Framework\Utilities\Handlers\AdminNoticesHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\AssetsHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\HooksHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\AdminNotices;

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
	// region TRAITS

	use Hooks;
	use Assets;
	use AdminNotices;

	// endregion

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
	 * @see     Hooks::register_hooks()
	 *
	 * @param   HooksHandler    $hooks_handler  Instance of the hooks handler.
	 */
	protected function register_hooks( HooksHandler $hooks_handler ): void {
		$hooks_handler->add_action( 'wp_ajax_' . $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine', $this, 'handle_ajax_installation' );
	}

	/**
	 * Displays an admin notice if there are any installables that need installation or an update routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AdminNoticesHandler     $admin_notices_handler      Instance of the admin notices handler.
	 */
	protected function register_admin_notices( AdminNoticesHandler $admin_notices_handler ): void {
		$installable_version = $this->get_installable_versions();
		if ( empty( $installable_version ) ) {
			return;
		}

		$installed_version  = $this->get_installed_versions();
		$installation_delta = array_diff_assoc( $installable_version, $installed_version );
		if ( empty( $installation_delta ) ) {
			return;
		}

		ob_start();

		if ( is_null( $this->get_original_version() ) ) {
			/* @noinspection PhpIncludeInspection */
			include $this->get_plugin()::get_templates_base_path() . 'installation/installation-required-original.php';

			$message   = ob_get_clean();
			$notice_id = $this->get_notice_id( 'installation' );
		} else {
			/* @noinspection PhpIncludeInspection */
			include $this->get_plugin()::get_templates_base_path() . 'installation/installation-required-update.php';

			$message   = ob_get_clean();
			$notice_id = $this->get_notice_id( 'update' );
		}

		$this->has_outputted_admin_notice = true;
		$admin_notices_handler->add_admin_notice(
			$message,
			$notice_id,
			array(
				'type'        => AdminNoticesHandler::INFO,
				'dismissible' => false,
				'capability'  => 'activate_plugins',
			)
		);
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

		$handle = $this->get_asset_handle( 'install-notice' );
		$assets_handler->enqueue_admin_script(
			$handle,
			$this->get_plugin()->get_assets_base_relative_url() . 'dist/js/installation.min.js',
			$this->get_plugin()->get_plugin_version(),
			array( 'jquery' )
		);
		wp_localize_script(
			$handle,
			$this->get_plugin()->get_plugin_safe_slug() . '_installation_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'action'   => $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine',
				'_wpnonce' => wp_create_nonce( $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine' ),
			)
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Intercepts an AJAX request for running the installation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function handle_ajax_installation() {
		if ( check_ajax_referer( $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine' ) ) {
			$this->run_installation_routine();
		}

		wp_die();
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

		$installed_version  = $this->get_installed_versions();
		$installation_delta = array_diff_assoc( $this->get_installable_versions(), $installed_version );

		foreach ( $installation_delta as $class => $version ) {
			if ( ! isset( $installed_version[ $class ] ) ) {
				$result = $this->get_container()->call( array( $class, 'install' ) );
			} else {
				$result = $this->get_container()->call( array( $class, 'update' ) );
			}

			if ( is_null( $result ) ) {
				$installed_version[ $class ] = $version;
				$this->update_installed_version( $installed_version );
			} else {
				$this->maybe_set_original_version( $installed_version );

				$admin_notices_handler = $this->get_admin_notices_handler();
				$admin_notices_handler->add_admin_notice_to_user(
					sprintf(
						/* translators: 1. Installation node name, 2. Error message. */
						__( '<strong>%1$s</strong> failed to complete the installation routine. The error is: %2$s', 'dws-wp-framework-core' ),
						$this->get_registrant_name(),
						$result->getMessage()
					),
					$this->get_notice_id( 'failed', array( $class ) )
				);

				return false;
			}
		}

		$this->maybe_set_original_version( $installed_version );
		return true;
	}

	// endregion

	// region HELPERS

	/**
	 * Gets the currently installable version of the installables of the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_installable_versions(): array {
		$installable_versions = array();

		foreach ( get_declared_classes() as $declared_class ) {
			if ( ! in_array( Installable::class, class_implements( $declared_class ), true ) ) {
				continue;
			}

			$installable_versions[ $declared_class ] = call_user_func( array( $declared_class, 'get_current_version' ) );
		}

		return $installable_versions;
	}

	/**
	 * Gets the currently installed version of the installables from the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function get_installed_versions(): array {
		return get_option( $this->get_plugin()->get_plugin_safe_slug() . '_version', array() );
	}

	/**
	 * Stores the newly installed version of the installables to the database.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $version    The current version of the installable components.
	 *
	 * @return  bool
	 */
	protected function update_installed_version( array $version ): bool {
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
