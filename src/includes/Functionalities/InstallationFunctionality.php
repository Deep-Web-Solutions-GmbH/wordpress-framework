<?php

namespace DeepWebSolutions\Framework\Core\Functionalities;

use DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DeepWebSolutions\Framework\Core\Actions\Installable\InstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\Installable\UninstallFailureException;
use DeepWebSolutions\Framework\Core\Actions\InstallableInterface;
use DeepWebSolutions\Framework\Core\Actions\UninstallableInterface;
use DeepWebSolutions\Framework\Foundations\Logging\LoggingService;
use DeepWebSolutions\Framework\Helpers\Assets;
use DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DeepWebSolutions\Framework\Helpers\Users;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Actions\InitializeAdminNoticesServiceTrait;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Actions\SetupAdminNoticesTrait;
use DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticesService;
use DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticesServiceAwareInterface;
use DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticesServiceRegisterInterface;
use DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticeTypesEnum;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Helpers\AdminNoticesHelpers;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Notices\DismissibleAdminNotice;
use DeepWebSolutions\Framework\Utilities\AdminNotices\Notices\SimpleAdminNotice;
use DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksService;
use DeepWebSolutions\Framework\Utilities\Hooks\HooksServiceRegisterInterface;
use function DeepWebSolutions\Framework\dws_wp_framework_get_core_base_path;

\defined( 'ABSPATH' ) || exit;

/**
 * Standardizes the actions of install, update, uninstall, and reinstall of any derived plugins.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Functionalities
 */
class InstallationFunctionality extends AbstractPluginFunctionality implements AdminNoticesServiceAwareInterface, AdminNoticesServiceRegisterInterface, HooksServiceRegisterInterface {
	// region TRAITS

	use InitializeAdminNoticesServiceTrait;
	use SetupAdminNoticesTrait;
	use SetupHooksTrait;

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
	protected bool $has_notice_output = false;

	// endregion

	// region MAGIC METHODS

