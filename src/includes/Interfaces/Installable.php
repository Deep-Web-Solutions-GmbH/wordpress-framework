<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * If a class implements this interface, then every time that the 'install' action will be triggered,
 * the 'install' method will be called if the current version is newer than the one previously installed.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0\Interfaces
 */
interface Installable {
	/**
	 * Implements the installation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public static function install();

	/**
	 * Implements the uninstallation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public static function uninstall();

	/**
	 * Returns the current version of the installable content of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_version();
}
