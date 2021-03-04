<?php

namespace DeepWebSolutions\Framework\Core\Actions;

defined( 'ABSPATH' ) || exit;

/**
 * Describes an instance that has an installation routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions
 */
interface InstallableInterface {
	/**
	 * Describes the data installation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Installable\InstallFailureException|null
	 */
	public function install(): ?Installable\InstallFailureException;

	/**
	 * Describes the data update logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $current_version    The currently installed version.
	 *
	 * @return  Installable\UpdateFailureException|null
	 */
	public function update( string $current_version ): ?Installable\UpdateFailureException;

	/**
	 * Describes the data uninstallation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $current_version    The currently installed version.
	 *
	 * @return  Installable\UninstallFailureException|null
	 */
	public function uninstall( string $current_version ): ?Installable\UninstallFailureException;

	/**
	 * Returns the current version of the installable data of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public function get_current_version(): string;
}