	/**
	 * {@inheritDoc}
	 */
	public function __construct( LoggingService $logging_service, ?string $component_id = null, ?string $component_name = null ) {
		parent::__construct( $logging_service, $component_id ?: 'installation', $component_name ?: 'Installation' );
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'admin_footer', $this, 'output_installation_js' );
		$hooks_service->add_action( 'wp_ajax_' . $this->get_hook_tag( 'installation_routine' ), $this, 'handle_ajax_installation' );
	}

	/**
	 * Displays an admin notice if there are any installables that need installation or an update routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AdminNoticesService     $notices_service    Instance of the admin notices service.
	 */
	public function register_admin_notices( AdminNoticesService $notices_service ): void {
		if ( \doing_action( 'activate_' . $this->get_plugin()->get_plugin_file_path() ) ) {
			return;
		}

		$installable_version = $this->get_installable_versions();
		if ( empty( $installable_version ) ) {
			return;
		}

		$installed_version  = $this->get_installed_versions();
		$installation_delta = \array_diff_assoc( $installable_version, $installed_version );
		if ( empty( $installation_delta ) ) {
			return;
		}

		\ob_start();

		if ( \is_null( $this->get_original_version() ) ) {
			/* @noinspection PhpIncludeInspection */
			require_once dws_wp_framework_get_core_base_path() . '/src/templates/installation/required-original.php';
		} else {
			/* @noinspection PhpIncludeInspection */
			require_once dws_wp_framework_get_core_base_path() . '/src/templates/installation/required-update.php';
		}
		$message = \ob_get_clean();

		$this->has_notice_output = true;
		$notices_service->add_notice(
			new SimpleAdminNotice(
				$this->get_admin_notice_handle(),
				$message,
				AdminNoticeTypesEnum::INFO,
				array(
					'html'       => true,
					'capability' => 'activate_plugins',
				)
			)
		);
	}

	// endregion

	// region HOOKS

	/**
	 * Outputs the JS that handles the install/update action.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function output_installation_js() {
		if ( false === $this->has_notice_output ) {
			return; // The install/upgrade notice has not been outputted.
		}

		\ob_start();

		?>

		( function( $ ) {
			$( 'div[id^="%div_id%"]' ).on( 'click', '.dws-install, .dws-update', function( e ) {
				var $clicked_button = $( e.target );
				if ( $clicked_button.hasClass('disabled') ) {
					return;
				}

				$( e.target ).addClass('disabled').html('%disabled_message%');
				$.ajax( {
					url: ajaxurl,
					method: 'POST',
					data: {
						action: '%action%',
						_wpnonce: '%nonce%'
					},
					complete: function() {
						window.location.href = '%admin_url%';
					}
				} );
			} );
		} ) ( jQuery );

		<?php

		echo Assets::wrap_string_in_script_tags( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			Strings::replace_placeholders(
				array(
					'%div_id%'           => \esc_js( $this->get_admin_notice_handle() ),
					'%disabled_message%' => \esc_html__( 'Please wait...', 'dws-wp-framework-core' ),
					'%action%'           => \esc_js( $this->get_hook_tag( 'installation_routine' ) ),
					'%nonce%'            => \esc_js( \wp_create_nonce( $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine' ) ),
					'%admin_url%'        => \esc_url( \admin_url() ),
				),
				\ob_get_clean()
			)
		);
	}

	/**
	 * Intercepts an AJAX request for running the installation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function handle_ajax_installation() {
		if ( \check_ajax_referer( $this->get_plugin()->get_plugin_safe_slug() . '_installation_routine', false, false ) ) {
			try {
				$this->install_or_update();
			} catch ( \Exception $exception ) {
				$this->get_admin_notices_service()->add_notice(
					new DismissibleAdminNotice(
						$this->get_admin_notice_handle( 'install-update_fail', 'ajax' ),
						\sprintf(
							/* translators: 1. Installation node name, 2. Error message. */
							__( '<strong>%1$s</strong> failed to complete the installation routine. The error is: %2$s', 'dws-wp-framework-core' ),
							AdminNoticesHelpers::get_registrant_name( $this ),
							$exception->getMessage()
						),
						AdminNoticeTypesEnum::ERROR,
						array( 'persistent' => true )
					),
					'user-meta'
				);
			}
		}

		\wp_die();
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
		return \get_option( $this->get_plugin()->get_plugin_safe_slug() . '_original_version', null );
	}

	/**
	 * Gathers all installable classes and runs their installation or upgrade routines.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  null|InstallFailureException
	 */
	public function install_or_update(): ?InstallFailureException {
		if ( ! Users::has_capabilities( 'activate_plugins' ) ) {
			return new InstallFailureException( 'User does not have enough permissions to run the installation routine' );
		}

		$installed_versions = $this->get_installed_versions();
		$installation_delta = \array_diff_assoc( $this->get_installable_versions(), $installed_versions );
		$notices_service    = $this->get_admin_notices_service();

		foreach ( $installation_delta as $class => $version ) {
			$instance = $this->get_plugin()->get_container_entry( $class );
			$result   = ( ! isset( $installed_versions[ $class ] ) )
				? $instance->install()
				: $instance->update( $installed_versions[ $class ] );

			if ( \is_null( $result ) ) {
				$installed_versions[ $class ] = $version;
				$this->update_installed_version( $installed_versions );
			} else {
				$this->maybe_set_original_version( $installed_versions );
				$notices_service->add_notice(
					new DismissibleAdminNotice(
						$this->get_admin_notice_handle( 'install-update_fail', $class ),
						\sprintf(
							/* translators: 1. Installation node name, 2. Error message. */
							__( '<strong>%1$s</strong> failed to complete the installation routine. The error is: %2$s', 'dws-wp-framework-core' ),
							AdminNoticesHelpers::get_registrant_name( $this ),
							$result->getMessage()
						),
						AdminNoticeTypesEnum::ERROR,
						array( 'persistent' => true )
					),
					'user-meta'
				);

				return $result;
			}
		}

		$result  = $this->maybe_set_original_version( $installed_versions );
		$message = \is_null( $result )
			? /* translators: 1. Plugin name. */ __( '<strong>%1$s</strong> was successfully updated.', 'dws-wp-framework-core' )
			: /* translators: 1. Plugin name. */ __( '<strong>%1$s</strong> was successfully installed.', 'dws-wp-framework-core' );

		$notices_service->add_notice(
			new SimpleAdminNotice(
				$this->get_admin_notice_handle( 'install-update_success', \md5( \wp_json_encode( $installation_delta ) ) ),
				\sprintf( $message, $this->get_plugin()->get_plugin_name() ),
				AdminNoticeTypesEnum::SUCCESS
			),
			'user-meta'
		);

		return null;
	}

	/**
	 * Gathers all installable classes and runs their uninstall routines.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  UninstallFailureException|null
	 */
	public function uninstall(): ?UninstallFailureException {
		if ( ! Users::has_capabilities( (array) 'delete_plugins' ) ) {
			return new UninstallFailureException( 'User does not have enough permissions to run the uninstallation routine' );
		}

		$installed_versions = $this->get_installed_versions();
		$uninstallables     = $this->get_uninstallable_classes();

		foreach ( $uninstallables as $class ) {
			$instance = $this->get_plugin()->get_container_entry( $class );
			if ( \is_null( $instance ) ) {
				continue;
			}

			$installed_version = $installed_versions[ $class ] ?? null;
			$result            = $instance->uninstall( $installed_version );

			if ( ! \is_null( $result ) ) {
				return $result;
			} elseif ( ! \is_null( $installed_version ) ) {
				unset( $installed_versions[ $class ] );
				$this->update_installed_version( $installed_versions );
			}
		}

		\delete_option( $this->get_plugin()->get_plugin_safe_slug() . '_version' );
		\delete_option( $this->get_plugin()->get_plugin_safe_slug() . '_original_version' );

		return null;
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

		foreach ( \get_declared_classes() as $declared_class ) {
			if ( ! \is_a( $declared_class, InstallableInterface::class, true ) ) {
				continue;
			}

			$instance = $this->get_plugin()->get_container_entry( $declared_class );
			if ( ! \is_null( $instance ) ) {
				$installable_versions[ $declared_class ] = $instance->get_current_version();
			}
		}

		return $installable_versions;
	}

	/**
	 * Gets all the declared uninstallables of the plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string[]
	 */
	protected function get_uninstallable_classes(): array {
		return \array_filter( \get_declared_classes(), fn( string $declared_class ) => \is_a( $declared_class, UninstallableInterface::class, true ) );
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
		return \get_option( $this->get_plugin()->get_plugin_safe_slug() . '_version', array() );
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
		return \update_option( $this->get_plugin()->get_plugin_safe_slug() . '_version', $version );
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
		if ( ! \is_null( $original_version ) ) {
			return null;
		}

		$data = array(
			'timestamp'                            => \time(),
			$this->get_plugin()->get_plugin_slug() => $this->get_plugin()->get_plugin_version(),
		);

		return \update_option(
			$this->get_plugin()->get_plugin_safe_slug() . '_original_version',
			$data + $version
		);
	}

	// endregion
}
