<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\Installable\InstallFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\Installable\UninstallFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\Installable\UpdateFailure;

defined( 'ABSPATH' ) || exit;

/**
 * If a class implements this interface, then every time that the 'install' action will be triggered,
 * the 'install' method will be called if the current version is newer than the one previously installed.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions
 */
interface Installable {
	/**
	 * Describes the data installation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  InstallFailure|null
	 */
	public function install(): ?InstallFailure;

	/**
	 * Describes the data update logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $current_version    The currently installed version.
	 *
	 * @return  UpdateFailure|null
	 */
	public function update( string $current_version ): ?UpdateFailure;

	/**
	 * Describes the data uninstallation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  UninstallFailure|null
	 */
	public function uninstall(): ?UninstallFailure;

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
